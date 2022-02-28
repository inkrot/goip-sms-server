<?php 
require_once("session.php");
define("OK", true);
require_once("global.php");
$yrecv=$yrecv1=$yrecv2='checked';
$formaction="dosend.php";
$yrecvid=$yrecvid1=$yrecvid2=array();
$ygroupid0=array();
if($_GET['action']=="modify"){
	
	$row0=$db->fetch_array($db->query("select * from message where crontime and id=$_GET[id]"));
	if($_SESSION['goip_permissions'] > 1 && owner_forbid() && $row0['userid']!=$_SESSION[goip_userid])
		WriteErrMsg("Permission denied");
	elseif($row0[over]>0)
		WriteErrMsg("The task has been started or done");
	$_GET['action']="send";
	$messageid=$_GET['id'];
	$formaction="dosend.php?id=".$messageid;
	//2009-06-30 16:28
	$crontime=date("Y-m-d H:i", $row0['crontime']);
	$ymsg=$row0['msg'];
	if($row0['type'] == 0){
		
		$_GET['type']="group";
		$_GET['id']=$row0['groupid'];
		$yrecvid=explode(',', $row0['receiverid']);
		$yrecvid1=explode(',', $row0['receiverid1']);
		$yrecvid2=explode(',', $row0['receiverid2']);
	}
	elseif($row0['type'] == 1){
		$_GET['type']="crowd";
		$ytype="modify";
		$ygroupid0=explode(',', $row0['groupid']);
		$ygroupid=$row0['groupid']?'checked':"";
		$ygroupid1=$row0['groupid1']?'checked':"";
		$ygroupid2=$row0['groupid2']?'checked':"";
	}
	elseif($row0['type'] == 2){
		$_GET['type']="all";
		$yrecv=$row0['recv']?'checked':"";
		$yrecv1=$row0['recv1']?'checked':"";
		$yrecv2=$row0['recv2']?'checked':"";
	}
        elseif($row0['type'] == 4){
                $_GET['type']="re";
                $_GET['goipid']=$row0['goipid'];
                $rs['srcnum']=$row0['tel'];
                $rs['provid']=$row0['prov'];                                                                      

        }
	$buttoninfo = <<<EOT
<td align="center"><input type="submit" name="submit2" value="Modify Task" class="submit" onClick="return check2();"><br> <input type="text" name="datehm"  readOnly onClick="SelectDate(this,'yyyy-MM-dd hh:mm')" value="{$crontime}"><br>set time</td>
EOT;
	
}
else {
	$buttoninfo = <<<EOT
<td align="center"  valign="top" style="padding-top:65px;"><input type="submit" name="submit1" value="Send" class="submit"> <br><br><br> <input type="submit" name="submit2" value="Save Task" class="submit" onClick="return check2();"><br> <input type="text" name="datehm"  readOnly onClick="SelectDate(this,'yyyy-MM-dd hh:mm')" value="{$crontime}"><br>set time</td>
EOT;
}
//echo $_GET['type'].$_GET['action'];

if($_GET['type']=="crowd") {
	//WriteErrMsg("");
	if($_SESSION['goip_permissions'] == 2 || $_SESSION['goip_permissions'] == 3)
		WriteErrMsg("<br><li>Permission denied!</li>");
	if($_GET['action']=="send"){
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
		if(empty($Id))
			WriteErrMsg('<br><li>Please choose one</li>');
		

		if($ytype=="modify" && $_SESSION['goip_permissions'] < 2)
			$query=$db->query("SELECT * FROM groups  ORDER BY id  ");	
		elseif($ytype=="modify" && $_SESSION['goip_permissions'] == 2)
			$query=$db->query("SELECT groups.* FROM groups,refcrowd where refcrowd.crowdid=groups.crowdid and refcrowd.userid=$_SESSION[goip_userid] ORDER BY id  ");
		elseif($ytype=="modify" && $_SESSION['goip_permissions'] > 2)
			WriteErrMsg('<br><li>Permission denied</li>');
		else {
			$query=$db->query("SELECT * FROM groups where crowdid in ($Id) ORDER BY id  ");	
			$ygroupid='checked';
		}
		$sdb=$db->fetch_array($db->query("select maxword from system where 1"));
		$maxword=$sdb[0];
		$msgrs=$db->fetch_array($db->query("SELECT * FROM user where username='".$_SESSION['goip_username']."'"));
		$groupc=0;
		while($row=$db->fetch_array($query)) {
			$rsdb[$row[crowdid]][]=$row;
			$groupc++;
		}
		$crowdc=count($rsdb);
		if($ytype=="modify" && $_SESSION['goip_permissions'] < 2)
			$query=$db->query("select id,name from crowd ORDER BY id");	
		elseif($ytype=="modify" && $_SESSION['goip_permissions'] == 2)
			$query=$db->query("select crowd.* from crowd,refcrowd where (refcrowd.crowdid=crowd.id and refcrowd.userid=$_SESSION[goip_userid] ) ORDER BY crowd.id");
		elseif($ytype=="modify" && $_SESSION['goip_permissions'] > 2)
			WriteErrMsg('<br><li>Permission denied</li>');
		else 
			$query=$db->query("select id,name from crowd where id in ($Id)");
		while($row=$db->fetch_array($query)) {
			$crowdna[$row[0]]=$row[1];
			$groups[$row[0]]=count($rsdb[$row[0]]);
		}	
		//print_r($rsdb);
	}
	else {
		if($_SESSION['goip_permissions'] < 2){
			$query=$db->query("select * from crowd order by id");
		}
		elseif($_SESSION['goip_permissions'] == 2){ 
			$query=$db->query("select crowd.* from crowd,refcrowd where (refcrowd.crowdid=crowd.id and refcrowd.userid=$_SESSION[goip_userid] ) ORDER BY crowd.id");
		}

		while($row=$db->fetch_array($query)) {
			$rsdb[]=$row;
		}


	}
}
elseif($_GET['type']=='groups'){
		//echo "ddddddddddddddddddd";
		if(empty($_GET[id]))
			WriteErrMsg("please choose a crowd!");
		$sdb=$db->fetch_array($db->query("select maxword from system where 1"));
		$maxword=$sdb[0];
		$msgrs=$db->fetch_array($db->query("SELECT * FROM user where username='".$_SESSION['goip_username']."'"));

		$gdb=$db->fetch_array($db->query("select name from crowd where id=$_GET[id]"));		
		$query=$db->query("SELECT * FROM groups where crowdid=$_GET[id] ORDER BY id DESC ");	
		while($row=$db->fetch_array($query)) {
			$gquery=$db->query("select * from groups where crowdid=$row[id] order by id desc");
			while($grow=$db->fetch_array($gquery)) {
				$row['groups'][]=$grow;
			}
			$rsdb[]=$row;
		}
}
elseif($_GET['type']=='group') {
	if($_GET[action]=="send"){
		if(empty($_GET[id]))
			WriteErrMsg("please choose a group!");
		$sdb=$db->fetch_array($db->query("select maxword from system where 1"));
		$maxword=$sdb[0];
		$msgrs=$db->fetch_array($db->query("SELECT * FROM user where username='".$_SESSION['goip_username']."'"));
		$gdb=$db->fetch_array($db->query("select name from groups where id=$_GET[id]"));		
		$query=$db->query("SELECT receiver.* FROM receiver,recvgroup where recvgroup.recvid=receiver.id and recvgroup.groupsid=$_GET[id] ORDER BY id ");	

		$count=0;
		while($row=$db->fetch_array($query)) {
			$rsdb[]=$row;
			$count++;
		}
		//print_r($rsdb);
	}
	else {
		if($_SESSION['goip_permissions'] < 2){
			$query=$db->query("SELECT groups.*,crowd.name as crowdname FROM `groups`,crowd where crowd.id=groups.crowdid ORDER BY groups.crowdid,groups.id");
		}
		elseif($_SESSION['goip_permissions'] == 2){ 
			$query=$db->query("SELECT groups.*,crowd.name as crowdname FROM `groups`,crowd,refcrowd where (crowd.id=groups.crowdid and refcrowd.crowdid=crowd.id and refcrowd.userid=$_SESSION[goip_userid] ) ORDER BY groups.crowdid,groups.id");
		}			
		elseif($_SESSION['goip_permissions'] == 3){ 
			$query=$db->query("SELECT groups.*,crowd.name as crowdname FROM `groups`,crowd,refgroup where crowd.id=groups.crowdid and refgroup.groupsid=groups.id and refgroup.userid=$_SESSION[goip_userid]  ORDER BY groups.crowdid,groups.id");
		}
		
		while($row=$db->fetch_array($query)) {
			$rsdb[]=$row;
			
		}	
	}	
}
elseif($_GET['type']=='all'){
	if($_SESSION['goip_permissions'] > 1)
		WriteErrMsg("<br><li>Permission denied!</li>");
	$sdb=$db->fetch_array($db->query("select maxword from system where 1"));
	$maxword=$sdb[0];
	$msgrs=$db->fetch_array($db->query("SELECT * FROM user where username='".$_SESSION['goip_username']."'"));
	$maxword=$sdb[0];	
	$row=$db->fetch_array($db->query("SELECT count(*) AS count FROM receiver "));
	$recvnum=$row[0];		
}
elseif($_GET['type']=='re'){ //发送一个号码
	$sdb=$db->fetch_array($db->query("select maxword from system where 1"));
	$maxword=$sdb[0];
	$msgrs=$db->fetch_array($db->query("SELECT * FROM user where username='".$_SESSION['goip_username']."'"));
	if($_GET['reid']){
/*
                if(!$_GET['namelevel']) $_GET['namelevel']=0;
                switch($_GET['namelevel']){
                        case 0:
                                $sql="select name from receiver where id=$_GET['renameid']  and tel='num'";
                                break;
                        case 1:
                                $sql="select name1 from receiver where id=$_GET['renameid']  and tel1='num'";
                                break;
                        case 2:
                                $sql="select name2 from receiver where id=$_GET['renameid']  and tel2='num'";
                                break;
                }
                $rs=$db->fetch_array($db->query($sql));
*/
		$rs=$db->fetch_array($db->query("select * from receive where id=$_GET[reid]"));


        }
        else if($_GET[goipid]){
                $rs[goipid]=$_GET[goipid];
        }
                if($_GET[goipid]  && $myaction!="modify")
                        $query=$db->query("SELECT *,goip.id as goipid, prov.id as provid FROM `goip`, prov WHERE goip.provider=prov.id and alive=1 and goip.id=$_GET[goipid] order by prov.id DESC, goip.id DESC");  
                else 
                	$query=$db->query("SELECT *,goip.id as goipid, prov.id as provid FROM `goip`, prov WHERE goip.provider=prov.id and alive=1 order by prov.id DESC, goip.id DESC");    
                $js.="<script  language=javascript>\n";
                $js.="var select2 = new Array();\n";
                $jsprovi=-1;
                //$js.="select2[0] = new Array();\nselect2[0][0] = new Option(\"请选择\", \" \");\n";
		$prsdb=array();
                while($row=$db->fetch_array($query)) {                                                            
                        $prsdb[]=$row; 
                        if($row[prov]) {
                                if($row[goipid]==$rs[goipid]){
                                        $rs[provid]=$row[provid];
                                        $ck='selected';
                                        $itre=$row;
                                }
                                else 
                                        $ck='';
                                if($row[provid] != $provnow){  //新服务商
                                        $jsprovi++;
                                        $j=1;
                                        $js.="select2[$jsprovi] = new Array();\n";
                                        $js.="select2[$jsprovi][0] = new Option(\"DEFAULT\",\"0\");\n";
                                        $provnow=$row[provid];
                                }
                                $js.="select2[$jsprovi][$j] = new Option(\"$row[name]\",\"$row[goipid]\");\n";
                                $j++;

                        }
                }
                $js.=<<<EOT
function redirec(x)                                                                                               
{                                                                                                                 
 var temp = document.myform.smsgoip;
 for (i=0;i<select2[x].length;i++)                                                                                
 {
  temp.options[i]=new Option(select2[x][i].text,select2[x][i].value);                                             
 }                                                                                                                
 temp.options[0].selected=true;                                                                                   
                                                                                                                  
}                                                                                                                 
</script> 
<tr id="row_customfield_operator">
  <td class="field-label"><label>Provider</label></td>
  <td>                                                                                                            
  <select style="width:200px" id="smsprovider" name="smsprovider" onChange="redirec(document.myform.smsprovider.options.selectedIndex)">  
EOT;

$prnow=0;
foreach($prsdb as $row){
        //if($rs[provid]){
        if($row[provid] != $prnow){

                                if($row[provid]==$rs[provid]){
                                        $ck='selected';
                                        //echo "select! $rs[goipid]";
                                }
                                else
                                        $ck='';


                $js.="\n<option value='$row[provid]' $ck>$row[prov]</option>" ;
                $prnow = $row[provid];
        }
        //}
        //else 
                //$js.="\n<option value='$row[provid]' $ck>$row[prov]</option>" ;
}
if(!$_GET['goipid']) $op="<option value='0'>DEFAULT</option>";
$js.=<<<EOT

  </select>   
  </td>                                                                                                    
</tr>
<tr id="row_goip">                                                                                                
  <td class="field-label"><label>GoIP</label></td>                                                                
  <td>                                                                                                            
  <select style="width:200px" id="smsgoip" name="smsgoip">                                                        
	$op
EOT;
foreach($prsdb as $row){
        if($rs[provid]){
        if($row[provid]==$rs[provid]){

                                if($row[goipid]==$rs[goipid]){
                                        $ck='selected';
                                }                                                                                 
                                else                                                                              
                                        $ck='';  
                $js.="\n<option value='$row[goipid]' $ck>$row[name]</option>" ;
        }
        }
        else {
                $rs[provid] = $row[provid];
                $js.="\n<option value='$row[goipid]' $ck>$row[name]</option>" ;
        }
}

$js.=<<<EOT

</select>
</td>
</tr>
EOT;
}
elseif($_GET['type']=='sn'){
        if($_SESSION['goip_permissions'] > 1)
                WriteErrMsg("<br><li>Permission denied</li>");
}
	require_once("send.htm");
?>
