<?php
$vcode = $_GET['vcode'];

@require_once('header.php');

echo '<div id="vcodeContainer">';
echo ($vcode);
echo '</div>
<button id="copyVcodeBtn">Copy VCODE</button>';

@require_once('footer.php');
