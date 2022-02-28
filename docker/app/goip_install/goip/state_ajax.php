<?php

$filename  = dirname(__FILE__).'/data.txt';

// infinite loop until the data file is not modified
$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
$currentmodif = filemtime($filename);

$i=0;
for($i;$i<300 && $currentmodif > $lastmodif ; $i++)
{
  usleep(10000); // sleep 10ms to unload the CPU
  //clearstatcache();
  //$currentmodif = filemtime($filename);
}
if($i==300){
}
else {
	clearstatcache();
}

// return a json array
$response = array();
$response['msg']       = file_get_contents($filename);
$response['timestamp'] = $currentmodif;
echo json_encode($response);
flush();

?>
