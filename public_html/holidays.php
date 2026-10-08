<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
UPDATE holidays SET link ="presidents-day" WHERE `holidays` LIKE '%armed%'

*/
include("config.php");
include("connection.php");
$year = $_GET['year'];
$firstYear = 2019;
$lastYear = 2099;
if (!empty($firstYear<=$year && $year<=$lastYear))
{
$holidayRows = [];
$listItems = [];
$sql = 'SELECT * FROM holidays WHERE year=' . (int) $year;
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $holidayRows[] = $row;
        $link = trim((string) ($row['link'] ?? ''));
        if ($link !== '') {
            $listItems[] = [
                'name' => (string) $row['holidays'],
                'url' => rtrim($site, '/') . '/when-is/' . $link,
            ];
        }
    }
}
require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureHolidaysYear((int) $year, $listItems);
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
        <?=$year?> Holidays
          </h1>
          <div class="blog-post">

            <?php $dc_ad_class = 'DreamCalendars_Blank_Header'; $dc_ad_slot = '9684019604'; $dc_ad_format = 'auto'; require __DIR__ . '/includes/dc_ad_slot.php'; ?>

            <p>The following list includes major <strong>(Federal) holidays</strong> in the United States. You can print <strong>holidays</strong> prepared in month order by selecting the day and date.</p>
            <nav aria-label="Holidays navigation">
              <ul class="pagination justify-content-end">
                <?php if (!empty($firstYear<$year && $year<=$lastYear))
                { ?>
                <li class="page-item ">
                  <a class="page-link" href="/holidays/<?=$year-1?>" tabindex="-1"><i class="fa fa-arrow-left"></i> <?=$year-1?> Holidays</a>
                </li>
              <?php } ?>
                <li class="page-item">
                  <a class="page-link" href="/holidays/<?=$year+1?>"><?=$year+1?> Holidays <i class="fa fa-arrow-right"></i></a>
                </li>
              </ul>
            </nav>
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
$now = time();
foreach ($holidayRows as $row) {
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
      $conn->close();

 ?>
    </tbody>
</table>

<p>Check out the holidays in the following years.</p>
<ul>
  <?php

for($i = $nowyear; $i<$nowyear+4; $i++) {

  echo '<li><a href="/holidays/'.$i.'" target="_blank">'.$i.' Holidays</a></li>';
  }
?>
</ul>

<h2>Download or view the <?=$year?> calendar</h2>
See also view the <a href="/calendar/<?=$year?>"><?=$year?> calendar</a>.

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
