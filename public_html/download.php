<?php
include("config.php");
@$month = $_GET['month'];
@$nextmonthdate = date('F', strtotime ( '+1 month' , strtotime ( $month ) ) );
@$year = $_GET['year'];
@$date_now=$year."-".$month."";
@$next_year = date('Y', strtotime($date_now.' +1 month'));
@$category = $_GET['cat'];
@$file = $_GET['file'];
@$alttitle=slug($file);
if(isset($month)||isset($year)||isset($category)||isset($file)) {
if ($category=="monthly") {
  $check = glob(dirname(realpath(__FILE__)).'/printable/'.$year.'/'.$month.'/'.$file.'.{jpg,jpeg,bmp,gif,png}', GLOB_BRACE);
  //$checkpdf = glob(dirname(realpath(__FILE__)).'/printable/pdf/'.$year.'/'.$month.'/'.$file.'.{pdf}', GLOB_BRACE);
  switch( $month ){
  case "january" : include 'templates/january.php';
  break;
  case "february" : include 'templates/february.php';
  break;
  case "march" : include 'templates/march.php';
  break;
  case "april" : include 'templates/april.php';
  break;
  case "may" : include 'templates/may.php';
  break;
  case "june" : include 'templates/june.php';
  break;
  case "july" : include 'templates/july.php';
  break;
  case "august" : include 'templates/august.php';
  break;
  case "september" : include 'templates/september.php';
  break;
  case "october": include 'templates/october.php';
  break;
  case "november": include 'templates/november.php';
  break;
  case "december": include 'templates/december.php';
  break;
  default:
    echo "404 error";
  exit;
  }
} else if ($category=="blank") {
  $check = glob(dirname(realpath(__FILE__)).'/printable/blank/'.$file.'.{jpg,jpeg,bmp,gif,png}', GLOB_BRACE);
  //$checkpdf = glob(dirname(realpath(__FILE__)).'/printable/blank/pdf/'.$file.'.{pdf}', GLOB_BRACE);
} else  {
  $check = glob(dirname(realpath(__FILE__)).'/printable/'.$category.'/'.$year.'/'.$file.'.{jpg,jpeg,bmp,gif,png}', GLOB_BRACE);
  //$checkpdf = glob(dirname(realpath(__FILE__)).'/printable/pdf/'.$year.'/'.$file.'.{pdf}', GLOB_BRACE);
}
if (!empty(array_filter($check))) {
$imgurl=basename($check[0]);
} else
{
header('Location: '.$site.'');
exit();
}

$firstYear = 2019;
$lastYear = 2099;
if(empty($year))
{
  $year=$nowyear;
}
if (!empty($firstYear<=$year && $year<=$lastYear))
{

  /*<a href="<?='/printable/'.$year.'/'.$month.'/'.$imgurl?>" alt="<?=$alttitle?>" class="btn btn-success btn-sm my-2" download><i class="fas fa-download"></i> Download</a>
  */
require_once __DIR__ . '/includes/classes/MetaManager.php';
dc_meta()->configureNoindex($alttitle . ' - Dream Calendars');
dc_meta()->setRobots('noindex, nofollow');
dc_meta()->setDescription('On this page you can download the calendar templates that we shared for free. Dream Calendars always strives to provide the best.');
if ($category=="monthly") {
    dc_meta()->setOgImage($site.'printable/'.$year.'/'.$month.'/'.$imgurl);
} else if ($category=="yearly") {
    dc_meta()->setOgImage($site.'printable/'.$category.'/'.$year.'/'.$imgurl);
} else {
    dc_meta()->setOgImage($site.'printable/'.$category.'/'.$imgurl);
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="author" content="www.dreamcalendars.com">
		<meta name="resource-type" content="document">
    <?= dc_meta()->render() ?>
    <?= $dc_head_assets ?>

  </head>

  <body>
    <div class="container">
<?php include ("nav.php"); ?>

    <main class="container-fluid">
      <div class="container">
  <div class="row">
    <div class="col-sm-5">
      <h1 class="pb-3 mb-4 border-bottom text-center download"><?=$alttitle?>
</h1>
<div class="d-flex justify-content-center mb-2">
  <!-- DreamCalendars_Download_Header -->
  <?php $dc_ad_class = 'DreamCalendars_Month_Header'; $dc_ad_slot = '9945279024'; require __DIR__ . '/includes/dc_ad_slot.php'; ?>
</div>
          <div class="card flex-md-row mb-4 shadow-sm">
            <div class="card-body d-flex flex-column align-items-start">
              <?php if ($category=="monthly") { ?>
                <figure>
                <a href="<?='/printable/'.$year.'/'.$month.'/'.$imgurl?>"><img class="img-responsive btn-block download card-img flex-auto lazy" id="first" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="<?='/printable/'.$year.'/'.$month.'/'.$imgurl?>" alt="<?=$alttitle?>"></a>
              </figure>



  <?php

  /*
  <a href="/make/<?=$month?>-<?=$year?>" target="_blank"  class="ajax-popup-link"></a>
  <div class="justify-content-center align-items-center">
  <p>

    <a href="/printable/pdf/<?=$year?>/<?=$month."/".$title."-".$year."-Calendar.pdf"?>" target="_blank" class="btn btn-primary btn-sm my-2"><i class="fas fa-eye"></i> View PDF</a>
    <button type="button" onclick="printJS('<?='/printable/'.$year.'/'.$month.'/'.$imgurl?>', 'image')" class="btn btn-danger btn-sm my-2">
<i class="fas fa-print"></i> Print Image
</button>

  </p>
   </div>
  */
  ?>

 </div>
</div>

<div class="alert alert-primary alert-dismissible fade show mt-2" role="alert">
  <small><i class="fas fa-info-circle"></i>
The copyright of these images belongs to <strong>Dreamcalendars.com</strong>. When you use these images make sure <strong>give us image credit</strong>.</small>
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>

<article>
  <p><strong>Blank <?=$title.' '.$year?> calendars</strong> are available in various designs. It is designed both vertically and horizontally. Our shared calendars are provided free of charge for your personal use. For commercial use, please contact us.</p>
  <p>You can download our <span style="text-decoration: underline;">printable blank calendars</span> in 4 different formats and print them for free. Our calendars are currently in <strong>PDF</strong> and <strong>Image</strong> (JPG) formats. Over time, MS
  Office (Word, Excel) will be designed for.</p>
<a href="/calendar/<?=$month?>-<?=$year?>" target="_blank" class="btn btn-dark btn-sm my-2"><i class="fas fa-chevron-left"></i> <?=$title.' '.$year?> Calendar</a>
<a href="/calendar/<?=strtolower($nextmonthdate)?>-<?=$next_year?>" target="_blank" class="btn btn-warning btn-sm my-2"> <?=$nextmonthdate.' '.$next_year?> Calendar <i class="fas fa-chevron-right"></i></a>
</article>
              <?php } else if ($category=="yearly")  { ?>
                  <figure>
                <img class="download card-img flex-auto lazy" id="first" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="<?='/printable/'.$category.'/'.$year.'/'.$imgurl?>" alt="<?=$alttitle?>">
              </figure>
 </div>
</div>
<article>
  <a href="/calendar/<?=$year?>" target="_blank" class="btn btn-dark btn-sm my-2"><i class="fas fa-chevron-left"></i> <?=$year?> Calendar</a>
  <a href="/calendar/<?=$year+1?>" target="_blank" class="btn btn-warning btn-sm my-2"> <?=$year+1?> Calendar <i class="fas fa-chevron-right"></i></a>
</article>
                  <?php } else  { ?>
                      <figure>
                  <a href="<?='/printable/'.$category.'/'.$imgurl?>" class="ajax-popup-link" target="_blank"><img class="download card-img flex-auto lazy" id="first" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="<?='/printable/'.$category.'/'.$imgurl?>" alt="<?=$alttitle?>"></a>
                </figure>
                <div class="justify-content-center align-items-center">
       <p>
         <a href="#" class="btn btn-success btn-sm my-2"><i class="fas fa-download"></i> Download</a>
         <a href="#" class="btn btn-primary btn-sm my-2"><i class="fas fa-eye"></i> View PDF</a>
         <a href="#" class="btn btn-danger btn-sm my-2"><i class="far fa-edit"></i> Customize Calendar</a>
       </p>
   </div>
   </div>
   </div>
                    <?php } ?>

        </div>
        <div class="col-sm-7">
          <h2 class="pb-3 mb-4 border-bottom text-center download"><i class="fas fa-thumbs-up"></i> Recommended for you
    </h2>
    <div class="container">
      <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-6725480146756741"
         data-ad-slot="9589484987"
         data-matched-content-rows-num="6"
         data-matched-content-columns-num="3"
         data-matched-content-ui-type="image_card_stacked"
         data-ad-format="autorelaxed"></ins>
      <?php require __DIR__ . '/includes/dc_ad_push.php'; ?>
        </div>
        </div>
    </div>

  </div>
</div>



    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>
  </body>
</html>
<?php }
}
else
{
	header('Location: '.$site.'');
	exit();
}?>
