<?php
require_once("session.php");

?>

<html>
<meta name="Author" content="Gaby_chen">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>GOIP SMS管理</title>
</head>
<frameset Id="frame" framespacing="0" border="false" cols="190,*" frameborder="1" scrolling="yes">
	<frame name="left" scrolling="auto" marginwIdth="0" marginheight="0" src="left.php">
	<frameset framespacing="0" border="false" rows="35,*" frameborder="0" scrolling="yes">
		<frame name="top" scrolling="no" src="top.php">
		<frame name="main" scrolling="auto" src="main.php" onload="window.top.frames[1].location.reload()">
	</frameset>
</frameset><noframes></noframes>
</html>
