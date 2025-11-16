<?php
$log = $_GET['job'];
//only take the part before the last underscore for the job name
$job = substr($log, 0, strrpos($log, '_'));
$job_data = file_get_contents('jobs/' . $job . '.txt');
$log_data = file_get_contents('user_logs/' . $log . '.json');

//new thing - also check if reference exists
$reference = file_exists('references/' . $job . '.txt') ? file_get_contents('references/' . $job . '.txt') : null;
//if it exists, we use it in our interface
if ($reference){
    $reference = json_decode($reference);
}
// var_dump($reference->{'task_100-100'});
// exit();

$job_data = json_decode($job_data);
$log_data = json_decode($log_data);

$total_polygons = count($job_data);
$total_actions = 0;
$total_duration = 0;
foreach ($log_data as $entry) {
    $actions = isset($entry->log) ? count($entry->log) : 0;
    $total_actions += $actions;
    if ($actions > 1) {
        $start = $entry->log[0]->timestamp;
        $end = $entry->log[$actions - 1]->timestamp;
        $total_duration += ($end - $start);
    }
}
$average_actions = $total_polygons > 0 ? round($total_actions / $total_polygons) : 0;
$average_duration = $total_polygons > 0 ? round(($total_duration / $total_polygons) / 1000, 1) : 0;

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
echo '<section class="info-block log-callout">
<h1>Replay: Job ' . htmlspecialchars($job) . '</h1>
<p>This dashboard replays a single contributor\'s session from our crowdsourcing platform. Follow each action below to understand how the polygon evolved over time.</p>
<div class="log-meta">
  <span>Polygons recorded: ' . $total_polygons . '</span>
  <span>Total actions: ' . $total_actions . '</span>
  <span>Avg. actions per polygon: ' . $average_actions . '</span>
  <span>Avg. duration: ' . $average_duration . 's</span>
</div>
<div class="demo-actions">
  <a class="action secondary" href="userLogsView.php">Back to log library</a>
  <a class="action primary" href="index.php">Try the editor</a>
</div>
</section>';
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
let reference_points_ = [];
let canvas_ = [];
let ctx_ = [];
let cursor_overlay_ = [];
let cursor_overlay_ctx_ = [];
let reference_overlay_ = [];
let reference_overlay_ctx_ = [];
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

  //get the reference points if they exist
  if ($reference){
    $reference_points = $reference->{'task_'.$ID};
  }

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
    } else if ($log->action == "move" || $log->action == "mousedrag"){
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
  echo '<div class="canvas-wrapper" style="position: relative;">';
  echo '<h1>Polygon #' . $ID . '</h1>';
  echo '<div class="replay-controls">';
  echo '<span>Replay of '. $total_actions.' user actions (select:'.$select_actions.', move:'.$move_actions.', create:'.$create_actions.', delete:'.$delete_actions.')</span><br><span>Total time: '.($total_time/1000).' seconds</span>';
  if ($total_actions > 0){
    echo '<div class="replay-buttons">';
    echo '<button class="play"><i class="fas fa-play"></i> Real time</button>';
    echo '<button class="play2"><i class="fas fa-play"></i> 2x time</button>';
    echo '<button class="play05"><i class="fas fa-play"></i> 0.5x time</button>';
    echo '<button class="play1"><i class="fas fa-play"></i> 1s step</button>';
    echo '<button class="stop"><i class="fas fa-stop"></i> Stop</button>';
    echo '</div>';
  }
  echo '<div class="timeline-bar" style="width: 100%; display: flex; flex-direction: row; align-items: center; justify-content: flex-start; padding: 3px; height: 10px; background-color:#666;">';
  echo '<div class="timeline-progress" style="height: 4px; width: 0%; background-color: white; right-border: 2px solid black;" data-totaltime = "'.$total_time.'"></div>';
  echo '</div>';
  echo '</div>';
  echo '</br>';
  if ($reference){
    echo '<div class="reference">';
    echo '<div class="reference_input">';
    echo '<label for="reference_'.$ID.'">Show reference</label>';
    echo '<input type="checkbox" name="reference_'.$ID.'">';
    echo '</div>';
    echo '<div class="IoU">Current step IoU: <span>-</span></div>';
    echo '<canvas class="IoU_development" width=400 height=200 style="display:none;"></canvas>';
    echo '</div>';
  } else {
    echo '<i>No reference data available</i>';
  }
  echo '</br>';
  echo '<canvas id="canvas_' . $index . '" width="' . $canvas_width . '" height="' . $canvas_height . '" style="background-image:url(' . $bg_image_filename . ');border:1px solid black;"></canvas>';
  echo '<canvas id="cursor_overlay_' . $index . '" width="' . $canvas_width . '" height="' . $canvas_height . '" style="position:absolute;bottom:0;left:50%;border:1px solid transparent; transform: translateX(-50%);"></canvas>';
  if ($reference){
    echo '<canvas id="reference_overlay_' . $index . '" width="' . $canvas_width . '" height="' . $canvas_height . '" style="position:absolute;bottom:0;left:50%;border:1px solid transparent; transform: translateX(-50%); display: none;"></canvas>';
  }
  echo '<script>
          // Array to store the points of the polygon
          polygonPoints_[' . $index . '] = [];
          polygonPointsBackup_[' . $index . '] = [];
          // Index of the currently selected point (-1 means none selected)
          selectedPointIndex_[' . $index . '] = -1;
          selectedPointIndexBackup_[' . $index . '] = -1;
          // Radius of the polygon points for hover effect and interaction
          pointRadius_[' . $index . '] = 10;

          //array to store the reference points
          reference_points_.push(' . json_encode($reference_points) . ');

          canvas_[' . $index . '] = document.getElementById("canvas_' . $index . '");
          ctx_[' . $index . '] = canvas_[' . $index . '].getContext("2d");
          ctx_[' . $index . '].fillStyle = "rgba(255,255,255,0.1)";
          ctx_[' . $index . '].lineWidth = "5";
          ctx_[' . $index . '].strokeStyle = "black";
          // Draw points and lines on canvas

          cursor_overlay_[' . $index . '] = document.getElementById("cursor_overlay_' . $index . '");
          cursor_overlay_ctx_[' . $index . '] = cursor_overlay_[' . $index . '].getContext("2d");
          cursor_overlay_ctx_[' . $index . '].fillStyle = "rgba(255,0,0,1)";
          cursor_overlay_ctx_[' . $index . '].lineWidth = "15";
          cursor_overlay_ctx_[' . $index . '].strokeStyle = "red";
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
  //simple version for reference points if they exist
    if ($reference){
        echo 'reference_overlay_[' . $index . '] = document.getElementById("reference_overlay_' . $index . '");
            reference_overlay_ctx_[' . $index . '] = reference_overlay_[' . $index . '].getContext("2d");
            reference_overlay_ctx_[' . $index . '].fillStyle = "rgba(0,0,255,0.1)";
            reference_overlay_ctx_[' . $index . '].lineWidth = "5";
            reference_overlay_ctx_[' . $index . '].strokeStyle = "rgba(0,0,255,0.5)";
            ';
        foreach ($reference_points as $i => $point) {
        echo '  var x' . $i . ' = ' . $point->x . ';
                var y' . $i . ' = ' . $point->y . ';
                reference_overlay_ctx_[' . $index . '].arc(x' . $i . ', y' . $i . ', 2, 0, 2 * Math.PI);
                ';
            }
        echo 'reference_overlay_ctx_[' . $index . '].fill();';
        echo '  reference_overlay_ctx_[' . $index . '].beginPath();
                reference_overlay_ctx_[' . $index . '].moveTo(x0, y0);';
        for ($i = 1; $i < count($reference_points); $i++) {
        echo 'reference_overlay_ctx_[' . $index . '].lineTo(x' . $i . ', y' . $i . ');
                ';
        }
        echo 'reference_overlay_ctx_[' . $index . '].lineTo(x0, y0);
                reference_overlay_ctx_[' . $index . '].stroke();
                reference_overlay_ctx_[' . $index . '].closePath();
                ';
    }
  echo '</script>';
  echo '<button class="nextBtn" style="display: none;"></button>
  </div>
  </div>';
}

echo '<script>';
echo'
let reference_checkboxes = document.querySelectorAll(".reference input[type=checkbox]");
let reference_IoU = document.querySelectorAll(".IoU");
let reference_IoU_development = document.querySelectorAll(".IoU_development");
reference_checkboxes.forEach((checkbox, index) => {
    checkbox.addEventListener("change", (e) => {
        if (e.target.checked){
            reference_overlay_[index].style.display = "block";
            reference_IoU[index].style.display = "block";
            reference_IoU_development[index].style.display = "block";
        } else {
            reference_overlay_[index].style.display = "none";
            reference_IoU[index].style.display = "none";
            reference_IoU_development[index].style.display = "none";
        }
    });
});
';

if ($reference){
    echo 'let reference_exists = true;
        // Initialize empty datasets for the chart
        const initialData = {
            labels: [],
            datasets: [{
                label: "IoU Values",
                borderColor: "blue",
                backgroundColor: "transparent",
                data: [],
                tension: 0.4, // for smooth line
                borderWidth: 1
            },
            {
                label: "Initial IoU Value",
                borderColor: "rgba(0, 0, 0, 0.5)",
                backgroundColor: "transparent",
                data: [],
                borderDash: [5, 5],
                borderWidth: 1,
                pointRadius: 0
            }]
        };

        // Define options for the chart
        const chartOptions = {
            responsive: false, // Set responsive to false to prevent canvas resizing
            scales: {
              x: {
                type: "linear",
                position: "bottom",
                title: {
                  display: true,
                  text: "User Actions"
                }
              },
              y: {
                // min: 0,
                // max: 1,
                title: {
                  display: true,
                  text: "IoU"
                }
              }
            },
            plugins: {
                legend: {
                    display: false // Hide the legend
                }
            },
            animation: {
                duration: 0 // Disable animations
            }
        };

        //create charts for all the reference polygons
        let iou_charts = [];
        document.querySelectorAll(".IoU_development").forEach((chart, index) => {
            let chart_ctx = chart.getContext("2d");
            let myChart = new Chart(chart_ctx, {
                type: "line",
                data: initialData,
                options: chartOptions
            });
            iou_charts.push(myChart);
        });
        ';
} else {
    echo 'let reference_exists = false;';
}

echo '
let currentlyPlaying = false;
let stopPressed = false;
let polygonPoints;
let canvas;
let ctx;
let cursor_overlay;
let cursor_overlay_ctx;
const cursor_png = new Image();
cursor_png.src = "pics/misc/cursor.png";
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

let timeouts = [];

function play(ind, mode = "real"){
    if (currentlyPlaying){
        console.log("already playing");
        return;
    } else {
        currentlyPlaying = true;
    }
    //bring all to initial state
    if (reference_exists){
        document.querySelectorAll(".IoU span")[ind].innerHTML = "-";
        let chart = iou_charts[ind];
        chart.data.labels = []; // Clear labels array
        chart.data.datasets.forEach(dataset => {
            dataset.data = []; // Clear data array for each dataset
        });
        chart.update(); // Update the chart to reflect the changes
    }
    restorePolygon(ind);
    let cnv = canvas_[ind];
    let ctx = ctx_[ind];
    let c_o = cursor_overlay_[ind];
    let c_o_ctx = cursor_overlay_ctx_[ind];
    let pts = polygonPoints_[ind];
    let userLog = userLogs[ind];
    let initialTimestamp = userLog.log[0].timestamp;
    let moddedTimestamps = [];
    let percentProgress = [];
    userLog.log.forEach((log, ii) => {
        //reduce the timestamps to 0
        moddedTimestamps[ii] = log.timestamp - initialTimestamp;
        //also generate the progress bar data
        percentProgress[ii] = moddedTimestamps[ii] / c_o.parentElement.querySelector(".timeline-progress").dataset.totaltime * 100;
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
    //there are 5 types of events in the user log: "select", "move", "create", "delete", "mousedrag"
    //there is also a "mousemove" for the cursor overlay
    
    for (let i = 0; i < userLog.log.length; i++){
        let currentStep = userLog.log[i];
        let timeout = setTimeout(()=>{
            if (stopPressed){ //think this peace of code never happens after the last code update, but not sure
                //we need to reset everything and not let the next step execute
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

            //update the progress bar
            c_o.parentElement.querySelector(".timeline-progress").style.width = percentProgress[i] + "%";

            console.log("##################################");
            console.log(`step ${i+1}/${userLog.log.length} (${currentStep.timestamp / 1000} seconds)`);

            //display the cursor movement
            c_o_ctx.clearRect(0, 0, c_o.width, c_o.height);
            c_o_ctx.beginPath();
            //Cursor icon by Icons8 https://icons8.com
            c_o_ctx.drawImage(cursor_png, currentStep.coordinates.x - 15, currentStep.coordinates.y - 15, 30, 30);
            c_o_ctx.fill();
            c_o_ctx.stroke();

            console.log(`moving mouse to ${currentStep.coordinates.x}, ${currentStep.coordinates.y}`);

            if (currentStep.action == "select"){
                //select the point
                selectedPointIndex_[ind] = currentStep.pointIndex;
                redrawPolygon(ind, i, false);
                console.log(`selecting point ${currentStep.pointIndex}`);
            } else if (currentStep.action == "create"){
                //create the point
                //this one is a bit tricky - we need to insert the point at the right index and adjust others
                pts.splice(currentStep.pointIndex, 0, currentStep.coordinates);
                selectedPointIndex_[ind] = currentStep.pointIndex;
                redrawPolygon(ind, i, false);
                console.log(`creating point at ${currentStep.coordinates.x}, ${currentStep.coordinates.y}`);
            } else if (currentStep.action == "delete"){
                //delete the point
                //this one is also tricky - we need to delete the point at the right index and adjust others
                selectedPointIndex_[ind] = -1;
                pts.splice(currentStep.pointIndex, 1);
                redrawPolygon(ind, i, false);
                console.log(`deleting point ${currentStep.pointIndex}`);
            } else if (currentStep.action == "move" || currentStep.action == "mousedrag"){ //move was in the legacy version, probably can get rid of it completely but it makes sense to keep it if we ever want to go back to only record the finished actions and not all the mouse movements
                //move the point
                selectedPointIndex_[ind] = currentStep.pointIndex;
                pts[currentStep.pointIndex].x = currentStep.coordinates.x;
                pts[currentStep.pointIndex].y = currentStep.coordinates.y;
                redrawPolygon(ind, i, true);
                console.log(`moving point ${currentStep.pointIndex} to ${currentStep.coordinates.x}, ${currentStep.coordinates.y}`);
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

        timeouts.push(timeout);
    }
}

// Function to pause the visualization
function pauseVisualization() {
    timeouts.forEach(timeout => clearTimeout(timeout));
}

// Function to reset the visualization
function resetVisualization() {
    pauseVisualization();
    timeouts = [];
}

function stop(ind){
    //stop the replay and return everything to the initial state
    stopPressed = true;
    currentlyPlaying = false;
    pauseVisualization();
    //and all the buttons
    document.querySelectorAll(".replay-buttons")[ind].querySelectorAll("button").forEach(btn => {
        btn.disabled = false;
        btn.classList.remove("active");
    });
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
function redrawPolygon(ind, step, ignoreDraw) {
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

    //if the reference exists, we also want to compute IoU using turf.js
    if(reference_exists && !ignoreDraw){
        let reference_points = reference_points_[ind];
        //add the first point to the end to close the polygon
        let polygon_points = polygonPoints.map(p => {return {x: p.x, y: p.y}});
        polygon_points.push(polygon_points[0]);
        let polygon = turf.polygon([polygon_points.map(p => turf.toWgs84([p.x, p.y]))]);
        let reference_polygon = turf.polygon([reference_points.map(p => turf.toWgs84([p.x, p.y]))]);
        let intersection = turf.intersect(polygon, reference_polygon);
        let union = turf.union(polygon, reference_polygon);
        let intersection_area = intersection ? turf.area(intersection) : 0;
        let union_area = turf.area(union);
        let iou = intersection_area / union_area;
        //output the iou in the proper place
        let output = document.querySelectorAll(".IoU")[ind].querySelector("span");
        output.innerHTML = iou.toFixed(3);
        //also dynamically draw and add new data to the the IoU development chart using chart.js line chart on the IoU_development canvas
        let chart = iou_charts[ind];
        chart.data.labels.push(step);
        chart.data.datasets[0].data.push(iou); 
        chart.data.datasets[1].data.push(chart.data.datasets[0].data[0]);
        chart.update();
    }
}
';
echo '</script>';

require_once('footer.php');
