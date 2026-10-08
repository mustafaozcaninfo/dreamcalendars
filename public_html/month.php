<?php
error_reporting(1);
ini_set('display_errors', 1);
include("config.php");
include("connection.php");
@$month = $_GET['month'];
$nmonth = date('n',strtotime($month));
@$year = $_GET['year'];
$firstYear = 2019;
$lastYear = 2099;
$date_now=$year."-".$month."";
$to_date=strtotime($date_now);
$next_year = date('Y', strtotime($date_now.' +1 month'));
$next_month = date('F', strtotime($date_now.' +1 month'));
$prev_year = date('Y', strtotime($date_now.' -1 month'));
$prev_month = date('F', strtotime($date_now.' -1 month'));
if (!empty($firstYear<=$year && $year<=$lastYear))
{
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
   header('Location: '.$site.'');
exit;
}
$new=strtotime($nowmonthdesc.$nowyear);
$date_now_prev=$nowyear."-".$nowmonthdesc."";
$prev_month_date = date('F', strtotime($date_now_prev.' -1 month'));
$next_year_date = date('Y', strtotime($prev_month_date.' +12 month'));
$next_new=strtotime($prev_month_date.$next_year_date);
require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureMonth($month, (int)$year, $title);
?>
<!doctype html>
<html lang="en-US" prefix="og: http://ogp.me/ns#">
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
          <nav aria-label="Month navigation">
            <ul class="pagination justify-content-center">
              <li class="page-item ">
                <a class="page-link" href="/calendar/<?=strtolower($prev_month).'-'.$prev_year?>" tabindex="-1"><i class="fa fa-arrow-left"></i> <?=$prev_month." ".$prev_year?></a>
              </li>
              <li class="page-item">
                <a class="page-link" href="/calendar/<?=strtolower($next_month).'-'.$next_year?>"><?=$next_month." ".$next_year?> <i class="fa fa-arrow-right"></i></a>
              </li>
            </ul>
          </nav>
           <article>
          <h1 class="pb-3 mb-2 border-bottom text-center" itemprop="name">Printable <?=$title.' '.$year?> Calendar with Holidays</h1>
          <div class="blog-post">
            <div class="ads row h-100 justify-content-center align-items-center mb-2">
<?php $dc_ad_class = 'DreamCalendars_Month_Header'; $dc_ad_slot = '3725115916'; require __DIR__ . '/includes/dc_ad_slot.php'; ?> </div>

<?php

if(($new<=$to_date) && ($to_date<=$next_new) && isset($content))
{
  echo '
      '.$content.'';


?>





<?php } else { ?>

            <article>
              <p itemprop="description"><strong>Blank <?=$title.' '.$year?> calendars</strong> are available in various designs. It is designed both vertically and horizontally. Our shared calendars are provided free of charge for your personal use. For commercial use, please contact us.</p>
              <p>You can download our <span style="text-decoration: underline;">printable blank calendars</span> in 4 different formats and print them for free. Our calendars are currently in <strong>PDF</strong> and <strong>Image</strong> (JPG) formats. Over time, MS
              Office (Word, Excel) will be designed for. Check out the calendar for the next year in <a href="/calendar/<?=strtolower($title).'-'.($year+1)?>"><?=$title.' '.($year+1)?></a>.</p>

</article>
<?php } ?>


<h2 class="text-center">Explore Our Range of <?=$title.' '.$year?> Calendar Templates</h2>
<div class="alert alert-primary alert-dismissible fade show mt-2" role="alert">
  <small><i class="fas fa-info-circle"></i>
The copyright of these images belongs to <strong>Dreamcalendars.com</strong>. When you use these images make sure <strong>give us image credit</strong>.</small>
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>


          <div class="album">
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-6725480146756741"
                 data-ad-slot="3643846827"
                 data-ad-format="rectangle"
                 data-full-width-responsive="true"></ins>
            <?php require __DIR__ . '/includes/dc_ad_push.php'; ?>
            <div class="container">
              <div class="row">
                <?php
                  //<div class="ads row h-100 justify-content-center align-items-center mb-3">
                  //<div id="waldo-tag-4020"></div>
                //  </div>
                //$setKey = md5("luxecalendar_".$month.$year); //set the memcached key. should be unique
                //$getCacheDetail  = $memcache->get($setKey); // Get the cached content based on the key
              //$memcache->flush();
              /* This block check if cached value is avaliable then serve the data from cache else database*/
                //if ($getCacheDetail) {
                //	$assoc=$getCacheDetail;
                //}
                //else
                //{
                $handle = opendir(dirname(realpath(__FILE__)).'/printable/'.$year.'/'.$month.'/');
  $assoc = []; // Assure that $assoc is initialized as an array

  while ($file = readdir($handle)) {
      if ($file !== '.' && $file !== '..') {
          $assoc[] = $file; // Results storing in array
      }
  }

  closedir($handle); // Don't forget to close the directory handle

  sort($assoc);
  $i = 0;

  foreach ($assoc as $filestring) {
      if ($filestring !== '.' && $filestring !== '..') {
          $alt = slug($filestring);
          $downloadurl = slug_uri($filestring);
          $filePath = dirname(realpath(__FILE__)).'/printable/'.$year.'/'.$month.'/'.$filestring;
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
                                  <a class="click-me" href="/printable/'.$year.'/'.$month.'/'.$filestring.'" data-href="/download/monthly/'.$year.'/'.$month.'/'.$downloadurl.'" title="'.$alt.'">
                              <img itemprop="photo" class="img-fluid card-img-top lazy" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="/printable/'.$year.'/'.$month.'/'.$filestring.'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'">
                              <div class="card-body">
                                <p class="card-text text-center">'.$alt.'</p>
                              </div></a>
                            </div>
                          </div>';
      }
      $i++;
  }

                /*
                <div class="col-md-6">
                            <div class="card mb-6 shadow-sm mb-3">
                                  <a class="click-me" href="/printable/'.$year.'/'.$month.'/'.$filestring.'" data-href="/download/monthly/'.$year.'/'.$month.'/'.$downloadurl.'" title="'.$alt.'">
                              <img itemprop="photo" class="card-img-top lazy" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="/printable/'.$year.'/'.$month.'/'.$filestring.'" alt="'.$alt.'">
                              <div class="card-body">
                                <p class="card-text text-center">'.$alt.'</p>
                              </div></a>
                            </div>
                          </div>
                          */
            		?>

              </div>
            </div>
          </div>

          <ins class="adsbygoogle"
               style="display:block"
               data-ad-client="ca-pub-6725480146756741"
               data-ad-slot="9630670673"
               data-ad-format="rectangle"
               data-full-width-responsive="true"></ins>
          <?php require __DIR__ . '/includes/dc_ad_push.php'; ?>
          <h2 class="text-center">Blank <?=$title.' '.$year?> Calendar PDF</h2>
                    <p class="lead">We merged 4 different templates in a <u>single PDF file</u>. Just click on the link below to free download calendars now.</p>
                         <p><a href="/printable/pdf/<?=$year?>/<?=$month."/".$title."-".$year."-Calendar.pdf"?>" class="btn btn-danger">Download <?=$title.' '.$year?> Calendar PDF</a></p>


<?php   if (!empty($content_more)) {
    echo $content_more;
  }
  ?>
                         <nav>
                           <div class="nav nav-tabs" id="nav-tab" role="tablist">
                             <a class="nav-item nav-link active" id="nav-calendar-tab" data-toggle="tab" href="#nav-calendar" role="tab" aria-controls="nav-calendar" aria-selected="true"><i class="fas fa-calendar"></i> <?=$title.' '.$year?></a>
                             <a class="nav-item nav-link" id="nav-holidays-tab" data-toggle="tab" href="#nav-holidays" role="tab" aria-controls="nav-holidays" aria-selected="false"><i class="fas fa-calendar"></i> <?=$title.' '.$year?> Holidays</a>
                           </div>
                         </nav>
                         <div class="tab-content" id="nav-tabContent">
                           <div class="tab-pane fade show active" id="nav-calendar" role="tabpanel" aria-labelledby="nav-calendar-tab">
                             <div class="row">
                             <div class="col-xs-12 col-md-12 col-lg-6 mt-3"><?php $calendar = build_calendar($nmonth, $year);
 echo $calendar; ?>
 <p>View the printable <a href="/calendar/<?=$year?>"><?=$year?> calendar</a>.</p>
</div>
                             <div class="col-xs-12 col-md-12 col-lg-6 mt-3"><?php
                             $_GET['lat'] = "40.7142691";
                             $_GET['lon'] = "-74.0059729";
                             $_GET['year'] = $year;
                             $_GET['month'] = $nmonth;
                             $_GET['tz'] = "US/Eastern";

                             include("./sunrise/year.php");
                              ?>

</div>
                           </div>

                           <div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
 All the times are calculated according to the <strong>US / Eastern</strong> time zone. <strong>New York / US</strong> sunrise and sunset times are shown by day in the <?=$title.' '.$year?> calendar. New cities will be added very soon.
 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
   <span aria-hidden="true">&times;</span>
 </button>
</div></div>
                           <div class="tab-pane fade" id="nav-holidays" role="tabpanel" aria-labelledby="nav-holidays-tab"><?php

                           $sql = "SELECT * FROM holidays WHERE year=$year and month='$title'";
                          $result = $conn->query($sql);
                          $now = time();
                          if ($result->num_rows > 0) {
                          ?>

                          <h2 class="mt-3"> <?=$title.' '.$year?> Holidays</h2>
                          <div class="table-responsive">
                           <table class="table table-bordered">
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
                           <td style="font-weight:bold"><?=$row["day"]?></td>
                           <td><a href="/when-is/<?=$row["link"]?>" itemprop="url" title="<?=$row["holidays"]?>"><span itemprop="name"><?=$row["holidays"]?></span></a></td>
                           <td><?=$days_remaining?></td>
                          </tr>
                          <?php

                        }
                        echo '  </tbody>
                          </table>
                          </div>';
                          }
                          $conn->close();

                          ?>
 <p>View the <a href="/holidays/<?=$year?>"><?=$year?> holidays</a>.</p>
<h3>Related calendars</h3>
<ul>
<li><a href="/calendar/<?= strtolower($prev_month) ?>-<?= $prev_year ?>"><?= $prev_month ?> <?= $prev_year ?> calendar</a></li>
<li><a href="/calendar/<?= strtolower($next_month) ?>-<?= $next_year ?>"><?= $next_month ?> <?= $next_year ?> calendar</a></li>
<li><a href="/calendar/<?= $year ?>"><?= $year ?> yearly calendar</a></li>
<li><a href="/calendar/blank">Blank calendar template</a></li>
</ul>
                        </div>


                         </div>




  </div>
  </article>
        </div><!-- /.blog-main -->
<?php include("right_menu.php");?>

      </div><!-- /.row -->
    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>
  </body>
</html>
<?php } else
{
	header('Location: '.$site.'');
	exit;
}?>
