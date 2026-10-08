<?php
error_reporting(1);
ini_set('display_errors', 1);
require_once ("vendor/autoload.php");
$client = new ScraperAPI\Client("d6920db34caad52b7223047effb69b8a");
  $result = $client->get("http://httpbin.org/ip")->raw_body;
  print($result);
