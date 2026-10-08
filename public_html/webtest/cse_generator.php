<?php
include('ayar.php');

function get_web_page( $url )
{
  $options = array(
      CURLOPT_RETURNTRANSFER => true,     // return web page
      CURLOPT_HEADER         => false,    // don't return headers
      CURLOPT_FOLLOWLOCATION => true,     // follow redirects
      CURLOPT_ENCODING       => "",       // handle all encodings
      CURLOPT_USERAGENT      => "spider", // who am i
      CURLOPT_AUTOREFERER    => true,     // set referer on redirect
      CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
      CURLOPT_TIMEOUT        => 120,      // timeout on response
  );

  $ch = curl_init( $url );
  curl_setopt_array( $ch, $options );
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  $content = curl_exec( $ch );
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );

  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $content;
  return $header;
}

$id = $argv[1];
$end = ($argv[2])?$argv[2]:null;
$i=0;
for($id; $id <= $end; $id++) {

  $calistirs = $mysqli->query("select * from cxkeys where id='$id'") or die("Hata Olustu!");
  $okus = mysqli_fetch_assoc($calistirs);
$key=urldecode($okus['key']);

$url="http://cse.google.com/cse.js?cx=".$key;

  $result = get_web_page( $url );
  $data=$result["content"];
preg_match_all('@"cse_token": "(.*?)"@si',$data,$userid);
$cse_token= urlencode($userid[1][0]);
$update=$mysqli->query("update cxkeys set cse_token='$cse_token'  where id=".$id."");

if( $id == $end )
     break;
}

   ?>
