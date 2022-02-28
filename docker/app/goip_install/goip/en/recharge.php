<?php

define("OK", true);
require_once("session.php");
//if($_SESSION['goip_permissions'] > 1)	
	//die("Permission denied!");	
require_once("global.php");
//!defined('OK') && exit('ForbIdden');
//$UserName=$_SESSION['goip_adminname'];

if(isset($_GET['action'])) {
	//if($_GET['action'] != "modifyself" && $_GET['action'] != "savemodifyself" && $_SESSION['goip_adminname']!="admin" )
		//WriteErrMsg("<br><li>需要admin权限!</li>");
	$action=$_GET['action'];
	
	if($action=="del")
	{
		$ErrMsg="";
		$Id=$_GET['id'];

		if(empty($Id)){
			$num=$_POST['boxs'];
			for($i=0;$i<$num;$i++)
			{	
				if(!empty($_POST["Id$i"])){
					if($Id=="")
						$Id=$_POST["Id$i"];
					else
						$Id=$_POST["Id$i"].",$Id";
				}
			}
		}
		//WriteErrMsg($num."$Id");

		if(empty($Id))
			$ErrMsg ='<br><li>Please choose one</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$db->query("DELETE FROM auto_ussd WHERE id IN ($Id)");
			sendto_cron("recharge");
			WriteSuccessMsg("<br><li>Delete successfully</li>","recharge.php");

		}
		//$id=$_GET['id'];
		//$query=$db->query("Delete  from ".$tablepre."admin WHERE Id=$id");
	}
	elseif($action=="add")
	{
		$query=$db->query("select id,prov from prov ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}
		$query=$db->query("select id,group_name from goip_group ");
		while($row=$db->fetch_array($query)) {
			$ggrsdb[]=$row;
		}

		$query=$db->query("select id,name from goip order by id");
		while($row=$db->fetch_array($query)) {
			$grsdb[]=$row;
		}
	}
	elseif($action=="modify")
	{
		$id=$_GET['id'];
		$query=$db->query("select id,prov from prov ");
		while($row=$db->fetch_array($query)) {
			$prsdb[]=$row;
		}
		$query=$db->query("select id,group_name from goip_group ");
		while($row=$db->fetch_array($query)) {
			$ggrsdb[]=$row;
		}

		$query=$db->query("select id,name from goip order by id");
		while($row=$db->fetch_array($query)) {
			$grsdb[]=$row;
		}
		$rs=$db->fetch_array($db->query("SELECT * FROM auto_ussd where id=$id"));
		if($rs['recharge_type']==1) {
			$select1='selected'; 
			$display_re='';
		}
		else if($rs['recharge_type']==2){
			$select2='selected';
			$display_re='none';
		}
		else {
			$select0='selected';
			$display_re='none';
		}
		if($rs['recharge_con_type']==2){
			$re_con_select2='selected';
		}else if($rs['recharge_con_type']==3){
			$re_con_select3='selected';
		}else $re_con_select0='selected';
	}
	elseif($action=="saveadd")
	{
		$_POST['name']=myaddslashes($_POST['name']);
		$_POST['auto_ussd']=myaddslashes($_POST['auto_ussd']);
		$_POST['bal_sms_r']=myaddslashes($_POST['bal_sms_r']);
		$_POST['bal_ussd_r']=myaddslashes($_POST['bal_ussd_r']);
		$_POST['recharge_ussd']=myaddslashes($_POST['recharge_ussd']);
		$_POST['auto_sms_msg']=myaddslashes($_POST['auto_sms_msg']);
		$_POST['recharge_ussd1']=myaddslashes($_POST['recharge_ussd1']);
		$_POST['recharge_ok_r']=myaddslashes($_POST['recharge_ok_r']);
		$_POST['recharge_ok_r2']=myaddslashes($_POST['recharge_ok_r2']);
		$_POST['bal_ussd_zero_match_char']=myaddslashes($_POST['bal_ussd_zero_match_char']);
		$_POST['bal_sms_zero_match_char']=myaddslashes($_POST['bal_sms_zero_match_char']);
		$_POST['auto_ussd_step2_start_r']=myaddslashes($_POST['auto_ussd_step2_start_r']);
		$_POST['auto_ussd_step2']=myaddslashes($_POST['auto_ussd_step2']);
		$_POST['auto_ussd_step3_start_r']=myaddslashes($_POST['auto_ussd_step3_start_r']);
		$_POST['auto_ussd_step3']=myaddslashes($_POST['auto_ussd_step3']);
		$_POST['auto_ussd_step4_start_r']=myaddslashes($_POST['auto_ussd_step4_start_r']);
		$_POST['auto_ussd_step4']=myaddslashes($_POST['auto_ussd_step4']);

		$_POST['recharge_sms_num']=myaddslashes($_POST['recharge_sms_num']);
		$_POST['recharge_sms_msg']=myaddslashes($_POST['recharge_sms_msg']);
		$_POST['recharge_sms_ok_num']=myaddslashes($_POST['recharge_sms_ok_num']);
		$_POST['sms_report_goip']=myaddslashes($_POST['sms_report_goip']);
		$_POST['bal_delay']=myaddslashes($_POST['bal_delay']);
		if($_POST['disable_when_if_bal']!=1) $_POST['disable_when_if_bal']=0;
		if($_POST['auto_disconnect_after_bal']!=1) $_POST['auto_disconnect_after_bal']=0;
		if($_POST['disable_if_ussd2_undone']!=1) $_POST['disable_if_ussd2_undone']=0;
		if($_POST['disable_callout_when_bal']!=1) $_POST['disable_callout_when_bal']=0;
		if($_POST['re_step2_enable']!=1) $_POST['re_step2_enable']=0;
		$_POST['re_step2_cmd']=myaddslashes($_POST['re_step2_cmd']);
		$_POST['re_step2_ok_r']=myaddslashes($_POST['re_step2_ok_r']);
		$_POST['auto_reset_remain_enable']=myaddslashes($_POST['auto_reset_remain_enable']);

		$_POST['recharge_con_type']=myaddslashes($_POST['recharge_con_type']);
		$_POST['fixed_time']=myaddslashes($_POST['fixed_time']);
		$_POST['remain_limit']=myaddslashes($_POST['remain_limit']);
		$_POST['remain_set']=myaddslashes($_POST['remain_set']);

		$ErrMsg="";
		if(empty($_POST['name']))
			$ErrMsg ='<br><li>please input name</li>';
		$no_t=$db->fetch_array($db->query("select id from auto_ussd where name='".$_POST['name']."'"));
		if($no_t[0])
			$ErrMsg	.='<br><li>This name already exist:  '.$_POST['name'].'</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$sql="INSERT INTO auto_ussd (name,prov_id,group_id,auto_ussd,crontime,bal_sms_r,bal_ussd_r,bal_limit,recharge_ussd,send_sms,type,auto_sms_num,auto_sms_msg,recharge_type,recharge_ussd1,recharge_ussd1_goip,recharge_ok_r,recharge_ok_r2,bal_ussd_zero_match_char,bal_sms_zero_match_char,disable_if_low_bal,auto_disconnect_after_bal,disable_callout_when_bal,ussd2,ussd2_ok_match,ussd22,ussd22_ok_match,send_mail2,disable_if_ussd2_undone,recharge_limit,send_email,send_sms2,recharge_sms_num,recharge_sms_msg,recharge_sms_ok_num,auto_ussd_step2,auto_ussd_step2_start_r,sms_report_goip,bal_delay,re_step2_enable,re_step2_cmd,re_step2_ok_r,auto_reset_remain_enable,auto_ussd_step3,auto_ussd_step3_start_r,auto_ussd_step4,auto_ussd_step4_start_r,recharge_con_type,fixed_time,remain_limit,remain_set)";
			$sql.=" VALUES ('$_POST[name]','$_POST[prov_id]','$_POST[group_id]','$_POST[auto_ussd]','$_POST[crontime]','$_POST[bal_sms_r]','$_POST[bal_ussd_r]','$_POST[bal_limit]','$_POST[recharge_ussd]','$_POST[send_sms]','$_POST[type]','$_POST[auto_sms_num]','$_POST[auto_sms_msg]','$_POST[recharge_type]','$_POST[recharge_ussd1]','$_POST[recharge_ussd1_goip]','$_POST[recharge_ok_r]','$_POST[recharge_ok_r2]','$_POST[bal_ussd_zero_match_char]','$_POST[bal_sms_zero_match_char]','$_POST[disable_if_low_bal]','$_POST[auto_disconnect_after_bal]','$_POST[disable_callout_when_bal]','$_POST[ussd2]','$_POST[ussd2_ok_match]','$_POST[ussd22]','$_POST[ussd22_ok_match]','$_POST[send_mail2]','$_POST[disable_if_ussd2_undone]','$_POST[recharge_limit]','$_POST[send_email]','$_POST[send_sms2]','$_POST[recharge_sms_num]','$_POST[recharge_sms_msg]','$_POST[recharge_sms_ok_num]','$_POST[auto_ussd_step2]','$_POST[auto_ussd_step2_start_r]','$_POST[sms_report_goip]','$_POST[bal_delay]','$_POST[re_step2_enable]','$_POST[re_step2_cmd]','$_POST[re_step2_ok_r]','$_POST[auto_reset_remain_enable]','$_POST[auto_ussd_step3]','$_POST[auto_ussd_step3_start_r]','$_POST[auto_ussd_step4]','$_POST[auto_ussd_step4_start_r]','$_POST[recharge_con_type]','$_POST[fixed_time]','$_POST[remain_limit]','$_POST[remain_set]')";
			$query=$db->query($sql);
			if($_POST['recharge_con_type']==2){
				$id=$db->fetch_array($db->query("SELECT LAST_INSERT_ID()"));
				$db->query("update auto_ussd set fixed_next_time=if(ADDTIME(TIMESTAMP(CURDATE()), '$_POST[fixed_time]')>now(), UNIX_TIMESTAMP(ADDTIME(TIMESTAMP(CURDATE()), '$_POST[fixed_time]')), UNIX_TIMESTAMP(ADDTIME(TIMESTAMP(CURDATE(), 240000), '$_POST[fixed_time]'))) where id=$id[0]");
			}

			sendto_cron("recharge");
			WriteSuccessMsg("<br><li>Add successfully</li>","?");
		}
	}
	elseif($action=="savemodify")
	{
                $_POST['name']=myaddslashes($_POST['name']);
                $_POST['auto_ussd']=myaddslashes($_POST['auto_ussd']);
                $_POST['bal_sms_r']=myaddslashes($_POST['bal_sms_r']);
                $_POST['bal_ussd_r']=myaddslashes($_POST['bal_ussd_r']);
                $_POST['recharge_ussd']=myaddslashes($_POST['recharge_ussd']);
		$_POST['auto_sms_msg']=myaddslashes($_POST['auto_sms_msg']);
		$_POST['recharge_ussd1']=myaddslashes($_POST['recharge_ussd1']);
		$_POST['recharge_ok_r']=myaddslashes($_POST['recharge_ok_r']);
		$_POST['recharge_ok_r2']=myaddslashes($_POST['recharge_ok_r2']);
                $_POST['bal_ussd_zero_match_char']=myaddslashes($_POST['bal_ussd_zero_match_char']);
                $_POST['bal_sms_zero_match_char']=myaddslashes($_POST['bal_sms_zero_match_char']);
		$_POST['auto_ussd_step2_start_r']=myaddslashes($_POST['auto_ussd_step2_start_r']);
		$_POST['auto_ussd_step2']=myaddslashes($_POST['auto_ussd_step2']);
		$_POST['auto_ussd_step3_start_r']=myaddslashes($_POST['auto_ussd_step3_start_r']);
		$_POST['auto_ussd_step3']=myaddslashes($_POST['auto_ussd_step3']);
		$_POST['auto_ussd_step4_start_r']=myaddslashes($_POST['auto_ussd_step4_start_r']);
		$_POST['auto_ussd_step4']=myaddslashes($_POST['auto_ussd_step4']);

		$_POST['recharge_sms_num']=myaddslashes($_POST['recharge_sms_num']);
		$_POST['recharge_sms_msg']=myaddslashes($_POST['recharge_sms_msg']);
		$_POST['recharge_sms_ok_num']=myaddslashes($_POST['recharge_sms_ok_num']);
                if($_POST['disable_if_low_bal']!=1) $_POST['disable_if_low_bal']=0;
		if($_POST['auto_disconnect_after_bal']!=1) $_POST['auto_disconnect_after_bal']=0;
		if($_POST['disable_if_ussd2_undone']!=1) $_POST['disable_if_ussd2_undone']=0;
		if($_POST['disable_callout_when_bal']!=1) $_POST['disable_callout_when_bal']=0;
		$_POST['sms_report_goip']=myaddslashes($_POST['sms_report_goip']);
		$_POST['bal_delay']=myaddslashes($_POST['bal_delay']);
		if($_POST['re_step2_enable']!=1) $_POST['re_step2_enable']=0;
		$_POST['re_step2_cmd']=myaddslashes($_POST['re_step2_cmd']);
		$_POST['re_step2_ok_r']=myaddslashes($_POST['re_step2_ok_r']);
		$_POST['auto_reset_remain_enable']=myaddslashes($_POST['auto_reset_remain_enable']);

		$_POST['recharge_con_type']=myaddslashes($_POST['recharge_con_type']);
		$_POST['fixed_time']=myaddslashes($_POST['fixed_time']);
		$_POST['remain_limit']=myaddslashes($_POST['remain_limit']);
		$_POST['remain_set']=myaddslashes($_POST['remain_set']);

		$Id=$_POST['Id'];
		$name=$_POST['name'];
		$ErrMsg="";
		$no_t=$db->fetch_array($db->query("select id from auto_ussd where name='".$name."' and id != $Id" ));
		if($no_t[0])
			$ErrMsg	.='<br><li>This name already exist:  '.$name.'</li>';					
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$db->query("UPDATE auto_ussd SET name='$name',prov_id='$_POST[prov_id]',group_id='$_POST[group_id]',auto_ussd='$_POST[auto_ussd]',crontime='$_POST[crontime]',bal_sms_r='$_POST[bal_sms_r]',bal_ussd_r='$_POST[bal_ussd_r]',bal_limit='$_POST[bal_limit]',recharge_ussd='$_POST[recharge_ussd]',send_sms='$_POST[send_sms]',type='$_POST[type]',auto_sms_num='$_POST[auto_sms_num]',auto_sms_msg='$_POST[auto_sms_msg]',recharge_type='$_POST[recharge_type]',recharge_ussd1='$_POST[recharge_ussd1]',recharge_ussd1_goip='$_POST[recharge_ussd1_goip]',recharge_ok_r='$_POST[recharge_ok_r]',recharge_ok_r2='$_POST[recharge_ok_r2]',bal_ussd_zero_match_char='$_POST[bal_ussd_zero_match_char]',bal_sms_zero_match_char='$_POST[bal_sms_zero_match_char]',disable_if_low_bal=$_POST[disable_if_low_bal],auto_disconnect_after_bal=$_POST[auto_disconnect_after_bal],disable_callout_when_bal='$_POST[disable_callout_when_bal]',ussd2='$_POST[ussd2]',ussd2_ok_match='$_POST[ussd2_ok_match]',ussd22='$_POST[ussd22]',ussd22_ok_match='$_POST[ussd22_ok_match]',send_mail2='$_POST[send_mail2]',disable_if_ussd2_undone='$_POST[disable_if_ussd2_undone]',recharge_limit='$_POST[recharge_limit]',send_email='$_POST[send_email]',send_sms2='$_POST[send_sms2]',recharge_sms_num='$_POST[recharge_sms_num]',recharge_sms_msg='$_POST[recharge_sms_msg]',recharge_sms_ok_num='$_POST[recharge_sms_ok_num]',auto_ussd_step2='$_POST[auto_ussd_step2]',auto_ussd_step2_start_r='$_POST[auto_ussd_step2_start_r]',sms_report_goip='$_POST[sms_report_goip]',bal_delay='$_POST[bal_delay]',re_step2_enable='$_POST[re_step2_enable]',re_step2_cmd='$_POST[re_step2_cmd]',re_step2_ok_r='$_POST[re_step2_ok_r]',auto_reset_remain_enable='$_POST[auto_reset_remain_enable]',auto_ussd_step3='$_POST[auto_ussd_step3]',auto_ussd_step3_start_r='$_POST[auto_ussd_step3_start_r]',auto_ussd_step4='$_POST[auto_ussd_step4]',auto_ussd_step4_start_r='$_POST[auto_ussd_step4_start_r]',recharge_con_type='$_POST[recharge_con_type]',remain_limit='$_POST[remain_limit]',remain_set='$_POST[remain_set]',fixed_next_time=if(fixed_time!='$_POST[fixed_time]' and '$_POST[recharge_con_type]'='2',if(ADDTIME(TIMESTAMP(CURDATE()), '$_POST[fixed_time]')>now(), UNIX_TIMESTAMP(ADDTIME(TIMESTAMP(CURDATE()), '$_POST[fixed_time]')), UNIX_TIMESTAMP(ADDTIME(TIMESTAMP(CURDATE(), 240000), '$_POST[fixed_time]'))),fixed_next_time ),fixed_time='$_POST[fixed_time]' WHERE id='$Id'");
			//echo("fixed_next_time=if(fixed_time!='$_POST[fixed_time]' and '$_POST[recharge_con_type]'=2,if(UNIX_TIMESTAMP(ADDTIME(TIMESTAMP(CURDATE()), '$_POST[fixed_time]'))>now(), UNIX_TIMESTAMP(ADDTIME(TIMESTAMP(CURDATE()), '$_POST[fixed_time]')), UNIX_TIMESTAMP(ADDTIME(TIMESTAMP(CURDATE(), 240000), '$_POST[fixed_time]'))),fixed_next_time )");
			sendto_cron("recharge"); 
			WriteSuccessMsg("<br><li>Modify successfully</li>","?");
		}
	}
	else if($action=="start"){
		$db->query("UPDATE auto_ussd SET next_time=UNIX_TIMESTAMP(),fixed_next_time=if(recharge_con_type=2, UNIX_TIMESTAMP(), fixed_next_time) where id='$_REQUEST[id]'");
		WriteSuccessMsg("<br><li>Set the next time successfully</li>","?");
	}
	else $action="main";
	
}
else $action="main";

//if($_SESSION['goip_adminname']=="admin")	
if($action=="main")
{
	$maininfo="Current Location:Auto balance and recharge scheme";
	$query=$db->query("SELECT count(*) AS count FROM auto_ussd");
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
	$fenye=showpage("?",$page,$count,$perpage,true,true,"rows");
	$query=$db->query("SELECT auto_ussd.*,prov.prov,group_name FROM auto_ussd left join prov on auto_ussd.prov_id=prov.id left join goip_group on auto_ussd.group_id=goip_group.id ORDER BY id LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		if($row['type']==1){
			$row['type']='SMS';
			$row['auto_ussd']='';
		}
		else {
			$row['type']='USSD';
			$row['auto_sms_num']='';
			$row['auto_sms_msg']='';
		}

		if($row['recharge_con_type']==2) $row['next_time']=date("Y-m-d H:i:s T", $row['fixed_next_time']);
		else $row['next_time']=date("Y-m-d H:i:s T", $row['next_time']);
		$rsdb[]=$row;
	}
}
	require_once ('recharge.htm');

?>
