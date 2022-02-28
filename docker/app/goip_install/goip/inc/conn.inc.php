<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
ini_set("memory_limit", "500M");
/*
	$dbhost='localhost';	//database server
	$dbuser='goip';		//database username
	$dbpw='goip';		//database password
	$dbname='goip';		//database name 哈
//*/
//ob_start();


include_once 'config.inc.php';
include_once("version.php");
include_once 'forbId.php';

//var_dump(headers_list());


//var_dump(headers_list());
/*
$dbhost1=$dbhost;
$dbuser1=$dbuser;
$dbpw1=$dbpw;
$dbname1=$dbname;
*/
function myaddslashes($var)
{
	if(!get_magic_quotes_gpc())
		return addslashes($var);
	else
		return $var;
}

class DB {
	function DB(){
		global $dbhost,$dbuser,$dbpw,$dbname;

		$conn=mysql_connect($dbhost,$dbuser,$dbpw) or die("Could not connect");
		mysql_select_db($dbname,$conn);
		mysql_query("SET NAMES 'utf8'");		
		mysql_query("set sql_mode='ANSI'");
	}
	function query($sql) {

		$result=mysql_query($sql) or die("Bad query: ".mysql_error()."($sql)");
		return $result;
	}
	function updatequery($sql) {

                $result=mysql_query($sql);
                return $result;
        }

	function fetch_array($query) {
		return mysql_fetch_array($query);
	}
	
	function fetch_assoc($query) {
		return mysql_fetch_assoc($query);
	}
	
	function num_rows($query) {
		return mysql_num_rows($query);
	}
	function real_escape_string($item){
		return mysql_real_escape_string($item);
	}
}

$db=new DB;

?>
