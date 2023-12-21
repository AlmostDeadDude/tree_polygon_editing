<?php
$log = $_GET['job'];
//only take the part before the last underscore for the job name
$job = substr($log, 0, strrpos($log, '_'));
$job_data = file_get_contents('jobs/' . $job . '.txt');
$log_data = file_get_contents('user_logs/' . $log . '.json');

$job_data = json_decode($job_data);
$log_data = json_decode($log_data);

// Calculate canvas size based on background image
$bg_image_filenames = array();
foreach ($job_data as $json_obj) {
    $json_obj = $json_obj[0];
    $bg_image_filenames[] = 'pics/' . $json_obj->filename . '.png';
}
$bg_image_info = getimagesize($bg_image_filenames[0]);
$bg_image_width = $bg_image_info[0];
$bg_image_height = $bg_image_info[1];

require_once('header.php');
echo '<div id="controls_container" style="display:none;">
<h2 id="controls_title">Controls info <i class="fas fa-chevron-down"></i></h2>
<div id="controls_wrapper" class="hidden" data-collapsed = "true">
<img class="controls_item" src="pics/controls/activate.svg" alt="select the point"></img>
<img class="controls_item" src="pics/controls/move.svg" alt="move the point"></img>
<img class="controls_item" src="pics/controls/add.svg" alt="create the coint"></img>
<img class="controls_item" src="pics/controls/delete.svg" alt="delete the point"></img>
</div>
</div>';
echo '<script>
let polygonPoints_ = [];
let polygonPointsBackup_ = [];
let canvas_ = [];
let ctx_ = [];
let selectedPointIndex_ = [];
let selectedPointIndexBackup_ = [];
let pointRadius_ = [];
</script>';
// Iterate through data array
foreach ($job_data as $index => $json_obj) {
  // Extract data
  $points = $json_obj[0]->points;
  $number_points = $json_obj[0]->number_points;
  $ID = $json_obj[0]->ID . '-' . $json_obj[0]->filename;
  $bg_image_filename = $bg_image_filenames[$index];

  // Calculate canvas size
  $canvas_width = $bg_image_width;
  $canvas_height = $bg_image_height; 

  //Calculate some log data
  $total_actions = count($log_data[$index]->log);
  $select_actions = 0;
  $move_actions = 0;
  $create_actions = 0;
  $delete_actions = 0;
  foreach ($log_data[$index]->log as $log) {
    if ($log->action == "select"){
        $select_actions++;
    } else if ($log->action == "move"){
        $move_actions++;
    } else if ($log->action == "create"){
        $create_actions++;
    } else if ($log->action == "delete"){
        $delete_actions++;
    }
  }
  $total_time = $log_data[$index]->log[$total_actions-1]->timestamp - $log_data[$index]->log[0]->timestamp;

  // Output canvas and points
  echo '<div class="task-wrapper" id="task_' . $ID . '">';
  echo '<div class="canvas-wrapper">';
  echo '<h1>Polygon #' . $ID . '</h1>';
  echo '<div class="replay-controls">';
  echo '<span>Replay of '. $total_actions.' user actions (select:'.$select_actions.', move:'.$move_actions.', create:'.$create_actions.', delete:'.$delete_actions.')</span><br><span>Total time: '.($total_time/1000).' seconds</span>';
  if ($total_actions > 0){
    echo '<div class="replay-buttons">';
    echo '<button class="play"><i class="fas fa-play"></i> Real time</button>';
    echo '<button class="play2"><i class="fas fa-play"></i> 2x time</button>';
    echo '<button class="play05"><i class="fas fa-play"></i> 0.5x time</button>';
    echo '<button class="play1"><i class="fas fa-play"></i> 1s step</button>';
    echo '<button class="stop"><i class="fas fa-stop"></i> Stop/Reset</button>';
    echo '</div>';
  }
  echo '</div>';
  echo '<canvas id="canvas_' . $index . '" width="' . $canvas_width . '" height="' . $canvas_height . '" style="background-image:url(' . $bg_image_filename . ');border:1px solid black;"></canvas>';
  echo '<script>
          // Array to store the points of the polygon
          polygonPoints_[' . $index . '] = [];
          polygonPointsBackup_[' . $index . '] = [];
          // Index of the currently selected point (-1 means none selected)
          selectedPointIndex_[' . $index . '] = -1;
          selectedPointIndexBackup_[' . $index . '] = -1;
          // Radius of the polygon points for hover effect and interaction
          pointRadius_[' . $index . '] = 10;

          canvas_[' . $index . '] = document.getElementById("canvas_' . $index . '");
          ctx_[' . $index . '] = canvas_[' . $index . '].getContext("2d");
          ctx_[' . $index . '].fillStyle = "rgba(255,255,255,0.1)";
          ctx_[' . $index . '].lineWidth = "5";
          ctx_[' . $index . '].strokeStyle = "black";
          // Draw points and lines on canvas
          ';
  foreach ($points as $i => $point) {
    echo 'var x' . $i . ' = ' . $point->x . ';
          var y' . $i . ' = ' . $point->y . ';
          ctx_[' . $index . '].arc(x' . $i . ', y' . $i . ', 2, 0, 2 * Math.PI);
          //fill the point list
          polygonPoints_[' . $index . '].push({"x":x' . $i . ', "y":y' . $i . '});
          polygonPointsBackup_[' . $index . '].push({"x":x' . $i . ', "y":y' . $i . '});
          ';
  }
  echo 'ctx_[' . $index . '].fill();
          ';
  echo 'ctx_[' . $index . '].beginPath();
          ctx_[' . $index . '].moveTo(x0, y0);
          ';
  for ($i = 1; $i < count($points); $i++) {
    echo 'ctx_[' . $index . '].lineTo(x' . $i . ', y' . $i . ');
          ';
  }
  echo 'ctx_[' . $index . '].lineTo(x0, y0);
          ctx_[' . $index . '].stroke();
          ctx_[' . $index . '].closePath();
          ';
  echo '</script>';
  echo '<button class="nextBtn" style="display: none;"><span>Next</span><small>' . ($index + 1) . '/' . count($data) . '</small></button>
  </div>
  </div>';
}

echo '<script>';
echo '
let currentlyPlaying = false;
let stopPressed = false;
let polygonPoints;
let canvas;
let ctx;
let selectedPointIndex;
let pointRadius;
let userLogs = ' . json_encode($log_data) . ';
canvas_.forEach((c, index)=>{
    polygonPoints = polygonPoints_[index];
    canvas = canvas_[index];
    ctx = ctx_[index];
    selectedPointIndex = selectedPointIndex_[index];
    pointRadius = pointRadius_[index];
    drawPolygon();
    canvas.parentElement.querySelector(".play").addEventListener("click", ()=>{
        stopPressed = false;
        play(index, "real");
    });
    canvas.parentElement.querySelector(".play2").addEventListener("click", ()=>{
        stopPressed = false;
        play(index, "2x");
    });
    canvas.parentElement.querySelector(".play05").addEventListener("click", ()=>{
        stopPressed = false;
        play(index, "0.5x");
    });
    canvas.parentElement.querySelector(".play1").addEventListener("click", ()=>{
        stopPressed = false;
        play(index, "1s");
    });
    canvas.parentElement.querySelector(".stop").addEventListener("click", ()=>{
        stop(index);
    });

});

function play(ind, mode = "real"){
    if (currentlyPlaying){
        console.log("already playing");
        return;
    } else {
        currentlyPlaying = true;
    }
    restorePolygon(ind);
    let cnv = canvas_[ind];
    let ctx = ctx_[ind];
    let pts = polygonPoints_[ind];
    let userLog = userLogs[ind];
    let initialTimestamp = userLog.log[0].timestamp;
    let moddedTimestamps = [];
    userLog.log.forEach((log, ii) => {
        //reduce the timestamps to 0
        moddedTimestamps[ii] = log.timestamp - initialTimestamp;
    });
    //adjust the timestamps and the button status to the mode
    let p = cnv.parentElement.querySelector(".play");
    let p2 = cnv.parentElement.querySelector(".play2");
    let p05 = cnv.parentElement.querySelector(".play05");
    let p1 = cnv.parentElement.querySelector(".play1");
    if (mode == "real"){
        //do nothing, just disable the buttons
        p.disabled = false;
        p.classList.add("active");
        p2.disabled = true;
        p2.classList.remove("active");
        p05.disabled = true;
        p05.classList.remove("active");
        p1.disabled = true;
        p1.classList.remove("active");
    } else if (mode == "2x"){
        moddedTimestamps = moddedTimestamps.map(ts => ts / 2);
        p.disabled = true;
        p.classList.remove("active");
        p2.disabled = false;
        p2.classList.add("active");
        p05.disabled = true;
        p05.classList.remove("active");
        p1.disabled = true;
        p1.classList.remove("active");
    } else if (mode == "0.5x"){
        moddedTimestamps = moddedTimestamps.map(ts => ts * 2);
        p.disabled = true;
        p.classList.remove("active");
        p2.disabled = true;
        p2.classList.remove("active");
        p05.disabled = false;
        p05.classList.add("active");
        p1.disabled = true;
        p1.classList.remove("active");
    } else if (mode == "1s"){
        counter = 0;
        moddedTimestamps = moddedTimestamps.map(ts => counter++ * 1000);
        p.disabled = true;
        p.classList.remove("active");
        p2.disabled = true;
        p2.classList.remove("active");
        p05.disabled = true;
        p05.classList.remove("active");
        p1.disabled = false;
        p1.classList.add("active");
    }
    console.log(userLog);
    //next we replay the user log in real time using timestamps starting from the initial points configuration from pts
    //there are 4 types of events in the user log: "select", "move", "create", "delete"
    
    for (let i = 0; i < userLog.log.length; i++){
        let currentStep = userLog.log[i];
        setTimeout(()=>{
            if (stopPressed){
                currentlyPlaying = false;
                console.log("playback stopped");
                p.disabled = false;
                p.classList.remove("active");
                p2.disabled = false;
                p2.classList.remove("active");
                p05.disabled = false;
                p05.classList.remove("active");
                p1.disabled = false;
                p1.classList.remove("active");
                return;
            }
            console.log("##################################");
            console.log(`step ${i+1}/${userLog.log.length} (${currentStep.timestamp / 1000} seconds)`);
            if (currentStep.action == "select"){
                //select the point
                selectedPointIndex_[ind] = currentStep.pointIndex;
                redrawPolygon(ind);
                console.log(`selecting point ${currentStep.pointIndex}`);
            } else if (currentStep.action == "move"){
                //move the point
                selectedPointIndex_[ind] = currentStep.pointIndex;
                pts[currentStep.pointIndex].x = currentStep.coordinates.x;
                pts[currentStep.pointIndex].y = currentStep.coordinates.y;
                redrawPolygon(ind);
                console.log(`moving point ${currentStep.pointIndex} to ${currentStep.coordinates.x}, ${currentStep.coordinates.y}`);
            } else if (currentStep.action == "create"){
                //create the point
                //this one is a bit tricky - we need to insert the point at the right index and adjust others
                pts.splice(currentStep.pointIndex, 0, currentStep.coordinates);
                selectedPointIndex_[ind] = currentStep.pointIndex;
                redrawPolygon(ind);
                console.log(`creating point at ${currentStep.coordinates.x}, ${currentStep.coordinates.y}`);
            } else if (currentStep.action == "delete"){
                //delete the point
                //this one is also tricky - we need to delete the point at the right index and adjust others
                selectedPointIndex_[ind] = -1;
                pts.splice(currentStep.pointIndex, 1);
                redrawPolygon(ind);
                console.log(`deleting point ${currentStep.pointIndex}`);
            }
            if (i == userLog.log.length - 1){
                currentlyPlaying = false;
                console.log("playback finished");
                p.disabled = false;
                p.classList.remove("active");
                p2.disabled = false;
                p2.classList.remove("active");
                p05.disabled = false;
                p05.classList.remove("active");
                p1.disabled = false;
                p1.classList.remove("active");
            }
        }, moddedTimestamps[i]);
    }
}

function stop(ind){
    //stop the replay and return everything to the initial state
    stopPressed = true;
    currentlyPlaying = false;
    //we also reset the canvas to initial state
    restorePolygon(ind);
}

function restorePolygon(ind){
    polygonPoints_ = JSON.parse(JSON.stringify(polygonPointsBackup_));
    polygonPoints = polygonPointsBackup_[ind];
    canvas = canvas_[ind];
    ctx = ctx_[ind];
    selectedPointIndex_ = JSON.parse(JSON.stringify(selectedPointIndexBackup_));
    selectedPointIndex = selectedPointIndexBackup_[ind];

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw lines connecting the polygon points
    ctx.beginPath();
    ctx.moveTo(polygonPoints[0].x, polygonPoints[0].y);
    for (let i = 1; i < polygonPoints.length; i++) {
        ctx.lineTo(polygonPoints[i].x, polygonPoints[i].y);
    }
    ctx.closePath();
    ctx.stroke();

    // Draw the polygon points
    for (let i = 0; i < polygonPoints.length; i++) {
        const point = polygonPoints[i];
        ctx.beginPath();
        ctx.arc(point.x, point.y, pointRadius, 0, 2 * Math.PI);
        ctx.fillStyle = i === selectedPointIndex ? "yellow" : "#888"; // Highlight the selected point in red
        ctx.fill();
        ctx.stroke();
    }
}

//utility functions for canvas points like in points.js
function redrawPolygon(ind) {
    polygonPoints = polygonPoints_[ind];
    canvas = canvas_[ind];
    ctx = ctx_[ind];
    selectedPointIndex = selectedPointIndex_[ind];

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw lines connecting the polygon points
    ctx.beginPath();
    ctx.moveTo(polygonPoints[0].x, polygonPoints[0].y);
    for (let i = 1; i < polygonPoints.length; i++) {
        ctx.lineTo(polygonPoints[i].x, polygonPoints[i].y);
    }
    ctx.closePath();
    ctx.stroke();

    // Draw the polygon points
    for (let i = 0; i < polygonPoints.length; i++) {
        const point = polygonPoints[i];
        ctx.beginPath();
        ctx.arc(point.x, point.y, pointRadius, 0, 2 * Math.PI);
        ctx.fillStyle = i === selectedPointIndex ? "yellow" : "#888"; // Highlight the selected point in red
        ctx.fill();
        ctx.stroke();
    }
}
';
echo '</script>';

require_once('footer.php');



