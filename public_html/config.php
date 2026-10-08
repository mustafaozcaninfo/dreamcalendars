<?php
date_default_timezone_set('US/Eastern');
$nowmonth=date("m");
$nowday=date("d");
$nowyear=date("Y");
$nowweek=date("W", strtotime("$nowyear-$nowmonth-$nowday"));
$nowdaynumber=date("z")+1;
$nextmonth=date('d', strtotime('+1 month'));
$howManyYear=1;
$nowmonthdesc=date("F");
$nextmonthdesc=date('F', strtotime('first day of next month', strtotime("$nowyear-$nowmonth-$nowday")));
$nextyear_date=date('Y', strtotime('+12 month', strtotime("$nowyear-$nowmonth-$nowday")));
$nextyear=date('Y', strtotime('+1 month', strtotime("$nowyear-$nowmonth-$nowday")));
$nowdaydesc=date("l");
$pages= basename($_SERVER['PHP_SELF']);
// Yılın yüzdelik hesaplaması
$now = new DateTime();
$startOfYear = new DateTime("$nowyear-01-01");
$endOfYear = new DateTime("$nowyear-12-31");

$yearProgress = ($now->getTimestamp() - $startOfYear->getTimestamp()) / ($endOfYear->getTimestamp() - $startOfYear->getTimestamp()) * 100;
$yearProgress = number_format($yearProgress, 1);

/* ======== Memcached Server Connection ========= */
//$memcache = new Memcached();
//$memcache->addServer('localhost', 11211) or die ("Could not connect");
/* ======== Memcached Server Connection ========= */

//$TTL = 2592000; //Second

function p($year){
    return ($year+floor($year/4)-floor($year/100)+floor($year/400))%7;
}

function weeks($year){
    $w = 52;
    if (p($year) == 4 || p($year-1) == 3){
        $w++;
    }
    return $w; // returns the number of weeks in that year
}


function build_calendar($month, $year) {
	$daysOfWeek = array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
	$firstDayOfMonth = mktime(0,0,0,$month,1,$year);
	$numberDays = date('t',$firstDayOfMonth);
	$dateComponents = getdate($firstDayOfMonth);
	$monthName = $dateComponents['month'];
	$dayOfWeek = $dateComponents['wday'];
	$calendar = "<table class='calendar table table-condensed table-bordered justify-content-center text-center'>";
	$calendar .= "<tr style='background-color: #eeeeee;'>";
	foreach($daysOfWeek as $day) {
		$calendar .= "<th class='header'>$day</th>";
	}
	$currentDay = 1;
	$calendar .= "</tr><tr>";
	if ($dayOfWeek > 0) {
		$calendar .= "<td colspan='$dayOfWeek'>&nbsp;</td>";
	}
	$month = str_pad($month, 2, "0", STR_PAD_LEFT);
	while($currentDay <= $numberDays){
		if($dayOfWeek == 7){
			$dayOfWeek = 0;
			$calendar .= "</tr><tr>";
		}
		$currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
		$date = "$year-$month-$currentDayRel";
		// Is this today?
		if(date('Y-m-d') == $date) {
			$calendar .= "<td class='day bg-pink' rel='$date'><b>$currentDay</b></td>";
		} else {
			$calendar .= "<td class='day' rel='$date'>$currentDay</td>";
		}
		$currentDay++;
		$dayOfWeek++;
	}
	if($dayOfWeek != 7){
		$remainingDays = 7 - $dayOfWeek;
		$calendar .= "<td colspan='$remainingDays'>&nbsp;</td>";
	}
	$calendar .= "</tr>";
	$calendar .= "</table>";
	return $calendar;
}


function build_array(&$ccyy, &$mm, &$dd)
// build an array containing every date for the selected year
{
global $date;
global $today;
global $date_array;
static $month_no;
static $week_no;
// at change of month reset to week 1 of 6
if ($mm > $month_no) {
    $month_no = $mm;
    $week_no  = 1;
} // if

$dow = $today['wday'];  // get day of week (0 = Sunday, 6 = Saturday)
if ($dow == 0) {
    $dow = 7;                // convert Sunday to day 7
} // if

// add to array
$date_array[$month_no][$week_no][$dow] = $dd;

// after day 7 increment to next week
if ($dow == 7) {
    $week_no = $week_no + 1;
} // if

// increment date to next day
$date = strtotime("+1 day", $date);

// create array of date information
$today = getdate($date);

// extract ccyy, mm, dd
$ccyy = $today['year'];
$mm   = $today['mon'];
$dd   = $today['mday'];
return;

} // build_array

function print_array($date_array) {
// set of array for day-of-week
$day_names = array(1 => 'MON','TUE','WED','THU','FRI','SAT','SUN');
// set up array of month names
$month_names = array(1 => 'J A N U A R Y',
                          'F E B R U A R Y',
                          'M A R C H',
                          'A P R I L',
                          'M A Y',
                          'J U N E',
                          'J U L Y',
                          'A U G U S T',
                          'S E P T E M B E R',
                          'O C T O B E R',
                          'N O V E M B E R',
                          'D E C E M B E R');

echo "<table border='0'>\n";
echo '<colgroup width="50">';
echo '<colgroup span="6" width="20">';
echo '<colgroup width="20">';
echo '<colgroup span="6" width="20">';
echo '<colgroup width="20">';
echo '<colgroup span="6" width="20">';
echo '<colgroup width="20">';
echo "<colgroup span='6' width='20'>\n";

// print the array in 3 rows with 4 months in each row
for ($row = 1; $row <= 3; $row++) {
   // identify the starting month in current row
   $first = set_start_month($row);
   // process one row at a time (the 1st line contains the month names)
   echo "<tr class='month'>\n";
   for ($m = $first; $m <= $first+3; $m++) {
	   $url = strtolower(str_replace(' ', '', $month_names[$m]));
      echo "<td>&nbsp;</td><td colspan='6'><a href='/".$url."-2019-calendar' title='".$url." calendar'>" .$month_names[$m] ."</a></td>\n";
   } // for
   echo "</tr>\n";
   // output a line for each of the 7 days of the week
   for ($dow = 1; $dow <= 7; $dow++) {
      echo "<tr class='$day_names[$dow]'>\n";
      // step through each of the 4 months in this row
      for ($m = $first; $m <= $first+3; $m++) {
         // output the details for 4 consecutive months
         // 1st cell identifies the day of week (first month of the 4)
         if ($m == $first) {
            echo "<td class='dayname'>" .$day_names[$dow] ."</td>";
         } else {
            echo "<td>&nbsp;</td>"; // not first month, so cell is empty
         } // if
         // the next 6 cells are for the dates that fall on that day
         for ($week = 1; $week <= 6; $week++) {
            if (isset($date_array[$m][$week][$dow])) {
               echo "<td>" .$date_array[$m][$week][$dow] ."</td>";
            } else {
               echo "<td>&nbsp;</td>";
            } // if
         } // for
      } // for
      echo "\n</tr>\n";
   } // for
   // output a blank line at the end of each row of months
   echo "<tr><td colspan='28'>&nbsp;</td></tr>\n";
} // for

echo "</table>\n";

return;

} // print_array

function set_start_month($row) {
// identify the first month in each of the three rows
    if ($row == 1) {
        $first = 1;
    } elseif ($row == 2) {
        $first = 5;
    } else {
        $first = 9;
    } // if
    return $first;

} // set_start_month

function slug($filename)
{

$ext = pathinfo($filename, PATHINFO_EXTENSION);
$alt = basename($filename, ".".$ext);
$arr = explode("-",$alt);
$string = implode(" ",$arr);
    return $string;
}

function slug_uri($filename)
{

$ext = pathinfo($filename, PATHINFO_EXTENSION);
$alt = basename($filename, ".".$ext);
$arr = explode("-",$alt);
    return $alt;
}

$site='https://www.dreamcalendars.com/';
$cdn='https://cdn.dreamcalendars.com/';
$sitename='DreamCalendars.com';


require_once __DIR__ . '/includes/dc_head_assets.php';
$head = $dc_head_assets;
$facebook='';
$twitter='';
$youtube='#';
$blogger='';
$google='';
$flipboard='';
$reddit='';
$mix='';
$medium='';
$pinterest='';
					?>
