<?php

define("OK", true);
require_once("session.php");
//if($_SESSION['goip_permissions'] > 1)	
	//die("需要admin权限！");	
require_once("global.php");
//!defined('OK') && exit('ForbIdden');
//$UserName=$_SESSION['goip_adminname'];
/*
function num_rand($lenth){
        mt_srand((double)microtime() * 1000000);
        for($i=0;$i<$lenth;$i++){
                $randval.= mt_rand(0,9);
        }
        $randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
        return $randval;
}
*/

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
			WriteSuccessMsg("<br><li>删除自动查余额充值计划成功</li>","recharge.php");

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
                $query=$db->query("select id,name from goip order by name");
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
                $query=$db->query("select id,name from goip order by name");
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

		$ErrMsg="";
		if(empty($_POST['name']))
			$ErrMsg ='<br><li>请输入名称</li>';
		$no_t=$db->fetch_array($db->query("select id from auto_ussd where name='".$_POST['name']."'"));
		if($no_t[0])
			$ErrMsg	.='<br><li>已存在名称: '.$_POST['name'].'</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$sql="INSERT INTO auto_ussd (name,prov_id,group_id,auto_ussd,crontime,bal_sms_r,bal_ussd_r,bal_limit,recharge_ussd,send_sms,type,auto_sms_num,auto_sms_msg,recharge_type,recharge_ussd1,recharge_ussd1_goip,recharge_ok_r,recharge_ok_r2,bal_ussd_zero_match_char,bal_sms_zero_match_char,disable_if_low_bal,auto_disconnect_after_bal,disable_callout_when_bal,ussd2,ussd2_ok_match,ussd22,ussd22_ok_match,send_mail2,disable_if_ussd2_undone,recharge_limit,send_email,send_sms2,recharge_sms_num,recharge_sms_msg,recharge_sms_ok_num,auto_ussd_step2,auto_ussd_step2_start_r,sms_report_goip,bal_delay,re_step2_enable,re_step2_cmd,re_step2_ok_r,auto_reset_remain_enable,auto_ussd_step3,auto_ussd_step3_start_r,auto_ussd_step4,auto_ussd_step4_start_r)";
			$sql.=" VALUES ('$_POST[name]','$_POST[prov_id]','$_POST[group_id]','$_POST[auto_ussd]','$_POST[crontime]','$_POST[bal_sms_r]','$_POST[bal_ussd_r]','$_POST[bal_limit]','$_POST[recharge_ussd]','$_POST[send_sms]','$_POST[type]','$_POST[auto_sms_num]','$_POST[auto_sms_msg]','$_POST[recharge_type]','$_POST[recharge_ussd1]','$_POST[recharge_ussd1_goip]','$_POST[recharge_ok_r]','$_POST[recharge_ok_r2]','$_POST[bal_ussd_zero_match_char]','$_POST[bal_sms_zero_match_char]','$_POST[disable_if_low_bal]','$_POST[auto_disconnect_after_bal]','$_POST[disable_callout_when_bal]','$_POST[ussd2]','$_POST[ussd2_ok_match]','$_POST[ussd22]','$_POST[ussd22_ok_match]','$_POST[send_mail2]','$_POST[disable_if_ussd2_undone]','$_POST[recharge_limit]','$_POST[send_email]','$_POST[send_sms2]','$_POST[recharge_sms_num]','$_POST[recharge_sms_msg]','$_POST[recharge_sms_ok_num]','$_POST[auto_ussd_step2]','$_POST[auto_ussd_step2_start_r]','$_POST[sms_report_goip]','$_POST[bal_delay]','$_POST[re_step2_enable]','$_POST[re_step2_cmd]','$_POST[re_step2_ok_r]','$_POST[auto_reset_remain_enable]','$_POST[auto_ussd_step3]','$_POST[auto_ussd_step3_start_r]','$_POST[auto_ussd_step4]','$_POST[auto_ussd_step4_start_r]')";
			$query=$db->query($sql);
			sendto_cron("recharge");
			WriteSuccessMsg("<br><li>添加计划成功</li>","?");
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
		$_POST['sms_report_goip']=myaddslashes($_POST['sms_report_goip']);
		$_POST['bal_delay']=myaddslashes($_POST['bal_delay']);

                if($_POST['disable_if_low_bal']!=1) $_POST['disable_if_low_bal']=0;
		if($_POST['auto_disconnect_after_bal']!=1) $_POST['auto_disconnect_after_bal']=0;
		if($_POST['disable_if_ussd2_undone']!=1) $_POST['disable_if_ussd2_undone']=0;
		if($_POST['disable_callout_when_bal']!=1) $_POST['disable_callout_when_bal']=0;
		if($_POST['re_step2_enable']!=1) $_POST['re_step2_enable']=0;
		$_POST['re_step2_cmd']=myaddslashes($_POST['re_step2_cmd']);
		$_POST['re_step2_ok_r']=myaddslashes($_POST['re_step2_ok_r']);
		$_POST['auto_reset_remain_enable']=myaddslashes($_POST['auto_reset_remain_enable']);

		$Id=$_POST['Id'];
		$name=$_POST['name'];
		$ErrMsg="";
		$no_t=$db->fetch_array($db->query("select id from auto_ussd where name='".$name."' and id != $Id" ));
		if($no_t[0])
			$ErrMsg	.='<br><li>已存在ID: '.$name.'</li>';					
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{
			$db->query("UPDATE auto_ussd SET name='$name',prov_id='$_POST[prov_id]',group_id='$_POST[group_id]',auto_ussd='$_POST[auto_ussd]',crontime='$_POST[crontime]',bal_sms_r='$_POST[bal_sms_r]',bal_ussd_r='$_POST[bal_ussd_r]',bal_limit='$_POST[bal_limit]',recharge_ussd='$_POST[recharge_ussd]',send_sms='$_POST[send_sms]',type='$_POST[type]',auto_sms_num='$_POST[auto_sms_num]',auto_sms_msg='$_POST[auto_sms_msg]',recharge_type='$_POST[recharge_type]',recharge_ussd1='$_POST[recharge_ussd1]',recharge_ussd1_goip='$_POST[recharge_ussd1_goip]',recharge_ok_r='$_POST[recharge_ok_r]',recharge_ok_r2='$_POST[recharge_ok_r2]',bal_ussd_zero_match_char='$_POST[bal_ussd_zero_match_char]',bal_sms_zero_match_char='$_POST[bal_sms_zero_match_char]',disable_if_low_bal=$_POST[disable_if_low_bal],auto_disconnect_after_bal=$_POST[auto_disconnect_after_bal],disable_callout_when_bal='$_POST[disable_callout_when_bal]',ussd2='$_POST[ussd2]',ussd2_ok_match='$_POST[ussd2_ok_match]',ussd22='$_POST[ussd22]',ussd22_ok_match='$_POST[ussd22_ok_match]',send_mail2='$_POST[send_mail2]',disable_if_ussd2_undone='$_POST[disable_if_ussd2_undone]',recharge_limit='$_POST[recharge_limit]',send_email='$_POST[send_email]',send_sms2='$_POST[send_sms2]',recharge_sms_num='$_POST[recharge_sms_num]',recharge_sms_msg='$_POST[recharge_sms_msg]',recharge_sms_ok_num='$_POST[recharge_sms_ok_num]',auto_ussd_step2='$_POST[auto_ussd_step2]',auto_ussd_step2_start_r='$_POST[auto_ussd_step2_start_r]',sms_report_goip='$_POST[sms_report_goip]',bal_delay='$_POST[bal_delay]',re_step2_enable='$_POST[re_step2_enable]',re_step2_cmd='$_POST[re_step2_cmd]',re_step2_ok_r='$_POST[re_step2_ok_r]',auto_reset_remain_enable='$_POST[auto_reset_remain_enable]',auto_ussd_step3='$_POST[auto_ussd_step3]',auto_ussd_step3_start_r='$_POST[auto_ussd_step3_start_r]',auto_ussd_step4='$_POST[auto_ussd_step4]',auto_ussd_step4_start_r='$_POST[auto_ussd_step4_start_r]' WHERE id='$Id'");
			sendto_cron("recharge"); 
			WriteSuccessMsg("<br><li>修改计划成功</li>","?");
		}
	}
	elseif($action=="search"){
		$key=$_POST['key'];
		$type=$_POST['type'];
		switch($type){
			case 1:
				$query=$db->query("SELECT goip.*,prov.prov, prov.id as provid FROM goip,prov where goip.provider=prov.id and goip.name='$key' ORDER BY goip.id DESC");
				$typename="ID";
				break;
			default:
				$typename="无效项";
		}
		$searchcount=0;
		while($row=$db->fetch_array($query)) {
			if($row['alive'] == 1){
				$row['alive']="已注册";
			}
			elseif($row['alive'] == 0){
				$row['alive']="未注册";
				$row['sendsms']="onClick=\"alert('GoIP logout!');return false;\"";
			}
			$searchcount++;
			$rsdb[]=$row;
		}
		//$action="searchmain";
                $maininfo="搜索项：$typename, 查询关键字：$key, 结果共{$searchcount}项.";
	}
	else if($action=="start"){
		$db->query("UPDATE auto_ussd SET next_time=UNIX_TIMESTAMP() where id='$_REQUEST[id]'");
		WriteSuccessMsg("<br><li>设置时间成功</li>","?");
	}
	else $action="main";
	
}
else $action="main";

//if($_SESSION['goip_adminname']=="admin")	
if($action=="main")
{
	$maininfo="当前位置：自动查余额充值计划";
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
	$fenye=showpage("?",$page,$count,$perpage,true,true,"编");
	$query=$db->query("SELECT auto_ussd.*,prov.prov FROM auto_ussd left join prov on auto_ussd.prov_id=prov.id ORDER BY id LIMIT $start_limit,$perpage");
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

		$row['next_time']=date("Y-m-d H:i:s T", $row['next_time']);
		$rsdb[]=$row;
	}
}
	require_once ('recharge.htm');

?>
