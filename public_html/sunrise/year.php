<?php
include "./api/SunService.php";
include "./api/TimeZoneService.php";
error_reporting(0);


function getTimeDiff($dtime,$atime)
    {
        $nextDay = $dtime>$atime?1:0;
        $dep = explode(':',$dtime);
        $arr = explode(':',$atime);
        $diff = abs(mktime($dep[0],$dep[1],0,date('n'),date('j'),date('y'))-mktime($arr[0],$arr[1],0,date('n'),date('j')+$nextDay,date('y')));
        $hours = floor($diff/(60*60));
        $mins = floor(($diff-($hours*60*60))/(60));
        $secs = floor(($diff-(($hours*60*60)+($mins*60))));
        if(strlen($hours)<2){$hours="".$hours;}
        if(strlen($mins)<2){$mins="0".$mins;}
        if(strlen($secs)<2){$secs="0".$secs;}
        return $hours.'h '.$mins.'m';
    }

function displayLatLon($lat, $lon) {

    $latD = floor(abs($lat));
    $latM = round((abs($lat) - $latD)*60);
    if ($latM >= 60) {
        $latM -= 60;
        $latD++;
    }

    $latD = str_pad($latD, 2, '0', STR_PAD_LEFT);
    $latM = str_pad($latM, 2, '0', STR_PAD_LEFT);
    $latI = $lat >= 0 ? 'N' : 'S';

    $lonD = floor(abs($lon));
    $lonM = round((abs($lon) - $lonD)*60);
    if ($lonM >= 60) {
        $lonM -= 60;
        $lonD++;
    }

    $lonD = str_pad($lonD, 3, '0', STR_PAD_LEFT);
    $lonM = str_pad($lonM, 2, '0', STR_PAD_LEFT);
    $lonI = $lon >= 0 ? 'E' : 'W';

    return $latD.'&#xb0;'.$latM.'\''.$latI.' '.$lonD.'&#xb0;'.$lonM.'\''.$lonI;

}

function uniord($c) {
    $h = ord($c[0]);
    if ($h <= 0x7F) {
        return $h;
    } else if ($h < 0xC2) {
        return false;
    } else if ($h <= 0xDF) {
        return ($h & 0x1F) << 6 | (ord($c[1]) & 0x3F);
    } else if ($h <= 0xEF) {
        return ($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) << 6
                                 | (ord($c[2]) & 0x3F);
    } else if ($h <= 0xF4) {
        return ($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12
                                 | (ord($c[2]) & 0x3F) << 6
                                 | (ord($c[3]) & 0x3F);
    } else {
        return false;
    }
}

$utc = new DateTimeZone('UTC');

$lat = $_GET['lat'];
$lon = $_GET['lon'];
$year = $_GET['year'];
$month = $_GET['month'];
$tz = new DateTimeZone($_GET['tz']);
$place = $_GET['name'];
$dateUtc = new DateTime($year.'-01-01 00:00:00', $utc);

if ($lat >= -90 && $lat < -89) {
    $lat = -89;
}
if ($lat <= 90 && $lat > 89) {
    $lat = 89;
}

$sun = new SunService();

$latLonStr = displayLatLon($lat, $lon);

$sunDays = array();
while ($dateUtc->format('Y') == $year) {
    $sunDays[$dateUtc->format('m')][$dateUtc->format('d')] = $sun->calcDay($dateUtc, $lat, -$lon, $tz);
    $dateUtc->modify('+1 day');
}


$utf8 = false;
$utf8marker=chr(128);
$count=0;
while(isset($place[$count])){
    if($place[$count]>=$utf8marker) {
        $parsechar=substr($place,$count,2);
        $count+=2;
    } else {
        $parsechar=$place[$count];
        $count++;
    }
    if (uniord($parsechar) > 255) {
        $utf8 = true;
    }
}


$months = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');

$total= array_slice($sunDays, $month-1, 1);
$total_array= count( $total, COUNT_RECURSIVE );



echo '<div class="table-wrapper-scroll-y my-custom-scrollbar">
<table class="table table-striped table-hover table-condensed suntime">
<thead>
<tr>
<th style="width:138px;">Date</th>
<th>Sunrise</th>
<th>Sunset</th>
<th>Day length</th>
</tr>
</thead>
<tbody>';

for ($d = 1; $d < $total_array; $d++) {
    //$background = $d%2 == 0 ? '#EEEEEE' : '#FFFFFF';
    $table= '<tr>';
    $table = $table.'<td>'.$months[$month].' '.$d.', '.$year.'</td>';
        $sunDay = $sunDays[$month < 10 ? '0'.$month : ''.$month][$d < 10 ? '0'.$d : ''.$d];
        if ($sunDay) {
         $sunset_time= $sunDay->sunset->format('H:i');
         $sunrise_time= $sunDay->sunrise->format('H:i');
          if ($sunDay->sunrise != null) {
              $table = $table.'<td>'.$sunrise_time.'</td>';
          }

          if ($sunDay->sunset != null) {
              $table = $table.'<td>'.$sunset_time.'</td>';
          }

          if ($sunDay->sunrise != null && $sunDay->sunset != null) {
              $table = $table.'<td>'.getTimeDiff($sunrise_time,$sunset_time).'</td>';
          }


        }

 $table= $table. '</tr>';


echo $table;

}

echo '</tbody>
</table>
</div>';
 ?>
