<?php
$year = date('Y');
$month = date('n');
$day = date('j');
if ($month < 4) {$year = $year - 1; $month = $month + 12;}
$days_y = 365.25 * $year;
$days_m = 30.42 * $month;
$julian = $days_y + $days_m + $day - 694039.09;
$julian = $julian / 29.53;
$phase = intval($julian);
$julian = $julian - $phase;
$phase = round($julian * 8 + 0.5);
if ($phase == 8) {$phase = 0;}
$phase_array = array('new', 'waxing crescent', 'first quarter', 'waxing gibbous', 'full', 'waning gibbous', 'last quarter', 'waning crescent');
$lunar_phase = $phase_array[$phase];

echo $lunar_phase;

 ?>
