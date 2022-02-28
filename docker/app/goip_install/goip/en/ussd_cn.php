<?php
session_start();
$debug=$_REQUEST[debug];
define("OK", true);
require_once("global.php");
//if($_SESSION['goip_permissions'] > 1)	
	//die("Permission denied!");

if(!isset($_SESSION['goip_username'])){
	//echo "SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'";
        $rs=$db->fetch_array($db->query("SELECT id FROM user WHERE username='".$_REQUEST[USERNAME]."' and password='".md5($_REQUEST[PASSWORD])."'"));
        if(empty($rs[0])){
		require_once ('login.php');
                exit;
        }
}

?>
<html>                                                                                                            
<head>                                                                                                            
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">                                               
<link href="style.css" rel="stylesheet" type="text/css">                                                          
<title>USSD</title>                                                                                               
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">   

<?php

function ok_over($TERMID, $USSD_MSG, $USSD_RETURN)
{
        global $db;                                                                                               
        $db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$USSD_MSG', USSD_RETURN='$USSD_RETURN', INSERTTIME=now()"); 
}                                                                                                                 
                                                                                                                  
function error_over($TERMID, $USSD_MSG, $ERROR_MSG)                                                               
{                                                                                                                 
        global $db;                                                                                               
        $db->query("insert into USSD set TERMID='$TERMID', USSD_MSG='$USSD_MSG', ERROR_MSG='$ERROR_MSG', INSERTTIME=now()");     
} 

if($goipcronport)
        $port=$goipcronport;
else
        $port=44444;

//echo "SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and goip.name=$_REQUEST[TERMID]";
if($_GET[id]) $query=$db->query("SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and goip.id=$_GET[id]");
else $query=$db->query("SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and goip.name='$_REQUEST[TERMID]'");



if(($goiprow=$db->fetch_array($query)) ==NULL){
        $errormsg=("ERROR Not find this TERM");                                                                 
        echo $errormsg;                                                                                           
        error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], $errormsg);
}

if(isset($_REQUEST['USSDMSG'])){                                                                                      
$recvid=time();                                                                                                   
ignore_user_abort(true);                                                                                          
set_time_limit(0);                                                                                                
//echo str_pad(" ", 256);                                                                                         

if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {                                               
        $errormsg = "ERROR socket_create() failed: reason: " . socket_strerror($socket) . "\n";     
        echo $errormsg;
        error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], $errormsg);                   
        exit;                                                                                                     
}                                                                                                                 
for($i=0;$i<3;$i++){                                                                                              
        $read=array($socket);                                                                                     
        $buf="START $recvid $goiprow[host] $goiprow[port]\n";                                             
        if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false){                           
                $errormsg = "ERROR sendto error".socket_strerror($socket) . "\n";
                echo $errormsg;
                error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], $errormsg);                                       
                exit;                                                                                             
        }
        $err=socket_select($read, $write = NULL, $except = NULL, 5);                                              
        if($err>0){                                                                                               
                if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){                                 
                        if($debug) echo("recvform error".socket_strerror($ret)."<br>");                           
                        continue;                                                                                 
                }                                                                                                 
                else{                                                                                             
                        if($buf=="OK"){                                                                           
                                $flag=1;                                                                          
                                break;                                                                            
                        }                                                                                         
                }                                                                                                 
        }

}//for
if($i>=3) {
        error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], "goipcron no response");                                  
        if($debug) die("goipcron no response");                                                              
        else echo "ERROR goipcron no response";
        exit;
}

if($_REQUEST['action'] == 'exit')                                                                                 
        $sendbuf="USSDEXIT ".$recvid." ".$goiprow[password];                                                      
else
        $sendbuf="USSD ".$recvid." ".$goiprow[password]." ".$_REQUEST['USSDMSG'];                                     

$socks[]=$socket;                                                                                                 
$timer=2;
$timeout=10;                                                                                                      
if (@socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, "127.0.0.1", $port)===false)                            
                echo ("ERROR sendto error");                                                                      
for(;;){
        $read=$socks;                                                                                             
        flush();                                                                                                  
        if(count($read)==0)                                                                                       
                break;                                                                                            
        $err=socket_select($read, $write = NULL, $except = NULL, $timeout);                                       
        if($err===false)                                                                                          
                echo "ERROR select error!";                                                                       
        elseif($err==0){ //全体超时                                                                               
                if(--$timer <= 0){                                                                                
                        if($debug) echo "<script language=\"javascript\">alert('Timeout! Not get response from Goip')</script>";  
                        else $errormsg = "ERROR term no response";                                                
                        break;                                                                                    
                }                                                                                                 
                if (@socket_sendto($socket,$sendbuf, strlen($sendbuf), 0, "127.0.0.1", $port)===false)         

                        echo ("ERROR sendto error");
        }
        else {
                if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
                                        echo("recvform error".socket_strerror($ret)."<br>");
                                        continue;
                                }

                $comm=explode(" ",$buf);
                if($comm[0] == "USSD") {
                        array_shift($comm);
                        array_shift($comm);
                        //$ussdmsg=$comm[2];
                        $ussdmsg=implode(" ", $comm);
                        if($ussdmsg != "USSD send failed!"){
                                if(!$debug) echo "OK $ussdmsg";
                                ok_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], $ussdmsg);
                        }
                        else $errormsg="ERROR ".$ussdmsg;
                        //$i=0;
                        $ussdmsg=htmlspecialchars($ussdmsg);
                        $ussdmsg=str_replace("\n", "<br>", $ussdmsg);
                        //echo $buf."<br>";
                        //print_r($ussdmsg);
                        break;
                }
                else if($comm[0] == "USSDERROR"){
                        array_shift($comm);
                        array_shift($comm);
                        $errormsg=implode(" ",$comm);
                        if($debug) echo "<script language=\"javascript\">alert('error! $errormsg ')</script>";
                        else $errormsg="ERROR $errormsg";

                       break;                                                                                    
                }                                                                                                 
                else if($comm[0] == "USSDEXIT"){                                                                  
                        //echo "1";                                                                               
                        if($debug) echo "<script language=\"javascript\">alert('USSD disconnected! ')</script>";         
                        break;                                                                                    
                }                                                                                                 

        }

}
if($errormsg){                                                                                                    
        error_over($_REQUEST[TERMID], $_REQUEST['USSDMSG'], $errormsg);                                               
}
if(!$debug && $errormsg) {                                                                                        
        echo $errormsg;                                                                                           
        
}
$buf1="DONE $recvid";
if (@socket_sendto($socket,$buf1, strlen($buf1), 0, "127.0.0.1", $port)===false)
        echo ("sendto error");


}

if($debug){
?>
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg">
    <td height="22" colspan="2" align="center"><strong>USSD</strong></td>
  </tr>
  <tr class="tdbg"> 
    <td wIdth="140" height="30"><strong>Navigation::</strong></td>
    <td height="30"><a href="goip.php" target=main>GoIP List</a>&nbsp;|&nbsp;<a href="goip.php?action=add" target=main>Add Goip</a>&nbsp;|&nbsp;<a href="ussdinfo.php" target=main>USSD Records</a></td>
  </tr>
</table>

<form method="post" action="ussd.php?debug=1&TERMID=<?php echo $_REQUEST[TERMID] ?>" name="form1">
  <br>
  <br>
  <table wIdth="600" border="0" align="center" cellpadding="1" cellspacing="1" class="border" >
    <tr class="title"> 
      <td height="22" colspan="2"> <div align="center"><strong>GOIP(<?php echo $goiprow[name] ?>)USSD</strong></div></td>
    </tr>
    <tr>
	<td wIdth="180" align="right" class="tdbg"><strong>USSD Return Information:</strong></td>
      <td height="22"  class="tdbg"><?php echo $ussdmsg ?></td>
    </tr>
    <tr> 
      <td wIdth="180" align="right" class="tdbg"><strong>USSD Command:</strong></td>
      <td class="tdbg"><input type="input" id="USSDMSG" name="USSDMSG" >  &nbsp;&nbsp;&nbsp;&nbsp;  <a href="ussd.php?TERMID=<?php echo $_REQUEST[TERMID]?>&debug=1&USSDMSG=1&action=exit" target=main onclick="return confirm('Sure to disconnect ussd?');">Disconnect</a></td>
    </tr>
    <tr>                                                                                                          
      <td height="40" colspan="2" align="center" class="tdbg"><input name="Id" type="hIdden" Id="Id" value="{$rs['id']}">
      		<input  type="submit" name="Submit" value="Send" style="cursor:hand;">
	</td>
    </tr>                                                                                                         
  </table>                                                                                                        
</form> 
</body>
</html>
<?php 
}
?>
