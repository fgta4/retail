<?php 

$file = dirname(__FILE__) . '/dummyregister.json';
$fp = fopen($file, 'r');

$jsontext = fread($fp, filesize($file));
fclose($fp);


$data = json_decode($jsontext, true);

foreach ($data['data']['ctg'] as $key=>$value) {
	echo $key . ": " . $value ."\n";
}
echo "=============\n";

// print_r($data['data']['ctg']);


