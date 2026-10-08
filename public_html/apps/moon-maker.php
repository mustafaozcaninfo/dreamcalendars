<?php
error_reporting(1);
ini_set('display_errors', 1);
require_once 'moon.php';
function kilometersToMiles($km){
    return number_format($km * 0.621371, 2, '.', ',');
}
$moon = new Solaris\MoonPhase();
$age = round($moon->get('age'), 1);
$stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
$distance = round($moon->get('distance'), 2);
$distance = number_format($distance);
$next = gmdate('G:i:s, j M Y', $moon->get_phase('next_new_moon'));
$demo = floor($moon->get('illumination')*100);
echo "The moon is currently $age days old, and is therefore $stage. ";
echo "It is $distance km from the centre of the Earth. ";
echo "The next new moon is at $next.<br>";

echo kilometersToMiles($distance)." miles";

echo $demo;
