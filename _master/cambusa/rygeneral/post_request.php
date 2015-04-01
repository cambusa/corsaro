<?php
/****************************************************************************
* Name:            post_request.php                                         *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2012-2013  Rodolfo Calzetti                              *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function do_post_request($url, $postdata)
{
    $data = "";
    $boundary = "---------------------".substr(md5(rand(0,32000)), 0, 10);
      
    //Collect Postdata
    foreach($postdata as $key => $val){
        $data .= "--$boundary\n";
        $data .= "Content-Disposition: form-data; name=\"".$key."\"\n\n".$val."\n";
    }
    
    $data .= "--$boundary\n";
   
    $params = array('http' => array(
           'method' => 'POST',
           'header' => 'Content-Type: multipart/form-data; boundary='.$boundary,
           'content' => $data
        ));

   $ctx = stream_context_create($params);
   $fp = fopen($url, 'rb', false, $ctx);
  
   if (!$fp) {
      throw new Exception("Problem with $url, $php_errormsg");
   }
 
   $response = @stream_get_contents($fp);
   if ($response === false) {
      throw new Exception("Problem reading data from $url, $php_errormsg");
   }
   return $response;
}

function post_async($url, array $params)
{
    foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);  
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 30);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);
    fclose($fp);
}
?>