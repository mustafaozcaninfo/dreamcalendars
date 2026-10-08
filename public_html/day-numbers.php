<?php
include("config.php");
$year = $_GET["year"];
$firstYear = 2019;
$lastYear = 2099;
if (!empty($firstYear<=$year && $year<=$lastYear))
{
$now = time();
$a_year_later = strtotime('Dec 31, '.$year.'');
$all_days = array();
// starting today
$next = strtotime('Jan 1, '.$year.'');
// increment day till a year has passed

require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureDayNumbers((int) $year);
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
          <h1 class="pb-3 mb-4 border-bottom text-center">
      Day Numbers <?=$year?>
          </h1>
          <div class="blog-post">
            <p>The following table gives information about <strong><?=$year?></strong> day numbers and week numbers, year of the percentage and the number of days left.</p>
            <nav aria-label="Day numbers pagination">
              <ul class="pagination justify-content-end">
                <?php if (!empty($firstYear<$year && $year<=$lastYear))
                { ?>
                <li class="page-item ">
                  <a class="page-link" href="/day-numbers/<?=$year-1?>" tabindex="-1"><i class="fa fa-arrow-left"></i> Day numbers <?=$year-1?></a>
                </li>
              <?php } ?>
                <li class="page-item">
                  <a class="page-link" href="/day-numbers/<?=$year+1?>">Day numbers <?=$year+1?> <i class="fa fa-arrow-right"></i></a>
                </li>
              </ul>
            </nav>
          <?php

          echo '<div class="table-responsive">
          <table class="table">
          <tbody>
          <tr>
          <thead class="bg-pink">
          <th>Day Number</th>
          <th>Date</th>
          <th>Day</th>
          <th>Days to go</th>
          <th>Week Number</th>
          <th>%</th>
          </tr>
          </thead>';

          while( 1 ){
              if( $next > $a_year_later )
                  break 1;
              $all_days[] = date( 'F j, Y', $next );
              $next_date[] = strtotime( date( 'F j, Y', $next ));
              $day_name[] = date( 'l', $next );
              $wnumber[] = (int)date( 'W', $next );
              $next = strtotime( '+1 Day', $next );

          }
          // result

          for( $i = 0; $i < $total = count($all_days); $i++ ){
            $datediff =  $next_date[$i] - $now;
            if( 1 > $datediff ){
           $days_remaining="-";
           } else {
           $days_remaining = floor($datediff/(60*60*24))+1;
           }
          $percentage = (($i+1) / $total ) * 100;
          ?>

          <tr>
          <td><?=$i+1?></td>
          <td><span style="font-weight: bold;" itemprop="name"><?=$all_days[$i]?></span></td>
          <td><?=$day_name[$i]?></td>
          <td><?=$days_remaining?></td>
          <td><?=$wnumber[$i]?></td>
          <td><? printf('%.2f%%', $percentage);?></td>
          </tr>
          <?php
          }

          ?>
          </tbody>
          </table>
          </div>

          </div>

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
