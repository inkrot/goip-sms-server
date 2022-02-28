<?php
define("OK", true);
require_once("session.php");
if($_SESSION['goip_permissions'] > 1)	
	die("Permission denied!");	
require_once("global.php");

if($_GET['action']=="save")
{
	if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
	$sql="update prov set ";
	for($i=1;$i<=$_POST['count'];$i++){
		 //addslashes($_POST["msg$i"]);
		if(!get_magic_quotes_gpc()){
			$auto_num_ussd=addslashes($_POST["auto_num_ussd$i"]);
			$num_prefix=addslashes($_POST["num_prefix$i"]);
			$num_postfix=addslashes($_POST["num_postfix$i"]);
		}
		else{ 
			$auto_num_ussd=$_POST["auto_num_ussd$i"];
			$num_prefix=$_POST["num_prefix$i"];
			$num_postfix=$_POST["num_postfix$i"];
		}
		//$sql.=" msg$i='$msg',";
	
		//$sql.=" id=id where username='$_SESSION[username]'";
		$sql="update prov set auto_num_ussd='$auto_num_ussd',num_prefix='$num_prefix',num_postfix='$num_postfix' where id=$i";
		$query=$db->query($sql);
	
	}
	WriteSuccessMsg("<br><li>Modify Success!</li>","auto_num.php");
}	

	$query=$db->query("SELECT * FROM prov ");
	while($row=$db->fetch_array($query)) {
		$rsdb[]=$row;
	}	


	require_once ('auto_num.htm');
?>
