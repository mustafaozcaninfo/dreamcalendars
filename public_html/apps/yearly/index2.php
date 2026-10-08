<?php
$year=2019; // change this to another year 2017 or  2018 or 2019 Or ...

if ($_GET['y'] ) {
    
   $year= $_GET['y'];
   
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
            display:inline !important;
           box-shadow:0 0px 0px rgba(0,0,0,0);
        }
    td 
    {
        height: 100px;
}
    }
    </style>
    
  </head>
  <body>
    <div class="container">
       
     
      <h2><?=$year?></h2>
      <div class="row">
        <div class="col-xss-12">
          <div id="fullyear" data-toggle="calendar"></div>
        </div>
       
      </div>
      
  </div>
    <script type="text/javascript" src="scripts/components/jquery.min.js"></script>
    <script type="text/javascript" src="scripts/dateTimePicker.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function()
    {
      $('#basic').calendar();
      $('#glob-data').calendar(
      {
        unavailable: ['*-*-8', '*-*-10']
      });
     
      $('#custom-name').calendar(
      {
        day_name: ['CN', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'],
        month_name: ['Tháng Một', 'Tháng Hai', 'Tháng Ba', 'Tháng Tư', 'Tháng Năm', 'Tháng Sáu', 'Tháng Bảy', 'Tháng Tám', 'Tháng Chín', 'Tháng Mười', 'Tháng Mười Một', 'Tháng Mười Hai'],
        unavailable: ['2014-07-10']
      });
      
      $('#fullyear').calendar(
      {
         month:1,
         year:2022, 
        num_prev_month: 0,
        unavailable: ['2001-*-9', '2001-*-10'],
        onSelectDate: function(date, month, year)
        {
            alert(month+' Secildi . ');
        }
      });
    });
    </script>
  </body>
</html>