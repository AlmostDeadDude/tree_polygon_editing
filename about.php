<?php
session_start();
$firstTime = $_GET['firstTime'] ?? 'false';
$Campaign_id = $_GET["campaign"] ?? "DEMO_CAMPAIGN";
$Worker_id = $_GET["worker"] ?? ("VISITOR_" . substr(hash('crc32b', session_id()), 0, 6));
$Rand_key = $_GET["rand_key"] ?? bin2hex(random_bytes(4));

@require_once('header.php');
echo '
<section class="info-block">
  <h1>About the Tree Polygon Editing Project</h1>
  <p>This tool started as part of a research series on tree crown delineation. Hundreds of crowd workers refined automatically or manually generated polygons, while the backend tracked every action to improve our models.</p>
  <div class="info-grid">
    <article class="info-card">
      <h3>Research goal</h3>
      <p>Collect high-quality human adjustments to train and validate new tree extraction workflows for forestry, agriculture, and urban planning.</p>
    </article>
    <article class="info-card">
      <h3>Task design</h3>
      <p>A lightweight HTML5 canvas app let workers move, add, or delete polygon vertices directly on drone imagery.</p>
    </article>
    <article class="info-card">
      <h3>Demo mode</h3>
      <p>The live demo keeps the full UX but does not write new files. Instead, it points to a curated set of historic results.</p>
    </article>
  </div>
  <div class="demo-actions">
    <a class="action primary" href="index.php">Try the editor</a>
    <a class="action secondary" target="_blank" rel="noopener noreferrer" href="userLogsView.php">Replay past submissions</a>
  </div>
</section>
<section class="info-block">
<h2>Task Instructions</h2>
<p>To complete the task please improve the polygon to match the underlying tree, submit your results and you will receive a unique proof code to claim your payment.</p>
<h2>How to Use:</h2>
  <ul>
    <li><strong>Move Points:</strong> Hover over a point to highlight it (lightblue color). Click and drag the active point (yellow color) to move it to a new position. When you release the mouse, the point will stay at the last position.
    <br>
    <img src="pics/examples/move.gif" alt="move" width=720>
    </li>
    <li><strong>Insert Points:</strong> To insert a new point between two existing points, click on the line connecting those points. A new point will be created at that location, and the polygon will adjust automatically.
    <br>
    <img src="pics/examples/add.gif" alt="insert" width=720>
    </li>
    <li><strong>Delete Points:</strong> Select the point you want to delete and press the delete button on your keyboard. The polygon will adjust automatically.
    <br>
    <img src="pics/examples/delete.gif" alt="delete" width=720>
    </li>
  </ul>

<h2>Conclusion</h2>
<p>Thank you for helping with our research! Your contributions will help us to improve our automatic tree selection algorithms, which will have a wide range of applications in fields such as agriculture, forestry, and urban planning. We appreciate your time and effort, and wish you good luck with the task.</p>
</section>
';
// if ($firstTime == 'true') {
if (true) {
  echo '<button class="toTaskBtn" onclick="startTask()">Start task</button>';
  echo '<script>
    function startTask() {
      window.location.href = "qualification.php?campaign=' . $Campaign_id . '&worker=' . $Worker_id . '&rand_key=' . $Rand_key . '";
    }
    </script>';
}
@require_once('footer.php');
