<?php
//session to check whether the qualification page was opened
session_start();
if (!isset($_SESSION['qualification'])) {
  //keep the demo self-contained by assuming the visitor can continue
  $_SESSION['qualification'] = true;
}

//get the original crowdsourcing platform params or fall back to demo placeholders
$Campaign_id = $_GET["campaign"] ?? "DEMO_CAMPAIGN";
$Worker_id = $_GET["worker"] ?? ("VISITOR_" . substr(hash('crc32b', session_id()), 0, 6));
$Rand_key = $_GET["rand_key"] ?? bin2hex(random_bytes(4));
$My_secret_key = "2a0f6d1b74e582f9ee21c8e899bb014163431b1491255737db06bb587703cfcf";
// string we will hash to produce VCODE
$String_final = $Campaign_id . $Worker_id . $Rand_key . $My_secret_key;
$vcode_for_proof = "mw-" . hash("sha256", $String_final);

//pick a random job file (no filesystem locking in demo mode)
$job_files = glob('jobs/job_*.txt');
if (!$job_files) {
  @require_once('header.php');
  echo "<section class='info-block'><h1>Demo data missing</h1><p>The jobs folder is empty, so the editor cannot load any examples at the moment.</p></section>";
  @require_once('footer.php');
  exit();
}
$json_filename = $job_files[array_rand($job_files)];
if (preg_match('/job_(\d+)/', basename($json_filename), $matches)) {
  $next_job = $matches[1];
} else {
  $next_job = basename($json_filename);
}
$next_it = 'demo';

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
$data = $data[0];

// Calculate canvas size based on background image
$bg_image_filenames = array();
foreach ($data as $json_obj) {
  $json_obj = $json_obj[0];
  $bg_image_filenames[] = 'pics/' . $json_obj['filename'] . '.png';
}
$bg_image_info = getimagesize($bg_image_filenames[0]);
$bg_image_width = $bg_image_info[0];
$bg_image_height = $bg_image_info[1];

require_once('header.php');

echo '<section class="info-block demo-callout">
<p class="demo-badge">Demo dataset: <strong>Job ' . htmlspecialchars($next_job) . '</strong></p>
<h1>Tree Polygon Editing – Research Demo</h1>
<p>This demo build keeps the full editing experience while running in a read-only mode. When deployed for large crowdsourcing campaigns, a backend service handled worker routing, reserved files, and wrote the annotations you sent back. For demo purposes every visitor simply gets a random job and no data ever leaves this page.</p>
<div class="info-grid">
  <article class="info-card">
    <h3>Original workflow</h3>
    <p>The task distributed thousands of trees to crowd workers, captured their edit logs, and replayed them in an internal dashboard.</p>
  </article>
  <article class="info-card">
    <h3>Demo behaviour</h3>
    <p>Jobs are picked at random, edits stay in the browser, and you can replay historic submissions via the public viewer.</p>
  </article>
  <article class="info-card">
    <h3>Explore more</h3>
    <p>Head over to the sample result viewer to inspect action logs that the production system collected during real campaigns.</p>
  </article>
</div>
<div class="demo-actions">
  <a class="action primary" href="#controls_container">Jump to the editor</a>
  <a class="action secondary" target="_blank" rel="noopener noreferrer" href="userLogsView.php">View sample results</a>
</div>
</section>';

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
          //create new element in the userActions array for this particular task/polygon
          userActions.push({
            "ID_filename": "' . $ID . '",
            "log": []
          });
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
  echo '<button class="nextBtn"><span>Next</span><small>' . ($index + 1) . '/' . count($data) . '</small></button>
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
  random: "' . $Rand_key . '",
  vcode: "' . $vcode_for_proof . '"
};
const dataInfo = {
  file: "' . $json_filename . '",
  image: "' . implode('&', $bg_image_filenames) . '",
  job: "' . $next_job . '",
  iteration: "' . $next_it . '",
};
';
echo '</script>';

echo '<button id="confirmBtn" class="hidden">Submit results</button>';
require_once('footer.php');
