<?php
function sefUrlConverter( $url ) {

  $clean = iconv('UTF-8', 'ASCII//IGNORE', utf8_encode($url) );
  $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
  $clean = strtolower(trim($clean, '-'));
  $clean = preg_replace("/[\/_|+ -]+/", "-", $clean);

  return $clean;

}

function stopit() {
  return  ++$i;

}

function referer_url( $url ) {
  $result = parse_url($url);
  return $result['scheme']."://".$result['host'];
}

function get( $url, $json_decode = false, $image = false, $proxy = false ) {

  if( $image == true ) {
    $headers = array(
      'accept: image/png,image/*;q=0.8,*/*;q=0.5'
    );
  } else {
    $headers = array(
      'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8'
    );
  }

  if( $proxy == true ) {

    $proxyOperation = new ProxyOperation();

    $proxyAddress = $proxyOperation->getProxyIp();

    echo $proxyAddress . PHP_EOL;

    if( $proxyAddress == false )
      return false;

  } else {
    $proxyAddress = null;
  }

  $options = array(
      CURLOPT_URL              => $url,
      CURLOPT_PROXY            => $proxyAddress,
      CURLOPT_RETURNTRANSFER   => true,     // return web page
      CURLOPT_HEADER           => false,    // don't return headers
      CURLOPT_FOLLOWLOCATION   => true,     // follow redirects
      CURLOPT_ENCODING         => "gzip, deflate, sdch, br",       // handle all encodings
      CURLOPT_USERAGENT        => "Googlebot-Image/1.0", // who am i
      CURLOPT_AUTOREFERER      => true,     // set referer on redirect
      CURLOPT_CONNECTTIMEOUT   => 10,      // timeout on connect
      CURLOPT_TIMEOUT          => 300,      // timeout on response
      CURLOPT_BUFFERSIZE       => 256,
      CURLOPT_NOPROGRESS       => true,
      CURLOPT_SSL_VERIFYPEER   => false,
      CURLOPT_REFERER          => referer_url($url),
      CURLOPT_HTTPHEADER       => $headers
      // CURLOPT_PROGRESSFUNCTION => function( $downloadSize, $downloaded ) {
      //     return ($downloaded > (50 * 1048576)) ? true : 'exceeds';
      // } // if file size exceeds 5mbs curl returns false
  );

  $ch = curl_init();

  curl_setopt_array( $ch, $options );

//        $info   = curl_getinfo($ch);
  $output   = curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close($ch);

  // var_dump($url);
  // var_dump($output);

  if( $httpcode == 200 ) {

    if( $json_decode == true ) {
      return json_decode( $output );
    } else {
      return $output;
    }

  } elseif( $httpcode == 404 ) {

    return '404';

    // var_dump($url);
    // var_dump($output);
    //print_r($httpcode);

  }

  return false;

}

 function save( $fileName, $file, $mimeType ) {

  echo "filename: {$fileName}" . PHP_EOL;
  echo "mimeType: {$mimeType}" . PHP_EOL;


  $headers = [
    "X-Auth-Token: 'gAAAAABc0qZRDze-7ahQHekQ4HlQMdumFWxPVxIAhPJIkQm95DjyIgkCX88MxTpFNnqorE4NuWEXbcgVTYRaRSLz5PM-cfMucn-zGG6ertI_aFIKDP5T7ODLzeIrUqTKJMKM7katNQkgiX98gDBoTl6uC6hOhgKPWvJIeY7wgfpy7C9sgvzkbug'",
    "Content-Type: {$mimeType}",
    "Cache-Control: max-age=29030400",
    "Content-Length: " . strlen( $file )
  ];



  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://storage.bhs3.cloud.ovh.net/v1/AUTH_7d824431ac674f9d8a6fabca332a91a8/img_dreamcalendario_es/{$fileName}");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $result  = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);

  if( isset($info["http_code"]) && $info["http_code"] == 201 ) {
    echo $info["http_code"]."\n";
    return true;
  }
echo $info["http_code"]."\n";
  return false;

}
