<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
require_once("../mail.php");
//!defined('OK') && exit('ForbIdden');
//$UserName=$_SESSION['goip_adminname'];
//print_r($_REQUEST);
if(isset($_REQUEST['action'])) {
	$action=$_REQUEST['action'];

	if($action=="email")
	{
		$rs=$db->fetch_array($db->query("SELECT * FROM system "));
	}
	elseif($action=="send_mail")
	{
		$title=myaddslashes($_REQUEST['title']);
		$content=myaddslashes($_REQUEST['content']);
		test_mail($_REQUEST['smtp_mail'],$_REQUEST['smtp_server'], $_REQUEST['smtp_port'], $_REQUEST['smtp_user'], $_REQUEST['smtp_pass'],$_REQUEST['report_mail'],$title,$content);
		WriteSuccessMsg("<br><li>Test Done:</li>","?action=email");
	}
	elseif($action=="savemodify")
	{
		//echo "11111";
		if(operator_owner_forbid()) WriteErrMsg('<br><li>forbidden</li>');
		$ErrMsg="";
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$_POST[info]=myaddslashes($_POST[info]);
			if($_POST['gsm_logout_enable']=='on') $_POST['gsm_logout_enable']=1;
			else $_POST['gsm_logout_enable']=0;
			if($_POST['reg_logout_enable']=='on') $_POST['reg_logout_enable']=1;
			else $_POST['reg_logout_enable']=0;
			if($_POST['remain_timeout_enable']=='on') $_POST['remain_timeout_enable']=1;
			else $_POST['remain_timeout_enable']=0;
			if($_POST['email_forward_sms_enable']=='on') $_POST['email_forward_sms_enable']=1;
			else $_POST['email_forward_sms_enable']=0;
			if($_POST['remain_count_enable']=='on') $_POST['remain_count_enable']=1;
			else $_POST['remain_count_enable']=0;
			if($_POST['remain_count_d_enable']=='on') $_POST['remain_count_d_enable']=1;
			else $_POST['remain_count_d_enable']=0;
	
			$db->query("UPDATE system SET smtp_mail='$_POST[smtp_mail]',smtp_server='$_POST[smtp_server]',smtp_port='$_POST[smtp_port]',smtp_user='$_POST[smtp_user]',smtp_pass='$_POST[smtp_pass]',email_report_gsm_logout_enable='$_POST[gsm_logout_enable]',email_report_gsm_logout_time_limit='$_POST[gsm_logout_time_limit]',email_report_reg_logout_enable='$_POST[reg_logout_enable]',email_report_reg_logout_time_limit='$_POST[reg_logout_time_limit]',report_mail='$_POST[report_mail]',email_report_remain_timeout_enable='$_POST[remain_timeout_enable]',email_forward_sms_enable='$_POST[email_forward_sms_enable]', email_remain_count_enable='$_POST[remain_count_enable]',email_remain_count_d_enable='$_POST[remain_count_d_enable]'");
			
			
			sendto_cron("report_init");
			WriteSuccessMsg("<br><li>Modify Success:</li>","?");
		}
	}
	else $action="main";

}
else $action="main";

if($action=="main"){
        $query=$db->query("SELECT * FROM system WHERE 1 ");
        $rs=$db->fetch_array($query);
	if($rs['email_report_gsm_logout_enable']) $gsm_logout_checked="checked";
	else $gsm_logout_display="none";
	if($rs['email_report_reg_logout_enable']) $reg_logout_checked="checked";
	else $reg_logout_display="none";
	if($rs['email_report_remain_timeout_enable']) $remain_timeout_checked="checked";
	else $remain_timeout_display="none";
	if($rs['email_forward_sms_enable']) $email_forward_sms_checked="checked";
	else $email_forward_sms_display="none";
	if($rs['email_remain_count_enable']) $remain_count_checked="checked";
	else $remain_count_display="none";
	if($rs['email_remain_count_d_enable']) $remain_count_d_checked="checked";
	else $remain_d_count_display="none";

}
require_once ('report.htm');

?>
