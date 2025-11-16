<?php
// Demo endpoint – keep the same contract but avoid writing anything to disk
header('Content-Type: application/json');
$payload = file_get_contents('php://input');
$json = json_decode($payload);

$polygonsEdited = 0;
if ($json && isset($json->values) && is_object($json->values)) {
    $polygonsEdited = count(get_object_vars($json->values));
}

echo json_encode([
    'status' => 'demo',
    'message' => 'Demo mode is enabled – submissions are not persisted on the server.',
    'polygons' => $polygonsEdited,
]);
