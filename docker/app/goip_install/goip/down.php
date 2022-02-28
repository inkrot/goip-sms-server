<?php
$filename=$_GET["filename"];

	ob_end_clean();
			
	header("Expires: 0");
$file_name=str_replace("backup/","",$filename);
//Header("Location: $file_name"); 
 
?>
