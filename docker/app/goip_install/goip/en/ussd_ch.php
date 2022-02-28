<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
//if($_SESSION['goip_permissions'] > 1)
       // die("Permission denied!");

        $query=$db->query("SELECT count(*) AS count FROM goip");
        $row=$db->fetch_array($query);
        $count=$row['count'];
        $numofpage=ceil($count/$perpage);
        $totlepage=$numofpage;
        if(isset($_GET['page'])) {                                                                                
                $page=$_GET['page'];
        } else {
                $page=1;
        }
        if($numofpage && $page>$numofpage) {
                $page=$numofpage;
        }
        if($page > 1) {
                $start_limit=($page - 1)*$perpage;
        } else{
                $start_limit=0;
                $page=1;
        }
        $fenye=showpage("?",$page,$count,$perpage,true,true,"row(s)");
        $query=$db->query("SELECT goip.*,prov.prov FROM goip,prov where goip.provider=prov.id ORDER BY goip.id DESC LIMIT $start_limit,$perpage");
        while($row=$db->fetch_array($query)) {                                                                    
                if($row['alive'])
                        $row['alive']="LOGIN";
                else
                        $row['alive']="LOGOUT";
                $rsdb[]=$row;
	}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../style.css" rel="stylesheet" type="text/css">
<title>USSD</title>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg">
    <td height="22" colspan="2" align="center"><strong>USSD</strong></td>
  </tr>
  <tr class="tdbg">
    <td wIdth="140" height="30"><strong>Navigation:</strong></td>
    <td height="30"><a href="goip.php" target=main>GoIP List</a>&nbsp;|&nbsp;<a href="goip.php?action=add" target=main>Add GoIP</a></td>                                                                                                               
  </tr>
</table>
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
<FORM  name=uploadform action="all_cmd.php" method="POST" enctype="multipart/form-data" onSubmit="return check()">
<center>
<tr><td>
Upload the USSD cmd file(.csv or .xls)<a href="../example/ussd.xls" target="_blank"> [Example]</a><INPUT TYPE="HIdDEN"  name="action" value="mingsenupload">
<input type=file name=img1><INPUT TYPE="SUBMIT" value="Upload">
</td><td align="right"><a href="../log/">Send Logs&nbsp;&nbsp;&nbsp;</a></td></tr>
</center>
</FORM>
</table>
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
  <tr class="topbg">
    <td width="8%">&nbsp;</td>
    <td width="92%" height="25"><strong>Current Location:Choose a goip to use USSD</strong></td>
  </tr>
</table>
<form action="goip.php" method=post name=myform >
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr class=title>
                <td align="center"><b>Login</b></td>
                <td align="center"><b>ID</b></td>
                <td align="center"><b>Provider</b></td>
                <td align="center"><b>Address</b></td>
                <td align="center"><b>Port</b></td>
                <td  align=center><b>Operator</b></td>
        </tr>
<?php
foreach($rsdb as $rs)
print <<<EOT
        <tr class="even">
                <td align="center">{$rs['alive']}</td>
                <td align="center">{$rs['name']}</td>
                <td align="center">{$rs['prov']}</td>
                <td align="center">{$rs['host']}</td>
                <td align="center">{$rs['port']}</td>
                <td align=center><a href="ussd.php?debug=1&TERMID={$rs['name']}">USSD</a> | 
                <a href="callf.php?id={$rs['id']}&reason=0">Call Forward Unconditional</a> | <a href="callf.php?id={$rs['id']}&reason=1">Call Forward Busy</a> |
                <a href="callf.php?id={$rs['id']}&reason=2">Call Forward No Answered</a> | <a href="callf.php?id={$rs['id']}&reason=3">Call Forward Out Of Reach</a>
		</td>
    </tr>

EOT;
?>
</table>
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">
<tr>                                                                      
<td  align=center><?php echo $fenye ?></td>
</tr>
</table>
</form>
</body>
</html>

