<?php include("config.php");
@$month = $_GET['month'];
@$year = $_GET['year'];
switch( $month ){
case "january" : include 'templates/january.php';
$month="01";
break;
case "february" : include 'templates/february.php';
$month="02";
break;
case "march" : include 'templates/march.php';
$month="03";
break;
case "april" : include 'templates/april.php';
$month="04";
break;
case "may" : include 'templates/may.php';
$month="05";
break;
case "june" : include 'templates/june.php';
$month="06";
break;
case "july" : include 'templates/july.php';
$month="07";
break;
case "august" : include 'templates/august.php';
$month="08";
break;
case "september" : include 'templates/september.php';
$month="09";
break;
case "october": include 'templates/october.php';
$month="10";
break;
case "november": include 'templates/november.php';
$month="11";
break;
case "december": include 'templates/december.php';
$month="12";
break;
default:
  echo "404";
exit;
}
require_once __DIR__ . '/includes/classes/MetaManager.php';
$makeUrl = $site . 'make/' . strtolower(date('F', mktime(0, 0, 0, $month, 1, date('Y')))) . '-' . $year;
dc_meta()->configureNoindex('Customize your ' . $title . ' ' . $year . ' calendar - Dream Calendars');
dc_meta()->setRobots('noindex, nofollow');
dc_meta()->setDescription('You can make your personal calendars free of charge. Let\'s start preparing your monthly, weekly and daily calendars right now!');
dc_meta()->setCanonical($makeUrl);
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
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.slim.min.js'></script>
<script async src='/js/print.js'></script>
<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css' rel='stylesheet' />
<script async src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js'></script>
<script async src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js'></script>
<script>
$(document).ready(function(){$("#calendar").fullCalendar({themeSystem: 'bootstrap4', height: 650,customButtons:{print:{text:"print",click:function(){$("#calendar").printThis()}}},header:{left:"prev,next today print",center:"title",right:"month,agendaWeek,agendaDay,listWeek"},weekNumbers: true,navLinks:!0,selectable:!0,selectHelper:!0,select:function(e,t){var n,a=prompt("Event Title:");a&&(n={title:a,start:e,end:t},$("#calendar").fullCalendar("renderEvent",n,!0)),$("#calendar").fullCalendar("unselect")},defaultDate:"<?=$year?>-<?=$month?>-01",editable:!0,eventBackgroundColor:"#007bff",eventBorderColor:"#0062cc",eventLimit:!0,events:[]})});
</script>
  </head>

  <body>
    <div class="container">
<?php include ("nav.php"); ?>

    <main class="container">
      <div class="row">
        <div class="col-md-12 blog-main">
          <div class="ads mb-2">
            <!-- DreamCalendars_Editor_v2 -->
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-6725480146756741"
                 data-ad-slot="9684019604"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <?php require __DIR__ . '/includes/dc_ad_push.php'; ?>
</div>
          <div class="blog-post">
            	<div id='calendar'></div>
            <article>
              <span>
                <a href="/calendar/<?=strtolower($title)?>-<?=$year?>" target="_blank" class="button"><button type="button" class="btn btn-secondary"><i class="fa fa-search" aria-hidden="true"></i> Browse <?=$title.' '.$year?> Calendar</button></a>
              </span>
                  <p><?=$content?></p>
            </article>
          </div>

        </div><!-- /.blog-main -->

      </div><!-- /.row -->

    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>
  </body>
</html>
