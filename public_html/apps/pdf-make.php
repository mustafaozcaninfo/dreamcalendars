<?php



for ($y = 2060; $y <= 2060; $y++) {
  $year=$y;
for ($i = 1; $i <= 12; $i++) {
  $monthNum = sprintf("%02s", $i);
  $month = date("F", mktime(null, null, null, $monthNum));
$checkimagepath=$_SERVER['DOCUMENT_ROOT']."/printable/".$year."/".strtolower($month)."/";
$images = array($checkimagepath."Free-Printable-".$month."-".$year."-Calendar.jpg", $checkimagepath."".$month."-".$year."-Calendar-Printable-(Monday).jpg", $checkimagepath."".$month."-".$year."-Calendar-Printable-Template.jpg", $checkimagepath."".$month."-".$year."-Calendar.jpg");
$pdf = new Imagick($images);
$pdf->setImageFormat('pdf');
$path = $_SERVER['DOCUMENT_ROOT'] . "/apps/pdf/".$year."/".strtolower($month);
if(!is_dir($path)){
  mkdir($path);
}
$pdf->writeImages($path."/".$month."-".$year."-Calendar.pdf", true);
}
}
