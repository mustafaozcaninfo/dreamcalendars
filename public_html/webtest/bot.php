<?php
error_reporting(1);
ini_set('display_errors', 1);
include('ayar.php');
function msleep($time)
{
    usleep($time * 1000000);
}
function get_web_page( $url )
{
	$useragent = "Opera/9.80 (J2ME/MIDP; Opera Mini/4.2.14912/870; U; id) Presto/2.4.15";

  $options = array(
      CURLOPT_RETURNTRANSFER => true,     // return web page
      CURLOPT_HEADER         => false,    // don't return headers
      CURLOPT_FOLLOWLOCATION => true,     // follow redirects
      CURLOPT_ENCODING       => "",       // handle all encodings
      CURLOPT_USERAGENT      => $useragent, // who am i
      CURLOPT_AUTOREFERER    => true,     // set referer on redirect
      CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
      CURLOPT_TIMEOUT        => 120,      // timeout on response
  );

  $ch = curl_init( $url );
  curl_setopt_array( $ch, $options );
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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


function ambilKata($param, $kata1, $kata2){
		if(strpos($param, $kata1) === FALSE) return FALSE;
		if(strpos($param, $kata2) === FALSE) return FALSE;
		$start = strpos($param, $kata1) + strlen($kata1);
		$end = strrpos($param, $kata2, $start);
		$return = substr($param, $start, $end - $start);
		return $return;
	}

  if($_GET["search"])
{
echo "Welcome: ". $_GET['search']. "<br />";


$cxcek = $mysqli->query("SELECT * FROM  `cxkeys` WHERE id BETWEEN '2' AND '1450'
ORDER BY hit asc LIMIT 1") or die("Hata cx Olustu!");
while ($okucx=mysqli_fetch_array($cxcek)){
$cx=$okucx['key'];
$cxid=$okucx['id'];
$cxtoken=$okucx['cse_token'];
}



//$cx='006875544420874369383:j292udbed0u';
//$cxtoken='AKaTTZgWzOXIuO0SkSJDe2vrzzaX:1575223015381';

  $url = "https://cse.google.com/cse/element/v1?rsz=filtered_cse&num=20&hl=en&source=gcsc&gss=.com&cselibv=none&prettyPrint=true&cx=".$cx."&q=".$img."&cse_tok=".$cxtoken."&callback=x";


  $result = get_web_page( $url );
//var_dump($result);

  // Get and parse JSON output
  $page = $result['content'];



  $clearCB = ambilKata(($page), "x(",');');


//$page = str_replace("// API callback\nhandleResponse(", "", $page);
  //$page = str_replace(");", "", $page);
  $page=json_decode($clearCB, true);


  // Print results
$items = $page['results'];
$imgsrc = array();
if (empty($items)){
++$i;
echo 'bitti';
exit;
} else {

foreach ($items as $item)
  {

      $item = (object) $item;

//$content= addslashes((isset($item->titleNoFormatting))?$item->titleNoFormatting:'-');
//$titlegoogle= addslashes((isset($item->titleNoFormatting))?$item->titleNoFormatting:'-');

$cxupdate=$mysqli->query("update cxkeys set hit= hit+1  where id=".$cxid."");

} else {
  echo "error";
}
}



if ($cxcek) {
    mysqli_free_result($cxcek);
		mysqli_free_result($sql);
		mysqli_free_result($update);
		mysqli_free_result($cxupdate);

}
if ($mysqli) {
    $mysqli->close();
}
}
?>

<html>
<body>
  <form action="#" method="get">
<input type="text" name="search" placeholder="Your search"></input><br/>
<input type="submit" name="submit" value="Submit"></input>
</form>
</body>
</html>
