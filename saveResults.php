<?php
// Get the raw POST data
$data = file_get_contents('php://input');

// Decode the JSON data into a PHP object
$json = json_decode($data);

// Access the fields of the JSON object
$userInfo = $json->userInfo;
$dataInfo = $json->dataInfo;
$values = $json->values;

//merge userInfo and dataInfo into one object
foreach ($userInfo as $key => $value) {
    $dataInfo->$key = $value;
}

// Access individual fields and save them to the results files
//there are two folders containig results files: "results" and "user_info"
//results folder contains txt files named as in 'job'.dataInfo->job.'_'.dataInfo->iteration.'.txt'
//the txt files in the results folder contain the key value pairs from $values
//user_info folder also contains txt files named as in 'job'.dataInfo->job.'_'.dataInfo->iteration.'.txt'
//but the txt files there contain the key value pairs from $userInfo and $dataInfo

//we try to open the files and write the data inside them - if it works - we return "success" to the frontend, otherwise we return "error"
//we also return the error message if there was an error

try {
    file_put_contents("results/job_" . $dataInfo->job . "_" . $dataInfo->iteration . ".txt", json_encode($values));
    file_put_contents("user_info/job_" . $dataInfo->job . "_" . $dataInfo->iteration . ".txt", json_encode($dataInfo));
    echo "success";
} catch (Exception $e) {
    echo "error";
    echo $e->getMessage();
}
