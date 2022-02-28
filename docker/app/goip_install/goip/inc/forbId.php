<?php
$PHP_SELF=@$_SERVER['PHP_SELF'] ? @$_SERVER['PHP_SELF'] : @$_SERVER['SCRIPT_NAME'];
$url='http://'.@$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF, '/')+1);
if(!defined('OK')) {
	header("Location: $url");
}
?>
