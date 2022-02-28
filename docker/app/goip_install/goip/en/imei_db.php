<?php
require_once("session.php");
define("OK", true);
require_once("global.php");
function num_rand($lenth){
        mt_srand((double)microtime() * 1000000);
        for($i=0;$i<$lenth;$i++){
                $randval.= mt_rand(0,9);
        }
        $randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
        return $randval;
}


function check_prov(&$data, $prov, $prov0, &$msg, $codeflag = 0 )
{
        if($codeflag) $data=iconv("GB2312//IGNORE","UTF-8", $data);
        $tmpdata=$data;
        if(!$data){
                //echo "sdsdsz:$prov0";
                $data=$prov0;
        }
        $data=$prov[$data];
        if(!$data){
                $msg.='<br><li>The provider not exist: '.$tmpdata.'</li>';
                return 0;
        }
        return 1;
}

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
					
					if(empty($Id))
						$ErrMsg ='<br><li>Please choose one</li>';
					if($ErrMsg!="")
						WriteErrMsg($ErrMsg);
					else{
						$query=$db->query("DELETE FROM imei_db WHERE id IN ($Id)");
						WriteSuccessMsg("<br><li>Delete success</li>","imei_db.php");
						
					}
	}
	elseif($action=="add")
	{
/*
                $query=$db->query("select id,prov from prov ");
                while($row=$db->fetch_array($query)) {
                        $prsdb[]=$row;          
                }   
*/ 
	}
	elseif($action=="modify")
	{
		$id=$_REQUEST['id'];
/*
                $query=$db->query("select id,prov from prov ");
                while($row=$db->fetch_array($query)) {
                        $prsdb[]=$row;          
                }  
*/  
		//echo "SELECT * FROM imei where id=$id";
		if($id) $rs=$db->fetch_array($db->query("SELECT * FROM imei_db where id=$id"));
		
	}
	elseif($action=="saveadd")
	{
		$imei=$_POST['imei'];
		$prov_id=$_POST['prov_id'];
		$ErrMsg="";
		if(empty($imei))
			$ErrMsg ='<br><li>Plesase input IMEI.</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{

			$db->query("INSERT INTO imei_db (imei) VALUES ('$imei')");
			WriteSuccessMsg("<br><li>Add successfully</li>","imei_db.php");				
		}
	}
	elseif($action=="savemodify")
	{
		$imei=$_POST['imei'];
		//$prov=$_POST['prov_id'];
		$used=$_POST['used'];
		//print_r($_POST);
		$Id=$_POST['Id'];
		if(empty($imei))
			$ErrMsg ='<br><li>Plesase input IMEI.</li>';
		if($ErrMsg!="")
			WriteErrMsg($ErrMsg);
		else{

			$db->query("UPDATE imei_db SET imei='$imei',used='$used' WHERE id='$Id'");
			WriteSuccessMsg("<br><li>Modify successfully</li>","imei_db.php");
		}
	}
	else if($action=="upload"){
		if ( $_POST["action"]=="mingsenupload")
		{
			$attach_name=$_FILES["img1"]['name'];
			$attach_size=$_FILES["img1"]['size'];
			$attachment=$_FILES["img1"]['tmp_name'];

			$db_uploadmaxsize='5120000';
			$db_uploadfiletype='txt cfg';
			$attachdir="upload";

			if(!$attachment || $attachment== 'none'){
				showerror('upload_content_error');
			} elseif(function_exists('is_uploaded_file') && !is_uploaded_file($attachment)){
				showerror('upload_content_error');
			} elseif(!($attachment && $attachment['error']!=4)){
				showerror('upload_content_error');
			}
			if ($attach_size>$db_uploadmaxsize){
				showerror("file too big.");
			}

			$available_type = explode(' ',trim($db_uploadfiletype));
			$attach_ext = substr(strrchr($attach_name,'.'),1);
			$attach_ext=strtolower($attach_ext);
			//      if($attach_ext == 'php' || empty($attach_ext) || !@in_array($attach_ext,$available_type)){
			//              showerror('对不起，您上传的文件类型已被系统禁止！');
			//      }
			$randvar=num_rand('15');
			$uploadname=$randvar.'.'.$attach_ext;

			if(!is_dir($attachdir)) {
				@mkdir($attachdir);
				@chmod($attachdir,0777);
			}

			$source=$attachdir.'/'.$uploadname;
			$returnfile="upload";
			$returnfile=$returnfile.'/'.$uploadname;
			if(function_exists("move_uploaded_file") && @move_uploaded_file($attachment, $source)){
				chmod($source,0777);
				$attach_saved = 1;
			}elseif(@copy($attachment, $source)){
				chmod($source,0777);
				$attach_saved = 1;
			}elseif(is_readable($attachment) && $attcontent=readover($attachment)){
				$attach_saved = 1;
				writeover($source,$attcontent);
				chmod($source,0777);
			}

			if($attach_saved == 1){
/*
				$query=$db->query("SELECT * from prov order by id");
				$pflag=0;
				while($row=$db->fetch_assoc($query)) {
					//echo $row[id]." ".$row[inter]."<br>";
					if(!$pflag && $row[prov]){
						$prov0=$row[prov];
						$pflag=1;
					}
					$prov[$row[prov]]=$row[id];
				}       
				if(!$pflag)
					die("Database did not have provider.");
*/
				$ext=substr($source, strlen($source) - 4);
				if($ext=='.csv')
				{
					//echo "2222";
					$fp   =   fopen($source,"r");
					/*解析列名*/  
					//if( fgetcsv($fp,'1024',',')){   
						//$namenum = count ($name);
					//}   
					$row=0;
					$srow=0;
					while($data   =   fgetcsv($fp,'1024',',')){   
						//if(!check_prov($data[1],$prov, $prov0, $Msg, 1)) continue;
						$row++;
						//echo "data[0] <br>";
						$db->query("insert into imei_db set imei='".$data[0]."'");
						//ussd_add($goiprow, $prov,$data[0], $data[1], $data[2]);
					}
					fclose($fp); 
					$Msg= "<br><li>Import IMEI done, total $row IMEI</li>".$Msg;
					WriteSuccessMsg($Msg,"imei_db.php");

				}
				else if($ext=='.xls') 
				{
					require_once "excel_class.php";
					Read_Excel_File($source,$return);
					$srow=0;
					//echo ("1111:".count($return[Sheet1]));
					for ($row=0;$row<count($return[Sheet1]);$row++)
					{
						//if(!check_prov($return[Sheet1][$row][1],$prov, $prov0, $Msg)) continue;
						$srow++;
						//echo "1111:".$return[Sheet1][$row][0]." <br>";
						$db->query("insert into imei_db set imei='".$return[Sheet1][$row][0]."'");
						//echo "11111:$return[Sheet1][$row][0], $return[Sheet1][$row][1], $return[Sheet1][$row][2]";
					}  
					//--$row;  
					$Msg= "<br><li>Import IMEI done, total $srow IMEI</li>".$Msg; 
					WriteSuccessMsg($Msg,"imei_db.php");
				}
			}  
		}
	}
	else $action="main";
}

else $action="main";

//if($_SESSION['goip_adminname']=="admin")	
if($action=="main")
{
	$maininfo="IMEI Data list";
	$query=$db->query("SELECT count(*) AS count FROM imei_db");
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
	$fenye=showpage("?",$page,$count,$perpage,true,true,"row");
	$query=$db->query("SELECT imei_db.*,goip.name from imei_db left join goip on goip.id=imei_db.goipid ORDER BY used,imei LIMIT $start_limit,$perpage");
	while($row=$db->fetch_array($query)) {
		$rsdb[]=$row;
	}
}
	require_once('imei_db.htm');

?>
