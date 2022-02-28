<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="style.css" rel="stylesheet" type="text/css">
<title>发送信息</title>
</head>
<body>
<?php
require_once("session.php");
define("OK", true);
ignore_user_abort(true);
set_time_limit(0);
ini_set("memory_limit", "200M");
echo str_pad(" ", 256);
require_once("global.php");
if($goipcronport)
	$port=$goipcronport;
else 
	$port=44444;

$query=$db->query("SELECT * from prov ");
while($row=$db->fetch_assoc($query)) {
	//echo $row[id]." ".$row[inter]."<br>";
	$row[interlen]=strlen($row[inter]);
	$row[locallen]=strlen($row[local]);
	$prov[$row[id]]=$row;
}

	function dolastsend(&$goipsend,$len,$msg)
	{
		//print_r($goipsend);
		global $port;
		$sendid=$goipsend[messageid];
		if($goipsend[send]=="RMSG"){
			if($goipsend[timer] <=1 ){
				$goipsend[send]="MSG";
				$goipsend[timer]=3;
			}
			else return;
		}
		if($goipsend[send]=="HELLO"){
			$buf="HELLO ".$sendid."\n";
						
			//$goipsend[timer]=0;
			
		}
		elseif($goipsend[send]=="PASSWORD"){
			$buf="PASSWORD $sendid $goipsend[password]\n";
				
		}
		elseif($goipsend[send]=="SEND"){
			$buf="SEND $sendid $goipsend[telid] ".$goipsend[tel][telnum]."\n";
			echo "<br> $buf <br>";
		}	
		elseif($goipsend[send]=="MSG"){
			$buf="MSG $sendid $len $msg\n";

		}	
		//echo "<br> buf:$buf <br>";
		if (@socket_sendto($goipsend[sock],$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
			echo ("sendto error");			
	}
        function checkover($goipdb)
        {
                //echo "checkover <br>";
                foreach($goipdb as $the0 => $goipsend){
                        if($goipsend[timer]>0){//重试
                                if($goipdb[$the0][send]!="RMSG"){  //如果其他已结束，将不等待连不上的GOIP
                                        return false;//未完成
                                }
                        }
                }
                return true;
        }


function startdo($db, $tels, $sendid, $msg, $len){	
		global $port;
		global $prov;
		$nowtime=date ("Y-m-d H:i:s");
		/*写入数据库*/
		//$sendid=$sendsiddb[0];
		
		//$id=0;
		$query=$db->query("SELECT prov.*,goip.* FROM goip,prov where prov.id=goip.provider and alive=1 ORDER BY name");
		$socks=array();
		while($goiprow=$db->fetch_array($query)) {
			$goipname[]=$goiprow[provider];


			/*把信息传过去*/			
			if(count($tels[$goiprow[provider]])){ //有要发给这个服务商的号码才通信
				//echo "sendid $sendid <br>";
				$errortels[$goiprow[provider]]=array();
				if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) <= 0) {
					echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
					exit;
				}
				$goiprow['timer']=3;
				$goiprow['send']="MSG";
				$goiprow['sock']=$socket;
				$goiprow['time']=time();//计时
				$goiprow['messageid']=$sendid+($goiprow[id] << 16)+$goiprow['time']%10000;
				$goipdb[]=$goiprow;
				$buf="START ".$goiprow['messageid']." $goiprow[host] $goiprow[port]\n";
				if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
					echo ("sendto error");
				for($i=0;$i<3;$i++){
					$read=array($socket);
					$err=socket_select($read, $write = NULL, $except = NULL, 5);
					if($err>0){		
						if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
							//echo("recvform error".socket_strerror($ret)."<br>");
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
				if($i>=3)
					die("goipcron 服务进程没有响应");				
				$buf="MSG ".$goiprow['messageid']." $len $msg\n";
				if (@socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
					echo ("sendto error");
				$socks[]=$socket;
			}
			//print_r($goiprow);
		}
		foreach($tels as $provtmp => $valuetmp){
			foreach($goipdb as $goiptmp){
				if($goiptmp['provider']==$provtmp)
					continue 2;
			}
			$n=count($valuetmp);
			echo "<font color='#FF0000'>要发送{$n}个".$prov[$provtmp]['prov']."服务商的号码，但找不到可用的".$prov[$provtmp]['prov']."GOIP</font><br>";
		}		
		//$read = array($socket);
		$timeout=5;
		for(;;){
			$read=$socks;
			flush();
			if(count($read)==0)
				break;
			$err=socket_select($read, $write = NULL, $except = NULL, $timeout);
			if($err===false)
				echo "select error!";
			elseif($err==0){ //全体超时
				$i=0;
				$flag=1;
				$nowtime=time();
				//reset($goipdb);
				//while (list (, $goipsend) = each ($goipdb)) {
				foreach($goipdb as $the0 => $goipsend){
					//$goipsend=$goipdb[$the0];
					$goipdb[$the0]['time']=$nowtime;
					if($goipsend[timer]>0){//重试
						if($goipdb[$the0][send]!="RMSG")  //如果其他已结束，将不等待连不上的GOIP
							$flag=0;//未完成
						dolastsend($goipsend,$len,$msg);
						$goipdb[$the0]['timer']--;
						//echo("<br>$i timer:".$goipsend[timer]."<br>");
						$i++;
					}
					else{ //累计失败
						if($goipsend[send]=="OK") //已完成的
							continue;
						if($goipsend[send]=="SEND"){
							echo "<font color='#FF0000'>$goipnow[telid] $goipnow[tel] faile</font><br>";
							foreach($goipdb as $the => $goiptmp){ 
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider]){
									if($goipsend[tel][error] && in_array($goiptmp[id],$goipsend[tel][error]))
										continue;//已发送错误
									$goipdb[$the][send]="SEND";
									$goipdb[$the][tel]=$goipsend[tel];
									$goipdb[$the][telid]=$goipsend[telid];
									$goipsend[send]=="OK"; //结束
									dolastsend($goiptmp,$len,$msg);
									$goipdb[$the0][send]="RMSG";//超时的goip，100s后通讯
									$goipdb[$the0][timer]=20;
									$goipdb[$the0][tel]=0;
									$goipdb[$the0][telid]=0;
									break;
								}									
							}
						}
						else{
							/*100s 后重新通讯*/
							echo "<font color='#FF0000'>无响应: $goipsend[send] ($goipsend[name] $goipsend[prov])</font><br>";
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=20; 
						}
						if($goipsend[send]=="SEND"){//没有找到空闲的goip，把号码压回，100s后重新通讯
							if($goipsend[tel][error])
								array_push($errortels[$goipsend[provider]], $goipsend[tel]);//压回出错数组
							else 
								array_push($tels[$goipsend[provider]], $goipsend[tel]); //压回
							//array_push($tels[$goipsend[provider]], $goipsend[tel]); //压回
							/*删除数据库*/
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=20;
							$goipdb[$the0][tel]=0;
							$goipdb[$the0][telid]=0;						
						}	
					}	
				}
				if($flag)
					break; //全部结束
			}//全体超时
			else{ //可读
			
			  foreach($read as $socket){
				unset($buf);
				//$buf="";
				
				if(($n=@socket_recvfrom($socket,$buf,1024,0,$ip,$port1))==false){
					//echo("recvform error".socket_strerror($ret)."<br>");
					continue;
				}
				//echo("num:$n $buf<br>");
				
				//$bufline=explode("\n",$buf);
				//foreach($bufline as $line){
					//$comm=explode(" ",$line);
				  $comm=explode(" ",$buf);
					foreach($goipdb as $the => $goipnow){
						//echo "$key => $val\n";
						if($goipnow[sock]==$socket){
							break;
						}	
					}
					
					if(empty($goipnow)){ //不是期望的套接口
						continue; 
					}
					if(strncmp($goipnow[messageid],$comm[1], strlen($goipnow[messageid])))//不是期望的id
						continue;
					if($comm[0]=="OK"){
						//更新数据库，发送成功 
						//echo "inser: ".$goipnow[tel][id]."  $goipnow[tel] ok<br>";
						$db->query("update sends set `over`=1,goipid=$goipnow[id] where id=".$goipnow[tel][id]." and messageid=$sendid");
						/**/
						if($goipnow[send]!="SEND"){//不处于发送状态，无视
							echo "not send status <br>";
							continue;
						}	
						if($comm[2]==$goipnow[tel][id] ){ //是现在发的号码,可以发下一个了
							echo "<font color='#00FF00'>SEND: $goipnow[telid] ".$goipnow[tel][telnum]." ok($goipnow[name] $goipnow[prov])</font><br
>";
							$goipdb[$the]['send']="OK";//结束了					
							$goipdb[$the][telid]=0;
							$goipdb[$the][tel]=0;
							$goipdb[$the][timer]=0;
							
							if(($goipdb[$the][tel]=array_pop($tels[$goipnow[provider]]))!==NULL){//出来	
								/*写入数据库，得到id, 发送*/
								$goipdb[$the][telid]=$goipdb[$the][tel][id];
								//$goipdb[$the][telid]=$testid++;
								$goipdb[$the]['send']="SEND";
								
								$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
								echo "SEND $goipnow[name] ".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n<br>";
								if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
									echo ("sendto error");
								$goipdb[$the][timer]=3;
							}
							elseif($errortels[$goipnow[provider]]){
								foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){
									if(!in_array($goipnow[id],$nowrow['error'])){
										$goipdb[$the][tel]=$nowrow;
										unset($errortels[$goipnow[provider]][$telthe]);
										$goipdb[$the][tel]=$goipdb[$the][tel];						
			
										$goipdb[$the][telid]=$goipdb[$the][tel][id];
										//$goipdb[$the][telid]=$testid++;
										//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
										$goipdb[$the]['send']="SEND";
										$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
										echo "SEND $goipnow[name]".$goipdb[$the][messageid]." ".$goipdb[$the][telid]." ".$goipdb[$the][tel]."\n<br>";
										if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
											echo ("sendto error");
										$goipdb[$the][timer]=3;									
										break;
									}
								}							
							}
							if(checkover($goipdb)) break 2;
						}

					}
					elseif($comm[0]=="WAIT"){
							echo "WAIT $goipnow[send] $comm[2] $goipnow[telid] <br>";         
							if($goipnow[send]=="SEND" && $comm[2]==$goipnow[telid]){          
									$goipdb[$the][timer]=3;//持续发送                         
							}
					} 
					elseif($comm[0]=="MSG"){ //不应该收到
					/*
						if($goipnow[send]="SEND")
							array_push($tels[$goipnow[provider]], $goipnow[tel]); //压回
						if($goipnow[send]!="MSG"){
							$goipnow[timer]=3;
							$goipnow[send]="MSG";
						}
						//if($comm[0]!="MSG")
							//$goipnow[timer]=0;
					*/	
					}
					elseif($comm[0]=="SEND"){
						//$goipnow['ok']=1;
						//$goipnow[send]="SEND";	
						if($goipnow[send]=="SEND")//已经处于发送状态
							continue;		
						$goipdb[$the]['send']="OK";//结束了
						$goipdb[$the][telid]=0;
						$goipdb[$the][tel]=0;
						$goipdb[$the][timer]=0;	
						if(($goipdb[$the][tel]=array_pop($tels[$goipnow[provider]]))!==NULL){//出来	
							/*写入数据库，得到id, 发送*/

							$goipdb[$the][telid]=$goipdb[$the][tel][id];
							//$goipdb[$the][telid]=$testid++;
							//echo "inser: ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]." start<br>";
							$goipdb[$the]['send']="SEND";
							$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
							echo $buf."<br>";
							if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
								echo ("sendto error");
							$goipdb[$the][timer]=3;
						}
						elseif($errortels[$goipnow[provider]]){
							foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){
								if(!in_array($goipnow[id],$nowrow[error])){
									$goipdb[$the][tel]=$nowrow;
									unset($errortels[$goipnow[provider]][$telthe]);
									//$goipdb[$the][tel]=$goipdb[$the][tel][tel];
									$goipdb[$the][telid]=$goipdb[$the][tel][id];
									//$goipdb[$the][telid]=$testid++;
									//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
									$goipdb[$the]['send']="SEND";
									$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
									echo $buf."<br>";
									if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
										echo ("sendto error");
									$goipdb[$the][timer]=3;									
									break;
								}
							}							
						}
						if(checkover($goipdb)) break 2;
					}
					elseif($comm[0]=="PASSWORD"){
						//$teli=substr($comm[1], -1);
						//echo ("PASSWORD s:$goipnow[send] c:$comm[1] p:$goipnow[password]\n");
						if($goipnow['send']!="PASSWORD" && $goipnow['send']!="MSG")//不是发送密码状态就不处理
							continue;
						
						socket_sendto($socket,"PASSWORD $comm[1] $goipnow[password]\n", strlen("PASSWORD $comm[1] $goipnow[password]\n"), 0, "127.0.0.1", $port);
						$goipdb[$the][send]="PASSWORD";
						$goipdb[$the][timer]=3;					
					}
					elseif($comm[0]=="ERROR"){
						echo "<font color='#FF0000'>$buf ($goipnow[name] $goipnow[prov] ".$goipnow[tel][telnum].")</font><br>";

						if($goipdb[$the][send]=="SEND" && $comm[2]==$goipnow[telid]){//发送失败
							$goipdb[$the]['tel']['error'][]=$goipdb[$the]['id'];
							foreach($goipdb as $the1 => $goiptmp){
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider] && !in_array($goiptmp[id],$goipdb[$the]['tel']['error']) ){
									$goipdb[$the1][send]="SEND";
									$goipdb[$the1][tel]=$goipdb[$the][tel];
									$goipdb[$the1][telid]=$goipnow[telid];
									//$goipdb[$the1][tel]['error']=$goipnow[tel]['error'];
									$goipdb[$the1][timer]=3;
									$findokflag=1;
									$db->query("update sends set goipid=$goiptmp[id] where id=$goipnow[telid]");
									dolastsend($goipdb[$the1],$len,$msg);
									break;
								}
							}                                                      
							if(!$findokflag){
								array_push($errortels[$goipsend[provider]], $goipdb[$the][tel]);
								//$db->query("delete from sends where id=$goipnow[telid]");
								//array_push($tels[$goipsend[provider]], $goipsend[tel]); //压回
							}
							$goipdb[$the]['send']="OK";//结束了
							$goipdb[$the][telid]=0;
							$goipdb[$the][tel]=0;
							$goipdb[$the][timer]=0;																																								 							if(($goipdb[$the][tel]=array_pop($tels[$goipnow[provider]]))!==NULL){//出来	
								$goipdb[$the][telid]=$goipdb[$the][tel][id];
								//$goipdb[$the][telid]=$testid++;
								echo "inser: ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]." start<br>";
								$goipdb[$the]['send']="SEND";
								$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
								echo $buf."<br>";
								if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
									echo ("sendto error");
								$goipdb[$the][timer]=3;
							}
							elseif($errortels[$goipnow[provider]]){
								foreach($errortels[$goipnow[provider]] as $telthe => $nowrow){

									if(!in_array($goipnow[id],$nowrow[error])){
										$goipdb[$the][tel]=$nowrow;
										unset($errortels[$goipnow[provider]][$telthe]);
										//$goipdb[$the][tel]=$goipdb[$the][tel][tel];
										$goipdb[$the][telid]=$goipdb[$the][tel][id];
										//$goipdb[$the][telid]=$testid++;
										//echo "inser: ".$goipdb[$the][telid]." ".$goipdb[$the][tel]." start<br>";
										$goipdb[$the]['send']="SEND";
										$buf="SEND ".$goipdb[$the][messageid]." ".$goipdb[$the][tel][id]." ".$goipdb[$the][tel][telnum]."\n";
										echo $buf."<br>";
										if (socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port)===false)
											echo ("sendto error");
										$goipdb[$the][timer]=3;									
										break;
									}

								}							
							}
							if(checkover($goipdb)) break 2;
						}
						elseif($goipnow[timer]>0){
							dolastsend($goipdb[$the],$len,$msg);
							$goipdb[$the][timer]--;
						}
						else //等待它超时吧
							continue;
					}//elseif($comm[0]=="ERROR"){
					$goipdb[$the]['time']=time();
				//}//foreach($bufline as $line){
			  }//foreach($read
				$i=0;
				
				$nowtime=time();
				foreach($goipdb as $the0 => $goipsend){
					//$flag=0;
					if($goipsend['time'] <$nowtime-$timeout && $goipsend[send]!="OK"){//超时了
						if($goipsend[timer]>0){//重试
							//$flag=1;//未完成
							dolastsend($goipdb[$the0],$len,$msg);
							$goipdb[$the0][timer]--;
							echo("<br>$i timer:".$goipsend[timer]."<br>");
							$i++;
						}
						else{ //累计失败
						if($goipsend[send]=="SEND"){
							echo "<font color='#FF0000'>$goipnow[telid] $goipnow[tel] faile</font><br>";
							foreach($goipdb as $the => $goiptmp){ 
								if($goiptmp[send]=="OK" && $goiptmp[provider]==$goipsend[provider]){
									if($goipsend[tel][error] && in_array($goiptmp[id],$goipsend[tel][error]))
										continue;//已发送错误
									$goipdb[$the][send]="SEND";
									$goipdb[$the][tel]=$goipsend[tel];
									$goipdb[$the][telid]=$goipsend[telid];
									$goipsend[send]=="OK"; //结束
									dolastsend($goiptmp,$len,$msg);
									$goipdb[$the0][send]="RMSG";//超时的goip，100s后通讯
									$goipdb[$the0][timer]=20;
									$goipdb[$the0][tel]=0;
									$goipdb[$the0][telid]=0;
									break;
								}									
							}
						}
						else{
							/*100s 后重新通讯*/
							echo "<font color='#FF0000'>无响应: $goipsend[send] ($goipsend[name] $goipsend[prov])</font><br>";
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=20; 
						}
						if($goipsend[send]=="SEND"){//没有找到空闲的goip，把号码压回，100s后重新通讯
							if($goipsend[tel][error])
								array_push($errortels[$goipsend[provider]], $goipsend[tel]);//压回出错数组
							else 
								array_push($tels[$goipsend[provider]], $goipsend[tel]); //压回
							//array_push($tels[$goipsend[provider]], $goipsend[tel]); //压回
							/*删除数据库*/
							$goipdb[$the0][send]="RMSG";
							$goipdb[$the0][timer]=20;
							$goipdb[$the0][tel]=0;
							$goipdb[$the0][telid]=0;						
						}	
								
						}	
					}
					$goipdb[$the0]['time']=$nowtime;
					
					//else $flag++;//完成
				}
			}//else{ //可读
			/*检查超时*/
		}//for(;;){
		foreach($socks as $socket){
			foreach($goipdb as $the => $goipnow){
				//echo "$key => $val\n";
				if($goipnow[sock]==$socket){
					break;
				}	
			}
			if($goipnow[sock]==$socket){
				$buf="DONE ".$goipdb[$the][messageid]."\n";
				socket_sendto($socket,$buf, strlen($buf), 0, "127.0.0.1", $port);
			}
		}
		//$i=0;
		//$i=count($tels);
		//$i+=count($errortels);
		echo "发送完毕！";
		echo "<br><br>";
		echo "<a href=sendinfo.php?id=$sendid target=main><font size=2>点我查看详情</font></a>";
}

	if(!empty($_GET[messageid])){
		if($_SESSION['goip_permissions'] <= 1)
			$merow=$db->fetch_array($db->query("SELECT id,msg FROM message where id=$_GET[messageid]"));
		else 
			$merow=$db->fetch_array($db->query("SELECT id,msg FROM message where id=$_GET[messageid] and userid=$_SESSION[goip_userid]"));	
		if(!$merow)
			die("不存在的发送或没有权限！");
		$sendid=$merow[id];
		$msg=$merow[msg];
		$len=strlen($msg);
		$query=$db->query("SELECT * FROM sends  where messageid=$_GET[messageid] and `over`=0 ORDER BY id");
		while($row=$db->fetch_array($query)) {
			$tels[$row[provider]][]=$row; 
			$totalnum++;
		}
	}
			
	elseif(!empty($_GET["id"])){
		$Id=$_GET["id"];
		$query=$db->query("SELECT * FROM sends  where id in ($Id) ORDER BY id");
		$flag=1;
		while($row=$db->fetch_array($query)) {
			if($flag){
				if($_SESSION['goip_permissions'] <= 1)
					$merow=$db->fetch_array($db->query("SELECT id,msg FROM message where id=$row[messageid]"));
				else 
					$merow=$db->fetch_array($db->query("SELECT id,msg FROM message where id=$row[messageid] and userid=$_SESSION[goip_userid]"));
				if(!$merow)
					die("不存在的发送或没有权限！");	
				$sendid=$merow[id];
				$msg=$merow[msg];
				$len=strlen($msg);
				$flag=0;
			}
			$tels[$row[provider]][]=$row; 
			$totalnum++;
		}
	}
	else 
		die("不存在的发送");
	//$Idb=$Ida;
	//$Idc=$Ida;
	echo "total: $totalnum <br>";
	//print_r($tels);
	startdo($db, $tels, $sendid, $msg, $len);

?>
</body>
</html>
