<?php

define('OK',true);
//require_once('global.php');
/*
setcookie("username","");
setcookie("userid","");
setcookie("permissions","");
*/
session_start();

unset($_SESSION['goip_username']);
unset($_SESSION['goip_userid']);
unset($_SESSION['goip_permissions']);
unset($_SESSION['goip_jibie']);
//session_destroy();
echo "<meta http-equiv=refresh content=1;url=\"index.php\">";
?>
