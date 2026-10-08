<?php
include("config.php");
include("connection.php");

@$whenis = $_GET['page'];
if(empty($whenis)) {
header("Location: $site");
exit();
}

$now = time();
$current_year=date('Y',$now);
$sql = "SELECT * FROM holidays WHERE link='$whenis' and year BETWEEN $current_year and $current_year+10";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
$first_query=$result->fetch_array();
$datediff =  $first_query["holidaydate"] - $now;
if( 1 > $datediff ){
$title_year=date('Y', strtotime('+1 years', $now));
} else {
$title_year=date('Y',$now);
}
include 'templates/when-is/'.$whenis.'.php';
if (isset($redirect)) {
header("HTTP/1.1 301 Moved Permanently");
header('location: '.$redirect.'');
exit();
}
$title="$day $title_year: When is $day $title_year & ".($title_year+1)."?";
$eventDateIso = !empty($first_query['holidaydate']) ? date('Y-m-d', (int) $first_query['holidaydate']) : null;
require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureWhenIs($whenis, $day, (int) $title_year, $eventDateIso);

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
        <?=$title?>
          </h1>
          <div class="blog-post">
            <div class="ads row h-100 justify-content-center align-items-center mb-2">
<?php $dc_ad_class = 'DreamCalendars_Month_Header'; $dc_ad_slot = '3725115916'; require __DIR__ . '/includes/dc_ad_slot.php'; ?> </div>

        <p>Below you can find dates of <strong><?=$day?> <?=$title_year?> and <?=$day?> <?=$title_year+1?></strong>. In the table you can check how many days you have been on holiday, which week is the holiday and which day of the month.</p>
 <div class="table-responsive">
 <table class="table">
<tbody>
<tr>
<thead class="bg-pink">
<th>When is ..?</th>
<th>Date</th>
<th>Day of the week</th>
<th>Week Number</th>
<th>Day left</th>
</tr>
</thead>
<?php
mysqli_data_seek($result, 0);
while($row = $result->fetch_assoc()) {
  $datediff =  $row["holidaydate"] - $now;
  if( 1 > $datediff ){
$days_remaining="Passed";
} else {
 $days_remaining = floor($datediff/(60*60*24))+1;
}
?>
<tr class="<?=($row["year"]==$title_year) ? "current_year":'regular_year';?>">
<td><span style="font-weight: bold;" itemprop="name"><?=$row["holidays"]?></span></td>
<td><?=date('F d, Y',$row["holidaydate"])?></td>
<td><?=$row["day"]?></td>
<td><?=date("W",$row["holidaydate"])?></td>
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
          <?=$content?>

          <p>Check out the <strong><?=$day?></strong> in the following years.</p>
          <ul>
            <?php

          for($i = $nowyear; $i<$nowyear+4; $i++) {

            echo '<li><a href="/holidays/'.$i.'" target="_blank">'.$i.' Holidays</a></li>';
            }
          ?>

          </div>

        </div><!-- /.blog-main -->
<?php include("right_menu.php");?>

      </div><!-- /.row -->

    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>
  </body>
</html>
