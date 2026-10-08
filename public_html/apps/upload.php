<?php
error_reporting(1);
ini_set('display_errors', 1);
require_once ("vendor/autoload.php");
require_once ("helpers.php");
$repository = new Dflydev\ApacheMimeTypes\PhpRepository;


## Error list
$imageerror=0;
$filetypeerror=0;
$imagesaverror=0;

$filename = "demo.jpg";
$handle = fopen($filename, "rb");
$image = fread($handle, filesize($filename));



try {

  $mimeType = new finfo(FILEINFO_MIME_TYPE);
  $mimeType = $mimeType->buffer($image);

  $fileExtension = $repository->findExtensions($mimeType);

} catch (Exception $e) {
  echo "file extension: " . PHP_EOL;
}



  if( is_array($fileExtension) && count($fileExtension) > 0 && preg_match("/\b(gif|jpeg|jpg|png|svg|tiff|tif|bmp)\b/i", $fileExtension[0], $matches) ) {
   $fileExtension = "." . $fileExtension[0];
  }


if($imageerror==0 && $filetypeerror == 0) {
  $imageName="demo/demo-thumbnail.jpg";

 try {

   $imageSaveResult = save( $imageName , $image , $mimeType);

   if( $imageSaveResult != true ) {
     echo "save failed: original";
   $imagesaverror=1;
   }

 } catch (Exception $e) {

  echo "image saving: " . PHP_EOL;

    $imagesaverror=1;

 }
 if ($imagesaverror == 0) {
        echo " saved\n". PHP_EOL;
      } else {
       echo "image saving error\n". PHP_EOL;
      }
}
