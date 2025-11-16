<?php
session_start();

$campaign = $_GET["campaign"] ?? "DEMO_CAMPAIGN";
$worker = $_GET["worker"] ?? ("VISITOR_" . substr(hash('crc32b', session_id()), 0, 6));
$rand = $_GET["rand_key"] ?? bin2hex(random_bytes(4));

$_SESSION['qualification'] = true;

$query = http_build_query([
    'campaign' => $campaign,
    'worker' => $worker,
    'rand_key' => $rand
]);

header("Location: index.php?$query");
exit();
