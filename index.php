<?php

//get the microworkers usual staff
$Campaign_id = $_GET["campaign"];
$Worker_id = $_GET["worker"];
$Rand_key = $_GET["rand_key"];
$My_secret_key = "2a0f6d1b74e582f9ee21c8e899bb014163431b1491255737db06bb587703cfcf";
// string we will hash to produce VCODE
$String_final = $Campaign_id . $Worker_id . $Rand_key . $My_secret_key;
$vcode_for_proof = "mw-" . hash("sha256", $String_final);

//job assignment algorithm
//first count how many files are in the jobs folder
$total_numb_jobs = count(glob('jobs/*'));
//set up the desired number of iterations
$total_numb_it = 5;
//default values = error codes for later
$next_job = 10000;
$next_it = 10000;
//check the files in the results folder and assign the next available job
for ($it = 1; $it <= $total_numb_it; $it++) {                       //for each iteration
  for ($in = 1; $in <= $total_numb_jobs; $in++) {                   //for each job
    $file_to_check1 = "results/job_" . $in . '_' . $it . ".txt";    //folder to check for results
    $existing = file_exists($file_to_check1);                       //check if file exists
    if ($existing) {                                                //if it does exist
      $no_of_lines = count(file($file_to_check1));                  //count the number of lines - basically check if empty
      $last_mod = filemtime($file_to_check1);                       //get the last modification time
      $cur_time = time();                                           //get the current time
      $time_since_last_mod = ($cur_time - $last_mod) / 60;          //calculate the time since last modification in minutes
      if ($no_of_lines < 1 and $time_since_last_mod > 20) {         //if the file is empty and it has been more than 20 minutes since last modification
        $next_job = $in;                                            //accept the job number
        $next_it = $it;                                             //and the iteration number
        unlink($file_to_check1);                                    //delete existing empty file
        break 2;                                                    //break out of the loops
      }
    } else {                                                        //if the file does not exist
      $next_job = $in;                                              //accept the job number
      $next_it = $it;                                               //and the iteration number
      break 2;                                                      //break out of the loops
    }
  }
}
//after job assignment we either end up with a legit job number and iteration number or with error codes
//in any cas ewe create the appropriate file in results (and user_info) folders
file_put_contents("results/job_" . $next_job . "_" . $next_it . ".txt", "");
file_put_contents("user_info/job_" . $next_job . "_" . $next_it . ".txt", "");

//if after the job assignment we have error codes we return an error message
if ($next_job == 10000 and $next_it == 10000) {
  @require_once('header.php');
  echo "<h1>⚠️</h1>
  <h1>Sorry, there are no more jobs available at the moment.</h1>
  <h2>Please try again later.</h2>
  ";
  @require_once('footer.php');
  exit();
}

//if job was assigned we read the appropriate job TXT file line by line
$json_filename = 'jobs/job_' . $next_job . '.txt';

$handle = fopen($json_filename, "r");
$data = array();
while (($line = fgets($handle)) !== false) {
  // Skip empty lines
  if (trim($line) === '') {
    continue;
  }

  // Parse JSON from each line
  $json_data = json_decode($line, true);
  if ($json_data === null) {
    echo 'Error parsing JSON: ' . json_last_error_msg() . PHP_EOL;
    continue;
  }

  // Append parsed JSON to data array
  $data[] = $json_data;
}
fclose($handle);

// Calculate canvas size based on background image
$bg_image_filenames = array();
foreach ($data as $json_obj) {
  $bg_image_filenames[] = 'pics/' . $json_obj[0]['filename'] . '.png';
}
$bg_image_info = getimagesize($bg_image_filenames[0]);
$bg_image_width = $bg_image_info[0];
$bg_image_height = $bg_image_info[1];

require_once('header.php');

echo '<div id="controls_container">
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
let canvas_ = [];
let ctx_ = [];
let selectedPointIndex_ = [];
let pointRadius_ = [];
</script>';
// Iterate through data array
foreach ($data as $index => $json_obj) {
  // Extract data
  $points = $json_obj[0]['points'];
  $number_points = $json_obj[0]['number_points'];
  $ID = $json_obj[0]['ID'] . '-' . $json_obj[0]['filename'];
  $bg_image_filename = $bg_image_filenames[$index];

  // $max_x = $json_obj[0]['max_x'];
  // $min_x = $json_obj[0]['min_x'];
  // $max_y = $json_obj[0]['max_y'];
  // $min_y = $json_obj[0]['min_y'];

  // Calculate canvas size
  $canvas_width = $bg_image_width; //$max_x - $min_x;
  $canvas_height = $bg_image_height; //$max_y - $min_y;

  // Output canvas and points
  echo '<div class="task-wrapper hidden" id="task_' . $ID . '">';
  echo '<div class="canvas-wrapper">';
  echo '<h1>Polygon #' . $ID . '</h1>';
  echo '<canvas id="canvas_' . $index . '" width="' . $canvas_width . '" height="' . $canvas_height . '" style="background-image:url(' . $bg_image_filename . ');border:1px solid black;"></canvas>';
  echo '<script>
          // Array to store the points of the polygon
          polygonPoints_[' . $index . '] = [];
          // Index of the currently selected point (-1 means none selected)
          selectedPointIndex_[' . $index . '] = -1;
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
    echo 'var x' . $i . ' = ' . $point['x'] . ';
          var y' . $i . ' = ' . $point['y'] . ';
          ctx_[' . $index . '].arc(x' . $i . ', y' . $i . ', 2, 0, 2 * Math.PI);
          //fill the point list
          polygonPoints_[' . $index . '].push({"x":x' . $i . ', "y":y' . $i . '});
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
  echo '<button class="nextBtn">Next</button>
  </div>
  </div>';
}

echo '<script>';
echo '
let polygonPoints = polygonPoints_[0];
let canvas = canvas_[0];
let ctx = ctx_[0];
let selectedPointIndex = selectedPointIndex_[0];
let pointRadius = pointRadius_[0];
interactiveCanvas(canvas);
drawPolygon();
';
echo '
const userInfo = {
  campaign: "' . $Campaign_id . '",
  worker: "' . $Worker_id . '",
  vcode: "' . $vcode_for_proof . '"
};
const dataInfo = {
  file: "' . $json_filename . '",
  image: "' . join('&', $bg_image_filenames) . '",
  job: "' . $next_job . '",
  iteration: "' . $next_it . '",
};
';
echo '</script>';

echo '<button id="confirmBtn" class="hidden">Submit results</button>';
require_once('footer.php');
