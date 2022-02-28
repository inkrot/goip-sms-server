<?php

define("OK", true);
require_once("session.php");
require_once("global.php");
$rs=$db->fetch_array($db->query("SELECT username,password FROM user WHERE id=1");

/**  
 * 发送post请求  
 * @param string $url 请求地址  
 * @param array $post_data post键值对数据  
 * @return string  
 */  

function send_post($url, $post_data) {   
  
  $postdata = http_build_query($post_data);   
  $options = array(
      'http' => array(
      'method' => 'POST',   
      'header' => 'Content-type:application/x-www-form-urlencoded',   
      'content' => $postdata,   
      'timeout' => 15 * 60 // 超时时间（单位:s）   
    )   
  );   
  $context = stream_context_create($options);   
  $result = file_get_contents($url, false, $context);   

echo $result;  
  return $result;   
}
$post_data = array(   
  'smsnum' => $argv[1],
  'Memo' => $argv[2],
  'smsprovider' => $argv[3],
  'smsgoip' => $argv[4],
  'method' => "2",
  'not_jump' => "1",
  'USERNAME' => $rs[0],
  'PASSWORD' => $rs[1]

);   
send_post("http://127.0.0.1/dosend.php", $post_data);   
?>
