<?php

$ws=0;

if ($_GET['y'] ) {

   $year= $_GET['y'];


} else {
  $year=2019;

}

$showweek=1;

$hl=0;


$n=0;


$ex=0 ;


$themecolor="rgb(32, 169, 223)";


$mt=0;

$tm=1;



$wdt=24.1;

if ($tm==1)
{
    $wdt=24.1;
    $hl=1;
      $ws=0;

} else
if ($tm==2)
{
     $wdt=24.1;
    $showweek=0;
    $ws=0;
    $hl=0;
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


?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/dateTimePicker.css">
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
    color: #333;
}

.datetimepicker .paging {
    background: #ececec;
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
     <div  class="col-xss-12" style="text-align:center;">
             <h2><?=$year?></h2>
       </div>
      <div class="row">
        <div class="col-xss-12" style=" ">

          <div id="fullyear" data-toggle="calendar"></div>
        </div>
  



      </div>
       <div  class="copyright">

         <h4>Free Calendar Templates <b>DreamCalendars.com</b></h4>
             </div>
  </div>
    <script type="text/javascript" src="scripts/components/jquery.min.js"></script>
    <script type="text/javascript" src="scripts/dateTimePicker.js"></script>
    <script type="text/javascript">
    $(document).ready(function()
    {


      $('#fullyear').calendar(
      {
              month:1,
         year:<?=$year?>,
          day_first: <?=$ws?>,
        num_next_month: 11,
        showWeekend:<?=$showweek?>,
        num_prev_month: 0,
          highlightWeekend:<?=$hl?>,
        adapter: 'server/adapter.php',

      });
    });

function timeConverter(UNIX_timestamp){
  var a = new Date(UNIX_timestamp * 1000);
  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time = date + ' ' + month      ;
  return time;
}

    </script>

  </body>
</html>
