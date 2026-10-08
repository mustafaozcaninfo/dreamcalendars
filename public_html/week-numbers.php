<?php
include("config.php");

$year=$nowyear;

if (isset($_GET['y']) ) {

   $year= $_GET['y'];


}

$firstYear = 2019;
$lastYear = 2099;
if (!empty($firstYear<=$year && $year<=$lastYear))
{

  $ws=0;



  $showweek=1;

  $hl=0;


  $n=0;


  $ex=0 ;


  $themecolor="rgb(32, 169, 223)";


  $mt=0;

  $tm=2;



  $wdt=47.5;

  if ($tm==1)
  {
      $wdt=47.5;
      $hl=1;
        $ws=0;

  } else
  if ($tm==2)
  {
       $wdt=47.5;
      $showweek=1;
      $ws=0;
      $hl=1;
      $n=0;

  } else
  if ($tm==3)
  {
      $ws=0;
      $hl=0;
      $themecolor="#f2150e";
      $n=1;

  } else
  if ($tm==4)
  {
      $wdt=32.1;


  }


require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureWeekNumbers((int) $year);
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
<link rel="stylesheet" href="https://cdn.dreamcalendars.com/css/week-number/assets/dateTimePicker.css">
<style>
    span.prev
    {
        display:none;
    }
    span.next
    {
        display:none;
    }
    .datetimepicker
    {
        margin:5px;
    }
.container
{



padding-left:20px;



}
.datetimepicker{
margin: 5px;
float: left;
<?php if ($mt>0) { ?>
width: 100%;
max-width:100%;

<?php } else { ?>
width:<?php echo $wdt ?>%;
<?php } ?>
padding:0px !important;
}

@media only screen and (min-width: 992px) {
  .datetimepicker {
    width: 32%;
  }
}

@media only screen and (min-width: 1200px) {
  .datetimepicker {
    width: 24%;
  }
}

<?php if ($mt>0) { ?>
.datetimepicker table td
{
 height:100px;
}

<?php }  ?>
.numberCircle {
border-radius: 50%;
behavior: url(PIE.htc);
/* remove if you don't care about IE8 */
position:absolute;
left:2px;
top:2px;
background: #fff;
border: 2px solid #666;
color: #666;
text-align: center;
font: 12px Arial, sans-serif;
}
.datetimepicker .paging {
background: <?php echo $themecolor?>;
}

.datetimepicker table {

font-size:12px;
}
.datetimepicker table thead td
{
min-width:0px !important;
}
.copyright
{
height:20px;
text-align:center;
display:block;
width:100%;

}

.datetimepicker table td.unavailable

{
background: <?php echo $themecolor?>;
color: #fff;

}
.div#holidays {
margin: 0;
padding: 0;
width: 100%;
}
.notesarea{
 color: #cecece;
  font-size:12px;
}
.holidayarea
{
margin-left:30px;

-moz-column-count: 5;
    -moz-column-gap: 10px;
    -webkit-column-count: 5;
    -webkit-column-gap: 10px;
    column-count: 5;
    column-gap: 10px;
-webkit-column-rule: 1px solid #eee;
 -moz-column-rule: 1px solid #eee;
      column-rule: 1px solid #eee;

}


.holidaybox {
  font-size: 12px;
padding: 2px;
-moz-column-count: 2;
-moz-column-gap: 10px;
-webkit-column-count: 2;
-webkit-column-gap: 10px;
column-count: 1;
column-gap: 10px;
max-width:170px;
max-height:20%;
}
.datesnip{
font-weight:bold;
margin:3px;
width:50px;
}
.holidayinfo
{
padding-left:5px;
}

td.weeknumber{
border:0px !important;

}
.contaier h2
{
font-size:32px;
}
.weeknumber{
color: #cecece;
font-style: italic;
}
.highlight{
background:#e6e7ea;
}

dt { float: left;  overflow: hidden; white-space: nowrap;margin:2px;}
dd { float: left;  overflow: hidden }

dt:after { content: " ........................................................" }

<?php
if ($tm==2)
{

?>

.datetimepicker table td.unavailable {
background: #525151;
color: #fff;
}

.datetimepicker .paging .month-name {
text-transform: uppercase;
font-weight: 700;
color: #fff;
}

.datetimepicker .paging {
background: #007bff;
}

td.weeknumber {
border: 0px !important;
font-style: italic;
}
<?php
}

?>
</style>
  </head>

  <body>

    <div class="container">
<?php include ("nav.php"); ?>
    <main class="container">
<?php dc_render_breadcrumb(null, true); ?>
      <div class="row">
        <div class="col-md-12">
          <h1 class="pb-3 mb-4 border-bottom text-center">
Week Numbers <?=$year?>
          </h1>
          <div class="blog-post">
          <p> Number of weeks in <?=$year?> year is <strong><?=weeks($year)?> weeks</strong>. Weeks are according United States calendar format, Sunday first day and Saturday last day.
          </p>
          <?php if ($year==$nowyear) { ?>
          <p class="font-weight-bold">Current week number is <?=$nowweek?>.</p>
        <?php } ?>
        </div>
        <nav aria-label="Week Numbers pagination">
          <ul class="pagination justify-content-end">
            <?php if (!empty($firstYear<$year && $year<=$lastYear))
            { ?>
            <li class="page-item ">
              <a class="page-link" href="/week-numbers/<?=$year-1?>" tabindex="-1"><i class="fa fa-arrow-left"></i> Week numbers <?=$year-1?></a>
            </li>
          <?php } ?>
            <li class="page-item">
              <a class="page-link" href="/week-numbers/<?=$year+1?>">Week numbers <?=$year+1?> <i class="fa fa-arrow-right"></i></a>
            </li>
          </ul>
        </nav>
          <div class="row">
            <div id="fullyear" data-toggle="calendar"></div>
        </div><!-- /.blog-main -->
</div>
      </div><!-- /.row -->
<p>&nbsp;</p>
    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.dreamcalendars.com/js/dateTimePicker.js"></script>
<script>
jQuery(function ($) {
  $('#fullyear').calendar({
    month: 1,
    year: <?= (int) $year ?>,
    day_first: <?= (int) $ws ?>,
    num_next_month: 11,
    showWeekend: <?= (int) $showweek ?>,
    num_prev_month: 0,
    highlightWeekend: <?= (int) $hl ?>,
  });
});
</script>
  </body>
</html>
<?php } else
{
	header('Location: '.$site.'');
	exit;
}?>
