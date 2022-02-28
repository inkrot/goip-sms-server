<?php
	require_once("mail.php");
	if($argv<4) exit;
	send_mail($argv[1], $argv[2], $argv[3]);
?>
