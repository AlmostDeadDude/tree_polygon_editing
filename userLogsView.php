<?php
//first get all the files in the user_logs folder
$files = glob('user_logs/*.json');
require_once('header.php');

//if there are no files in the user_logs folder - return an error message
if (count($files) == 0) {
    echo "<section class='info-block'><h1>No logs available</h1><p>Upload a curated selection of JSON files into <code>user_logs/</code> to power this viewer.</p></section>";
    require_once('footer.php');
    exit();
}

$entries = [];
foreach ($files as $path) {
    $basename = basename($path, '.json');
    $parts = explode('_', $basename);
    $jobId = $parts[1] ?? $basename;
    $iteration = end($parts);
    $entries[] = [
        'basename' => $basename,
        'jobId' => $jobId,
        'iteration' => $iteration,
        'mtime' => filemtime($path)
    ];
}

usort($entries, function ($a, $b) {
    $comparison = strnatcmp($a['jobId'], $b['jobId']);
    if ($comparison === 0) {
        return strnatcmp($a['iteration'], $b['iteration']);
    }
    return $comparison;
});

echo '<section class="info-block">
<h1>Action log library</h1>
<p>Here is a curated list of logged editing sessions pulled from the original crowdsourcing platform. Each entry opens the replay dashboard in a new tab so you can inspect how an annotator refined the polygons.</p>
<p class="muted">Only a handful of the most illustrative jobs are published here; swap files in <code>user_logs/</code> to update the gallery.</p>
<ul class="log-list">';
foreach ($entries as $entry) {
    $readableDate = date('M j, Y H:i', $entry['mtime']);
    echo '<li class="log-entry">
    <div>
      <h3>Job #' . htmlspecialchars($entry['jobId']) . '</h3>
      <p class="log-meta"><span>Iteration: ' . htmlspecialchars($entry['iteration']) . '</span><span>Last updated: ' . $readableDate . '</span></p>
      <small>' . htmlspecialchars($entry['basename']) . '</small>
    </div>
    <a class="action tertiary" href="userLog.php?job=' . urlencode($entry['basename']) . '" target="_blank" rel="noopener noreferrer">Open replay</a>
  </li>';
}
echo '</ul>
<div class="demo-actions">
  <a class="action primary" href="index.php">Back to editor</a>
</div>
</section>';

require_once('footer.php');
