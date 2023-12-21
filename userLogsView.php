<?php
//first get all the files in the user_logs folder
$files = glob('user_logs/*');
require_once('header.php');

//if there are no files in the user_logs folder - return an error message
if (count($files) == 0) {
    echo "<h1>⚠️</h1>
    <h1>There are no user logs available.</h1>
    <h2>Check the user_logs folder.</h2>
    ";
    require_once('footer.php');
    exit();
}

//if there are files in the user_logs folder - display them as links to the userLog.php page with the appropriate GET parameters
echo "<h1>". count($files) ." user logs detected:</h1><br><ol>";
foreach ($files as $file) {
    $file = str_replace('user_logs/', '', $file);
    $file = str_replace('.json', '', $file);
    echo "<li><a href='userLog.php?job=" . $file . "' target='_blank' rel='noopener noreferrer'>" . $file . "</a></li>";
}
echo "</ol>";

require_once('footer.php');
?>
