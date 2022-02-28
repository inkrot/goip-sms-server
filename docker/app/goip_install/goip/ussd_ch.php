<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
//if($_SESSION['goip_permissions'] > 1)
        //die("Permission denied!");

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
        $fenye=showpage("?",$page,$count,$perpage,true,true,"编");
        $query=$db->query("SELECT goip.*,prov.prov FROM goip,prov where goip.provider=prov.id ORDER BY goip.id DESC LIMIT $start_limit,$perpage");
        while($row=$db->fetch_array($query)) {                                                                    
                if($row['alive'])
                        $row['alive']="已注册";
                else
                        $row['alive']="未注册";
                $rsdb[]=$row;
	}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>SIM网络服务</title>
<body leftmargin="2" topmargin="0" marginwIdth="0" marginheight="0">
<table wIdth="100%" border="0" align="center" cellpadding="2" cellspacing="1" class="border">
  <tr class="topbg">
    <td height="22" colspan="2" align="center"><strong>SIM网络服务</strong></td>
  </tr>
  <tr class="tdbg">
    <td wIdth="140" height="30"><strong>goip管理导航:</strong></td>
    <td height="30"><a href="goip.php" target=main>参数管理</a>&nbsp;|&nbsp;<a href="goip.php?action=add" target=main>添加机器</a></td>
  </tr>
</table>
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
<FORM  name=uploadform action="all_cmd.php" method="POST" enctype="multipart/form-data" onSubmit="return check()">
<center>
<tr><td>
导入USSD指令文件(.csv或.xls)<a href="example/ussd.xls" target="_blank"> [例子]</a><INPUT TYPE="HIdDEN"  name="action" value="mingsenupload">
<input type=file name=img1><INPUT TYPE="SUBMIT" value="上 传">
</td><td align="right"><a href="log/">文件发送记录&nbsp;&nbsp;&nbsp;</a></td></tr>
</center>                                                                                                         
</FORM>
</table>
<table width="100%" height="25"  border="0" cellpadding="0" cellspacing="0">
  <tr class="topbg">
    <td width="8%">&nbsp;</td>
    <td width="92%" height="25"><strong>当前位置：选择goip进行网络服务</strong></td>
  </tr>
</table>
<form action="goip.php" method=post name=myform >
<table wIdth="100%"  border="0" cellspacing="2" cellpadding="2">
        <tr class=title>
                <td align="center"><b>注册</b></td>
                <td align="center"><b>ID</b></td>
                <td align="center"><b>服务商</b></td>
                <td align="center"><b>地址</b></td>
                <td align="center"><b>端口</b></td>
                <td  align=center><b>操作</b></td>
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
<a href="callf.php?id={$rs['id']}&reason=0">无条件转移</a> | <a href="callf.php?id={$rs['id']}&reason=1">遇忙转移</a> | 
<a href="callf.php?id={$rs['id']}&reason=2">无应答转移</a> | <a href="callf.php?id={$rs['id']}&reason=3">不可及转移</a>
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

