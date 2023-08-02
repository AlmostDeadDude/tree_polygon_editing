<?php
//initialize the session
session_start();
//we are using the session variable to remember when user completed the qualification test
$_SESSION['qualification'] = true;

$firstTime = $_GET['firstTime'];
$Campaign_id = $_GET["campaign"];
$Worker_id = $_GET["worker"];
$Rand_key = $_GET["rand_key"];

//data is set for the qualification test
$data = [json_decode('[{"ID": "0", "number": "000", "number_points": 8, "points": [{"number": 118, "x": 245, "y": 546.8541564941406}, {"number": 119, "x": 201, "y": 386.8541564941406}, {"number": 120, "x": 327, "y": 215.85415649414062}, {"number": 121, "x": 448, "y": 269.8541564941406}, {"number": 122, "x": 557, "y": 333.8541564941406}, {"number": 123, "x": 624, "y": 447.8541564941406}, {"number": 124, "x": 583, "y": 571.8541564941406}, {"number": 125, "x": 461, "y": 469.8541564941406}], "max_x": 624, "min_x": 201, "max_y": 571.8541564941406, "min_y": 215.85415649414062}]
', true)];

// Calculate canvas size based on background image
$bg_image_filename = 'pics/70.png'; // TODO: Update with the actual filename of the background image
$bg_image_info = getimagesize($bg_image_filename);
$bg_image_width = $bg_image_info[0];
$bg_image_height = $bg_image_info[1];

require_once('header.php');
echo '<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>';
echo '<div id="controls_container">
<h2 id="controls_title">Controls info <i class="fas fa-chevron-down"></i></h2>
<div id="controls_wrapper" class="" data-collapsed = "false">
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
      $ID = $json_obj[0]['ID'];
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
      echo '<h1>Please improve the selection</h1>';
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
      echo '</div>
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
};';
echo '</script>';

echo '<button id="confirmBtn">Confirm</button>';
require_once('footer.php');
