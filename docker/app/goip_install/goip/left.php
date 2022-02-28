<?php
        define("OK", true);
        require_once("session.php");
        require_once("global.php");
?>

<html>
<meta name="Author" content="Gaby_chen">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>后台管理</title>
<style type=text/css>
body  { background:#799AE1; margin:0px; font:9pt 宋体; }
table  { border:0px; }
td  { font:normal 16px 宋体; }
img  { vertical-align:bottom; border:0px; }

a  { font:normal 16px 宋体; color:#000000; text-decoration:none; }
a:hover  { color:#428EFF;text-decoration:underline; }

.sec_menu  { border-left:1px solId white; border-right:1px solId white; border-bottom:1px solId white; overflow:hIdden; background:#D6DFF7; }
.menu_title  { }
.menu_title span  { position:relative; top:2px; left:8px; color:#000000; font-weight:bold; }
.menu_title2  { }
.menu_title2 span  { position:relative; top:2px; left:8px; color:#428EFF; font-weight:bold; }

</style>
<SCRIPT language=javascript1.2>
function showsubmenu(ClassId)
{
whichEl = eval("submenu" + ClassId);
if (whichEl.style.display == "none")
{
eval("submenu" + ClassId + ".style.display=\"\";");
}
else
{
eval("submenu" + ClassId + ".style.display=\"none\";");
}
}
</SCRIPT>
</head>
<BODY leftmargin="0" topmargin="0" marginheight="0" marginwIdth="0">
<table wIdth=185 cellpadding=0 cellspacing=0 border=0 align=left>
    <tr><td valign=top>
<table wIdth=185 border="0" align=center cellpadding=0 cellspacing=0>
  <tr>
  </tr>
</table>
<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle0> 
          <span><a href="main.php" target=main><b>管理首页</b></a> | <a href=logout.php target=_top><b>退出</b></a></span> 
        </td>
  </tr>
  <tr>
    <td style="display:" Id='submenu0'>
<div class=sec_menu style="wIdth:185">
<table cellpadding=0 cellspacing=0 align=center wIdth=177>
<tr><td height=20>用户:<?php echo $_SESSION['goip_username'] ?></td>
</tr>
<tr><td height=20>权限:<?php $adm=array("系统管理员","高级管理员","群管理员","组管理员", "GoIP操作者", "GoIP所有者");echo $adm[$_SESSION['goip_permissions']] ?></td>
</tr>
</table>
</div>
	</td>
  </tr>
</table>
<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle1 onClick="showsubmenu(1)" style="cursor:hand;"> 
          <span>发送信息</span> </td>
  </tr>
  <tr>
    <td style="display:" Id='submenu1'>
<div class=sec_menu style="wIdth:185">
<table cellpadding=0 cellspacing=0 align=center wIdth=177>
<tr><td height=20><a href="send.php?type=re" target=main>直接发送</a></td>
</tr>
<?php if(!operator_owner_forbid()) {
?>
<tr><td height=20><a href="send.php?type=all" target=main>向所有人发送</a></td>
</tr>
<tr><td height=20><a href="send.php?type=crowd" target=main>群发送</a></td>
</tr>
<tr><td height=20><a href="send.php?type=group" target=main>组发送</a></td>
</tr>
<?php } ?>
<tr><td height=20><a href="xmlfile.php" target=main>xml文件发送</a></td>
</tr>
<tr><td height=20><a href="filesms.php" target=main>新文件发送</a></td>
</tr>
<tr><td height=20><a href="all_send.php" target=main>全体发送设置</a></td>
</tr>
<tr><td height=20><a href="do_all_send.php" target=main onClick="return confirm('确认用所有在线的终端发送短信?')">一键群发</a></td>
</tr>
<tr><td height=20><a href="cron.php" target=main>定时计划查询</a></td>
</tr>
<tr><td height=20><a href="sendinfo.php" target=main>已发送查询</a></td>
</tr>
<tr><td height=20><a href="sms_count.php" target=main>短信发送数量</a></td>
</tr>
<tr><td height=20><a href="receive.php" target=main>收件箱</a></td>
</tr>
<tr><td height=20><a href="ussd_ch.php" target=main>SIM网络服务</a></td>
</tr>
<tr><td height=20><a href="ussdinfo.php" target=main>USSD查询</a></td>                                          
</tr>
</table>
</div>
	</td>
  </tr>
</table>

<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle7 onClick="showsubmenu(7);" style="cursor:hand;">
          <span>自动查余额与充值</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu7'>
<div class=sec_menu style="wIdth:185">
            <table cellpadding=0 cellspacing=0 align=center wIdth=177>

<tr><td height=20><a href="recharge.php" target=main>自动查余额与充值</a></td>
</tr>
<tr><td height=20><a href="recharge_card.php" target=main>充值卡号</a></td>
</tr>
            </table>
          </div>
        </td>
  </tr>
</table>


<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
    <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle8 onClick="showsubmenu(8)" style="cursor:hand;"> <span>发送人信息管理</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu8'>
      <div class=sec_menu style="wIdth:185">
        <table cellpadding=0 cellspacing=0 align=center wIdth=177>
         <tr><td height=20><a href="user.php?action=modifyself" target=main>修改密码</a></td>
</tr>
<tr><td height=20><a href="user.php?action=modifymsg" target=main>编辑常用语</a></td>
</tr>
<?php if($_SESSION['goip_permissions']<2) {
?>
<tr><td height=20><a href="user.php?job=modify" target=main>管理他人</a></td>
</tr>
<?php } ?>
		  
        </table>
      </div>
    </td>
  </tr>
</table>
<?php if($_SESSION['goip_permissions']<4) {
?>
<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle2 onClick="showsubmenu(2)" style="cursor:hand;"> 
          <span>接收人管理</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu2'>
<div class=sec_menu style="wIdth:185">
            <table cellpadding=0 cellspacing=0 align=center wIdth=177>
<tr><td height=20><a href="receiver.php" target=main>接收人管理</a></td>
</tr>
<?php if($_SESSION['goip_permissions']<2){
echo '<tr><td height=20><a href="receiver.php?action=add" target=main>添加接收人</a></td>
</tr>';
echo '<tr><td height=20><a href="upload.php" target=main>导入接受人信息</a></td>
</tr>';
}
?>
      </table>
	  </div>
	</td>
  </tr>
</table>
<?php }
?>
<?php if($_SESSION['goip_permissions']<2) {
?>
      <table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle44 onClick="showsubmenu(44)" style="cursor:hand;"> 
          <span>群组管理</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu44'>
<div class=sec_menu style="wIdth:185">
<table cellpadding=0 cellspacing=0 align=center wIdth=177>
         <tr><td height=20><a href="crowd.php" target=main>群管理</a></td>
</tr>
<tr><td height=20><a href="groups.php" target=main>组管理</a></td>
</tr>
</table>
	  </div>
	</td>
  </tr>
</table>

<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle4 onClick="showsubmenu(4);" style="cursor:hand;"> 
          <span>数据维护</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu4'>
<div class=sec_menu style="wIdth:185">
            <table cellpadding=0 cellspacing=0 align=center wIdth=177>
			  <tr>
                <td height=20><a href="databackup.php"  target=main>数据备份</a></td>
              </tr>
              <tr>
                <td height=20>
                 <a href="datarestore.php" target=main>数据导入</a></td>
              </tr>
            </table>
	  </div>
	</td>
  </tr>
</table>

<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle7 onClick="showsubmenu(17);" style="cursor:hand;">
          <span>IMEI数据库</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu17'>
<div class=sec_menu style="wIdth:185">
            <table cellpadding=0 cellspacing=0 align=center wIdth=177>

<tr><td height=20><a href="imei_db.php" target=main>IMEI数据库</a></td>
</tr>
            </table>
          </div>
        </td>
  </tr>
</table>
<?php }
?>

<table cellpadding=0 cellspacing=0 wIdth=185 align=center>
  <tr>
        <td height=25 class=menu_title onmouseover=this.className='menu_title2'; onmouseout=this.className='menu_title';  Id=menuTitle4 onClick="showsubmenu(5);" style="cursor:hand;"> 
          <span>系统管理</span> </td>
  </tr>
  <tr>
    <td style="display:none" Id='submenu5'>
<div class=sec_menu style="wIdth:185">
            <table cellpadding=0 cellspacing=0 align=center wIdth=177>
<?php if($_SESSION['goip_permissions']<2) {
?>
	      <tr>
                <td height=20><a href="sys.php"  target=main>系统参数管理</a></td>
              </tr>
              <tr>
                <td height=20>
                 <a href="report.php" target=main>邮件报告</a></td>
              </tr>
<?php }
?>
              <tr>
                <td height=20>
                 <a href="goip_record.php" target=main>通话记录</a></td>
              </tr>
              <tr>
                <td height=20>
                 <a href="goip_cdr.php" target=main>GoIP CDR</a></td>
              </tr>
<?php if($_SESSION['goip_permissions']<2) {
?>
              <tr>
                <td height=20>
                 <a href="provider.php" target=main>服务商修改</a></td>
              </tr>
              <tr>
                <td height=20>
                 <a href="goip_group.php" target=main>GoIP组管理</a></td>
              </tr>
<?php }
?>
              <tr>
                <td height=20>
                 <a href="goip.php" target=main>GoIP参数管理</a></td>
              </tr>	  
            </table>
	  </div>
<div  style="wIdth:185">
<table cellpadding=0 cellspacing=0 align=center wIdth=177>
<tr><td height=20></td></tr>
</table>
	  </div>
	</td>
  </tr>
</table>

	  </div>
	</td>
  </tr>
</table>
</body>
</html>


