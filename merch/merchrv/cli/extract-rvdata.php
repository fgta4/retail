<?php 

$file = dirname(__FILE__) . '/dummyrvdata.json';
$fp = fopen($file, 'r');

$jsontext = fread($fp, filesize($file));
fclose($fp);

// echo $jsontext;


$data = json_decode($jsontext, true);
if (json_last_error() !== JSON_ERROR_NONE) {
	die(json_last_error_msg());
}

foreach ($data['data']['po'] as $key=>$value) {
	echo $key . "\n";
}
// echo "=============\n";

// print_r($data['data']['ctg']);


