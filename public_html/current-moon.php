<?php
error_reporting(1);
ini_set('display_errors', 1);
include("config.php");
require_once 'function/moon.php';
function kilometersToMiles($km){
    return number_format($km * 0.621371, 3, ',', ',');
}
$moon = new Solaris\MoonPhase();
$age = round($moon->get('age'), 1);
$stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
$phase_name = $moon->phase_name();
$illumination = round($moon->get('illumination')*100);
$distance = round($moon->get('distance'), 2);
$distance = number_format($distance);
$sun_distance = round($moon->get('sundistance'), 2);
$sun_distance = number_format($sun_distance);
$diameter = round($moon->get('diameter'), 2);
$sun_diameter = round($moon->get('sundiameter'), 2);
$next_new_moon = gmdate('M j, Y - G:i:s', $moon->get_phase('next_new_moon'));
$next_full_moon = gmdate('M j, Y - G:i:s', $moon->get_phase('full_moon'));

require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureCurrentMoon();

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
            Current (Today's) Moon Phase
          </h1>
          <div class="blog-post">
<div class="row justify-content-center">
  <div class="col-xs-4">
  <div class="card">
    <img class="card-img-top" src="<?=$cdn?>moon/current/<?=$illumination?>.jpg" alt="<?=$illumination?>% visible - <?=$phase_name?>">
    <div class="card-body">
      <h5 class="card-title">Today's moon phase</h5>
      <p class="card-text">Phase: <?=$phase_name?></p>
<p class="card-text">Illumination: <?=$illumination?>%</p>
<p class="card-text">Moon Age: <?=$age?> days (<?=$stage?>)</p>
<p class="card-text">Moon Angle: <?=$diameter?></p>
<p class="card-text">Moon Distance: <?=kilometersToMiles($distance)?> miles (<?=$distance?> km)</p>
<p class="card-text">Sun Angle: <?=$sun_diameter?></p>
<p class="card-text">Sun Distance: <?=$sun_distance?> km</p>
<p class="card-text font-weight-bold">Next New Moon: <?=$next_new_moon?></p>
<p class="card-text font-weight-bold">Next Full Moon: <?=$next_full_moon?></p>
      <p class="card-text"><small class="text-muted">Last updated a few seconds ago</small></p>
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
  </body>
</html>
