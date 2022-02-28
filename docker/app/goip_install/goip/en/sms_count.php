<?php

define("OK", true);
require_once("session.php");
require_once("global.php");

if($_SESSION['goip_permissions'] <=1 || operator_owner_forbid());
else die("Permission denied!");	
require_once("global.php");

function second_to_time($t)
{
        $h=floor($t/3600);
        $m=floor(($t-3600*$h)/60);
        $s=$t-3600*$h-60*$m;
	if($h) $n.="{$h}h ";
	if($m) $n.="{$m}m ";
	$n.="{$s}s";
        return $n;
}

$start_time=$_REQUEST['start_time'];
if(!$start_time) $start_time=date("Y-m-d")." 00:00";
$end_time=$_REQUEST['end_time'];
if(!$end_time) $end_time=date("Y-m-d H:i");
$goip_id=$_REQUEST['goipid'];
$prov_id=$_REQUEST['prov_id'];
$group_id=$_REQUEST['group_id'];
//$where="where 1 ";
if($goip_id) $where.=" and goipid='$goip_id'";
if($prov_id) $where.=" and goip.provider='$prov_id'";
if($group_id) $where.=" and group_id='$group_id'";

$sql="SELECT goipid,sum(sends.total) as ok_count from sends left join goip on sends.goipid=goip.id where over=1 and time>'$start_time' and time<'$end_time' $where group by goipid";
/*
//echo $sql." $goip_id";
$query=$db->query($sql);
while($row=$db->fetch_array($query)){
	$calltime+=$row[1];
	$callcount+=$row[2];
	$row['acd']=round($row[1]/$row[2]);
	$row['acd_s']=second_to_time($row['acd']);;	
	$row['calltime_s']=second_to_time($row[1]);
	$rsdb[$row[goipid]]=$row;
}
$calltime_s=second_to_time($calltime);
$acd=round($calltime/$callcount);
$acd_s=second_to_time($acd);

$sql="SELECT goipid,count(record.id) from record left join goip on goip.id=record.goipid where dir=2 and expiry>=0 and time>'$start_time' and time<'$end_time' $where group by goipid";
*/
$query=$db->query($sql);
while($row=$db->fetch_array($query)){
	//$rsdb[$row[goipid]]['asr']=(round($rsdb[$row[goipid]][callcount]/$row[1],3)*100)."%";
        $rsdb[$row[goipid]][sms_count]=$row[1];
        $sms_count+=$row[1];
}
//$asr=(round($callcount/$tcount,3)*100)."%";
//echo "asr:".round($rs[1]/$rs1[0],3);
$wh="where 1 ";
if(!$goip_id) {
	$ch="selected";
	$goip_name="All";
}
if($prov_id) {
	$wh.=" and provider='$prov_id'";
}
if($group_id) {
	$wh.=" and group_id='$group_id'";
}
$select="<select name=\"goipid\"  style=\"width:80px\" >\n\t<option value=\"0\" $ch>All</option>\n";
$query=$db->query("select id,name from goip $wh order by name");
while($row=$db->fetch_array($query)) {
	if($goip_id==$row[id]) {
		$row['ch'] = "selected";
		$goip_name = $row['name'];
		$goip[]=$row;
	}
	else if(!$goip_id){
		$goip[]=$row;
	}
	$select.="\t<option value=\"$row[id]\" $row[ch]>$row[name]</option>\n";
}
$select.="</select>";

if(!$prov_id) {
	$ch="selected";
	$prov_name="All";
}
$prov_select="<select name=\"prov_id\"  style=\"width:80px\" >\n\t<option value=\"0\" $ch>All</option>\n";
$query=$db->query("select id,prov from prov order by id");
while($row=$db->fetch_array($query)) {
	if($prov_id==$row[id]) {
		$row['ch'] = "selected";
		$prov_name = $row['prov'];
		$prov[]=$row;
	}
	else if(!$prov_id){
		$prov[]=$row;
	}
	$prov_select.="\t<option value=\"$row[id]\" $row[ch]>$row[prov]</option>\n";
}
$prov_select.="</select>";

$ch="";
if(!$group_id) {
	$ch="selected";
	$group_name="All";
}
$group_select="<select name=\"group_id\"  style=\"width:80px\" >\n\t<option value=\"0\" $ch>All</option>\n";
$query=$db->query("select * from goip_group");
while($row=$db->fetch_array($query)) {
	if($group_id==$row[id]) {
		$row['ch'] = "selected";
		$group_name = $row['group_name'];
		$group[]=$row;
	}
	else if(!$goip_id){
		$group[]=$row;
	}
	$group_select.="\t<option value=\"$row[id]\" $row[ch]>$row[group_name]</option>\n";
}
$group_select.="</select>";


require_once ('sms_count.htm');

?>
