<?php include("config.php");
include("connection.php");
$firstYear = (int)date('Y');
$year = $_GET['year'];
$firstYear = 2019;
$lastYear = 2099;
$year = intval(str_replace('.','',$year));

include 'templates/year/year.php';

    if (!empty($firstYear<=$year && $year<=$lastYear))
    {
require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureYear((int)$year);
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
    <main class="container">
<?php dc_render_breadcrumb(); ?>
      <div class="row">
        <div class="col-md-12 col-lg-9 blog-main">
          <nav aria-label="Yearly Calendar pagination">
            <ul class="pagination justify-content-center">
              <?php if (!empty($firstYear<$year && $year<=$lastYear))
              { ?>
              <li class="page-item ">
                <a class="page-link" href="/calendar/<?=$year-1?>" tabindex="-1"><i class="fa fa-arrow-left"></i> <?=$year-1?> Calendar</a>
              </li>
            <?php } ?>
              <li class="page-item">
                <a class="page-link" href="/calendar/<?=$year+1?>"><?=$year+1?> Calendar <i class="fa fa-arrow-right"></i></a>
              </li>
            </ul>
          </nav>
          <h1 class="pb-3 mb-4 border-bottom text-center">
      <?=$year?> Calendar
          </h1>
          <div class="blog-post">
            <div class="ads row h-100 justify-content-center align-items-center mb-3">
      <?php $dc_ad_class = 'DreamCalendars_Year_Header'; $dc_ad_slot = '3725115916'; require __DIR__ . '/includes/dc_ad_slot.php'; ?> </div>
      <?php

      if(($year==2023) && isset($content))
      {
        echo '
            '.$content.'';
      ?>

    <?php } else { ?>

                <article>
                  <p>You can view the <?=$year?>, <?=$year+1?>, <?=$year+2?> calendars regularly on our website. Special days and holiday times are marked on the dates indicated on the calendars. You can also download and print calendars for free. Our calendars are free for personal use, please
      contact us for commercial use!</p>
  <p>&nbsp;</p>
  <p>View below the <strong>printable <?=$year?> Calendar</strong>!</p>
  <p>&nbsp;</p>
  <h2>About the <?=$year?> Calendar</h2>
  <p>The <?=$year?> annual calendar has been created in 4 different templates for you. Our platform works online. You can also view our calendars on a monthly template. If we take a look at our calendar templates, our calendar includes special holidays, a simple
      <?=$year?> calendar and a portrait <?=$year?> yearly calendar. In addition, you can view day numbers, week numbers, the lunar phase (moon calendar) in <?=$year?>, world clocks, leap years, holidays, and more.</p>

    </article>
    <?php } ?>

    <h2>Monthly Blank Printable <?=$year?> Calendar</h2>
    <ul>
      <?php  for ($m=1; $m<=12; $m++) {
              echo '<li><a href="/calendar/' . strtolower(date('F', mktime(0,0,0,$m, 1, date('Y')))) . '-'.$year.'">Printable ' . date('F', mktime(0,0,0,$m, 1, date('Y'))) . ' '.$year.' Calendar</a></li>';
          } ?>
    </ul>

<h3>Related resources</h3>
<ul>
<li><a href="/holidays/<?= $year ?>"><?= $year ?> US holidays</a></li>
<li><a href="/week-numbers/<?= $year ?>"><?= $year ?> week numbers</a></li>
<li><a href="/day-numbers/<?= $year ?>"><?= $year ?> day numbers</a></li>
<li><a href="/calendar/blank">Blank calendar templates</a></li>
</ul>


<h2 class="text-center"><?=$year?> Calendar</h2>
<div class="album">
  <div class="container">
    <div class="row">
      <?php
      //$setKey = md5("luxecalendar_".$month.$year); //set the memcached key. should be unique
      //$getCacheDetail  = $memcache->get($setKey); // Get the cached content based on the key
    //$memcache->flush();
    /* This block check if cached value is avaliable then serve the data from cache else database*/
      //if ($getCacheDetail) {
      //	$assoc=$getCacheDetail;
      //}
      //else
      //{
      $handle = opendir(dirname(realpath(__FILE__)).'/printable/yearly/'.$year.'/');
  $assoc = []; // Assure that $assoc is initialized as an array

  while ($file = readdir($handle)) {
      if ($file !== '.' && $file !== '..') {
          $assoc[] = $file; // Results storing in array
      }
  }

  closedir($handle); // Don't forget to close the directory handle

  sort($assoc);
  $row = 0;

  foreach ($assoc as $filestring) {
      if ($filestring !== '.' && $filestring !== '..') {
          $alt = slug($filestring);
          $downloadurl = slug_uri($filestring);
          $filePath = dirname(realpath(__FILE__)).'/printable/yearly/'.$year.'/'.$filestring;
          $imageSize = getimagesize($filePath);

          if ($imageSize) {
              $width = $imageSize[0];
              $height = $imageSize[1];
          } else {
              $width = 0;
              $height = 0;
          }

          echo '          <div class="col-md-6">
                            <div class="card mb-6 shadow-sm mb-3">
                                  <a class="click-me" href="/printable/yearly/'.$year.'/'.$filestring.'" data-href="/download/yearly/'.$year.'/'.$downloadurl.'" title="'.$alt.'">
                              <img loading="lazy" itemprop="photo" class="img-fluid card-img-top" src="/printable/yearly/'.$year.'/'.$filestring.'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'">
                              <div class="card-body">
                                <p class="card-text text-center">'.$alt.'</p>
                              </div>
                            </a>
                            </div>
                        </div>';
      }
  }
      ?>

    </div>
  </div>
</div>

<h2 class="text-center"><?=$year?> Calendar PDF</h2>
<p class="lead">We have combined all of our templates into a <strong>single pdf</strong> file. Download <?=$year?> printable calendar pdf immediately without wasting time!</p>
<p><button type="button" class="btn btn-danger" data-toggle="modal" id="downloadme" data-target="#downloadpdf">Download <?=$year?> Calendar PDF</button></p>

<h2 class="text-center"><?=$year?> Holidays</h2>
<div class="table-wrapper-scroll-y my-custom-scrollbar mb-3">
<table class="table table-striped table-hover table-condensed">
<thead>
<tr>
<th>Date</th>
<th>Day</th>
<th>Holiday</th>
<th>Day left</th>
</tr>
</thead>
<tbody>
<?php
$sql = "SELECT * FROM holidays WHERE year=$year";
$result = $conn->query($sql);
$now = time();
if ($result->num_rows > 0) {
// output data of each row
while($row = $result->fetch_assoc()) {
$datediff =  $row["holidaydate"] - $now;
if( 1 > $datediff ){
$days_remaining="-";
} else {
$days_remaining = floor($datediff/(60*60*24))+1;
}


?>
<tr>
<td><?=date('F d',$row["holidaydate"])?></td>
<td><strong><?=$row["day"]?></strong></td>
<td><a href="/when-is/<?=$row["link"]?>" itemprop="url" title="<?=$row["holidays"]?>"><span itemprop="name"><?=$row["holidays"]?></span></a></td>
<td><?=$days_remaining?></td>
</tr>
<?php

}
}
$conn->close();

?>
</tbody>
</table>
</div>




<!-- Download Modal -->
<div class="modal fade" id="downloadpdf" tabindex="-1" role="dialog" aria-label="downloadpdfTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="downloadpfLongTitle">Download <?=$year?> Calendar PDF</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>You can use our calendars on any platform. <?=$year?> calendars are available with 4 different template options. Calendars are <span style="text-decoration: underline;">free for your personal use</span>. Please <a href="/pages/contact" target="_blank">contact us</a> if
            you are using for commercial use.</p>
        <p>&nbsp;</p>
        <p><strong><?=$sitename?></strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button id="download" type="button" class="btn btn-primary" onclick="window.open('/printable/pdf/yearly/<?=$year?>/<?=$year?>-calendar.pdf')" disabled>Download</button>
      </div>
    </div>
  </div>
</div>
          </div>

        </div><!-- /.blog-main -->
<?php include("right_menu.php");?>

      </div><!-- /.row -->

    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>
<script>
document.getElementById('downloadme').onclick = function() {
  var id,downloadButton=document.getElementById("download"),counter=7;downloadButton.innerHTML="You can download the file in 7 seconds.",id=setInterval(function(){--counter<0?(downloadButton.innerHTML="Download",downloadButton.removeAttribute("disabled"),clearInterval(id)):downloadButton.innerHTML="You can download the file in "+counter.toString()+" seconds."},1e3);
};
</script>
  </body>
</html>
<?php } else
{
	header('Location: '.$site.'');
	exit;
}?>
