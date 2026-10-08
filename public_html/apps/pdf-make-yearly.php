<?php


  $year=$_GET['y'];
  $end=$_GET['end'];
$checkimagepath=$_SERVER['DOCUMENT_ROOT']."/printable/yearly/".$year."/";
$images = array($checkimagepath."".$year."-Calendar-(Portrait).jpg", $checkimagepath."".$year."-Calendar-Template.jpg", $checkimagepath."".$year."-Calendar-with-holidays.jpg", $checkimagepath."".$year."-Calendar.jpg");
$pdf = new Imagick($images);
$pdf->setImageFormat('pdf');
$path = $_SERVER['DOCUMENT_ROOT'] . "/apps/pdf/".$year."/";
if(!is_dir($path)){
  mkdir($path);
}
$pdf->writeImages($path."/".$year."-calendar.pdf", true);
?>

<?php

if ($end == $_GET['y']) {
  exit;
}
else {
  $next=$year+1;
  $url= 'https://www.dreamcalendars.com/apps/pdf-make-yearly.php?y='.$next.'&end='.$end.'';
  echo '<meta http-equiv="refresh" content="2; url='.$url.'"/>';
}

?>
