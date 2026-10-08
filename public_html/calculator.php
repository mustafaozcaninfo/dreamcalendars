<?php
header('X-Robots-Tag: noindex, follow', true);
include("config.php");
$firstYear = (int)date('Y');
$year = $_GET['year'];
$year = intval(str_replace('.','',$year));
    switch ($year)
    {
        case $firstYear:
            include 'templates/year/'.$year.'.php';
        break;
		default:
   header('Location: '.$site.'');
exit;
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta name="robots" content="noindex, follow" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="www.luxecalendar.com">
    <meta name="resource-type" content="document">
    <meta name="description" content="Great 2019 calendars prepared to achieve success in a new year. Includes a blank printable 2019 template and gives information about how to use."/>
    <link rel="canonical" href="<?=$site.$year?>-calendar-printable.html" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?=$year?> Printable Calendars - Luxe Calendar" />
    <meta property="og:description" content="Great 2019 calendars prepared to achieve success in a new year. Includes a blank printable 2019 template and gives information about how to use." />
    <meta property="og:url" content="<?=$site.$year?>-calendar-printable.html" />
    <meta property="og:site_name" content="Luxe Calendar" />
    <meta name="twitter:description" content="Great 2019 calendars prepared to achieve success in a new year. Includes a blank printable 2019 template and gives information about how to use." />
    <meta name="twitter:title" content="<?=$year?> Printable Calendars - Luxe Calendar" />
    <meta name="twitter:site" content="@luxecalendar" />
    <meta name="twitter:creator" content="@luxecalendar" />
    <script type='application/ld+json'>{"@context":"https:\/\/schema.org","@type":"WebSite","@id":"#website","url":"https:\/\/www.luxecalendar.com\/","name":"Luxe Calendar"}</script>
    <title> - Luxe Calendar</title>
<?=$head?>
  </head>

  <body>

    <div class="container">
<?php include ("nav.php"); ?>

    <main class="container">
      <div class="row">
        <div class="col-md-8 blog-main">
          <h1 class="pb-3 mb-4 border-bottom text-center">
        <?=$title?>
          </h1>
          <div class="blog-post">
<?=$content?>
          </div>

        </div><!-- /.blog-main -->
<?php include("right_menu.php");?>

      </div><!-- /.row -->

    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>
  </body>
</html>
