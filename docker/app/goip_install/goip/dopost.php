<?php
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
  'name' => $argv[2],   
  'number' => $argv[3],
  'content' => $argv[4]
);   
//send_post('http://192.168.2.1/goip/post.php', $post_data);   
send_post($argv[1], $post_data);   
?>
