<?php
$firstTime = $_GET['firstTime'];
$Campaign_id = $_GET["campaign"];
$Worker_id = $_GET["worker"];
$Rand_key = $_GET["rand_key"];

@require_once('header.php');
echo '
<h2>Introduction</h2>
<p>Welcome to our tree outline optimization task! You will be presented with a series of pictures that contain a tree and a polygon. Your job is to modify and improve polygons on the canvas. Your effort will help us to improve the accuracy of our automatic tree selection algorithms in the future.</p>
<h2>Task Instructions</h2>
<p>To complete the task please improve the polygon to match the underlying tree, submit your results and you will receive a unique VCODE to claim your payment. </p>
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
';
if ($firstTime == 'true') {
  echo '<button class="toTaskBtn" onclick="startTask()">Start task</button>';
  echo '<script>
    function startTask() {
      window.location.href = "qualification.php?campaign=' . $Campaign_id . '&worker=' . $Worker_id . '&rand_key=' . $Rand_key . '";
    }
    </script>';
}
@require_once('footer.php');
