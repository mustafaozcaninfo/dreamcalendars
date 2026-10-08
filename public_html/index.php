<?php
error_reporting(1);
ini_set("display_errors", 1);
##header("HTTP/1.1 301 Moved Permanently");
##header("Location: https://www.dreamcalendars.com/calendar/august-2021");
##exit();
include "config.php";
include "connection.php";
require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureHome();
$dc_lcp_preloads = [
    ['href' => $cdn . 'images/main_page.png', 'media' => '(max-width: 767px)'],
    ['href' => $cdn . 'images/' . $nowyear . '-calendars.jpg', 'media' => '(min-width: 768px)'],
];
$dc_hero_mobile = $cdn . 'images/main_page.png';
$dc_hero_desktop = $cdn . 'images/' . $nowyear . '-calendars.jpg';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="google-adsense-account" content="ca-pub-6725480146756741">
    <meta name="author" content="www.dreamcalendars.com">
    <meta name="resource-type" content="document">
    <?= dc_meta()->render() ?>
    <?php require __DIR__ . '/includes/dc_lcp_preload.php'; ?>
    <?= $dc_head_assets ?>
  </head>

  <body>

    <div class="container">
<?php include "nav.php"; ?>

    <main class="container">
      <div class="row">
        <div class="col-md-8 blog-main">
          <h1 class="pb-3 mb-3 border-bottom text-center">
<?= $nowyear ?> Printable Calendar</h1>

          <div class="blog-post">
            <div class="ads row h-100 justify-content-center align-items-center mb-2">
            <?php $dc_ad_class = 'DreamCalendars_Month_Header'; $dc_ad_slot = '5557342766'; require __DIR__ . '/includes/dc_ad_slot.php'; ?>
            </div>
            <p>Edit and print your calendars for <?= $nowyear ?> using our collection of <strong><?= $nowyear ?> Calendar Templates</strong> for Excel, Word, PDF and image format. These calendars are great for families, clubs, and other organizations. Quickly print a blank yearly <?= $nowyear ?> calendar for your fridge, desk, planner, or wall using one of our PDFs or Images. Some <?= $nowyear ?> Holidays and religious observances are listed below for reference.</p>

<h2>Free Printable Calendars for <?= $nowyear ?></h2>
<p class="dc-hero-picture"><picture>
  <source media="(max-width: 767px)" srcset="<?= htmlspecialchars($dc_hero_mobile, ENT_QUOTES, 'UTF-8') ?>">
  <img src="<?= htmlspecialchars($dc_hero_desktop, ENT_QUOTES, 'UTF-8') ?>" class="img-fluid" title="<?= $nowyear ?> calendar" width="1414" height="2000" alt="<?= $nowyear ?> Calendar" fetchpriority="high" loading="eager" decoding="async">
</picture></p>
<p>Have a favorite calendar template for <?= $nowyear ?>? Would you please share it in the comments? Don&#39;t forget to print our free calendar templates for the year <?= $nowyear ?>!</p>

<h2>Holidays and Religious Observances in <?= $nowyear ?></h2>
<table class="table table-bordered">
  <thead>
    <tr>
      <th>Date</th>
      <th>Day</th>
      <th>Holiday</th>
    </tr>
  </thead>
  <tbody>
<?php
$sql = "SELECT * FROM holidays WHERE year=" . (int) $nowyear;
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $holidayName = htmlspecialchars((string) $row['holidays'], ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars((string) $row['link'], ENT_QUOTES, 'UTF-8');
?>
    <tr>
      <td><?= date('F d', $row['holidaydate']) ?></td>
      <td><?= htmlspecialchars((string) $row['day'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><a href="/when-is/<?= $link ?>" title="<?= $holidayName ?>"><?= $holidayName ?></a></td>
    </tr>
<?php
    }
}
$conn->close();
?>
  </tbody>
  </table>


<p>Find the complete list of <a href="/holidays/<?= $nowyear ?>"><?= $nowyear ?> holidays</a> and celebrations. Our printable calendars include federal holidays and are perfect for planning <?= $nowyear ?> and beyond.</p>


            <p><strong>Manage your life with a printable calendar</strong></p>
    <p>Personal <strong>printable calendars</strong> allow you to plan events in advance. Calendars are generally accessible from anywhere and are available in pdf and image formats on our website. Using our calendars, we'll provide a broad overview of what
        we can do faster and more effectively.</p>
    <p>There are calendars on the Internet that serve different purposes. We are working hard to offer printable calendars that are ideal for our dear visitors and more practical on our website. We share <a href="/calendar/<?= $nowyear ?>"><?= $nowyear ?> calendar with holidays</a> with different
        colors and different template formats. After you like calendars presented in various formats and sizes, you can download and print.</p>
<h2>Monthly Printable Calendars <?= $nowyear ?></h2>
<ul>
<?php for ($m = 1; $m <= 12; $m++) {
    $monthName = date('F', mktime(0, 0, 0, $m, 1, $nowyear));
    $monthSlug = strtolower($monthName);
?>
<li><a href="/calendar/<?= $monthSlug ?>-<?= $nowyear ?>">Printable <?= $monthName ?> <?= $nowyear ?> Calendar</a></li>
<?php } ?>
</ul>

<h2>Calendar Tools &amp; Planning Resources</h2>
<ul>
<li><a href="/leap-years">Leap years list and rules</a> — find which years have 366 days.</li>
<li><a href="/week-numbers/<?= $nowyear ?>"><?= $nowyear ?> week numbers</a> — US and ISO 8601 week calendar.</li>
<li><a href="/day-numbers/<?= $nowyear ?>"><?= $nowyear ?> day numbers</a> — day-of-year reference table.</li>
<li><a href="/daylight-saving-time"><?= $nowyear ?> daylight saving time</a> — US DST start and end dates.</li>
<li><a href="/todays-moon-phase">Today's moon phase</a> — current lunar calendar.</li>
<li><a href="/calendar/blank">Blank calendar templates</a> — customize and print your own planner.</li>
</ul>

    <p>We handle our calendars in 3 different themes. You can create your own personal calendar completely by using our <a href="/calendar/blank">blank calendars</a>. For now, the calendars we present to you as monthly and yearly are shared in 4 different themes.
        To give a general name to them, we can say that it is a simple monthly calendar, monthly calendar with holidays, monthly calendar beginning of Monday start day and finally calendar according to classical American formats.</p>
    <p>&nbsp;</p>
    <p>So do you need a specialist process to print out these calendars? No, just one click to download and print out the platform you want. Whether you are a beginner or an expert, the purpose of our website is to provide you with calendars that you can print
        out as soon as possible.</p>

    <p>We would like to briefly touch on another feature. With this feature, you can use your personal calendars to create your own personal calendar with just a few clicks by entering information on your chosen day, month, year using our online calendar creator.
        We'll give you information about this system in the future!</p>
    <h2>History of Calendar</h2>
    <p>The calendars were determined on a number of basis and reached to the present day. They are generally equated with the cycle of some astronomical events, such as the sun and the moon cycle, but are born through natural phenomena such as harvesting time,
        rising and withdrawal of waters.</p>
    <p>Many calendar systems have been invented to date. A few examples.</p>
    <ul>
        <li>Hijri calendar</li>
        <li>Mayan</li>
        <li>the Aztec</li>
        <li>iranian</li>
        <li>Hindu</li>
        <li>Buddhist</li>
        <li>Pre-Columbian Mesoamerican</li>
        <li>Hellenic</li>
        <li>Lunar</li>
        <li>Chinese</li>
        <li>Julian</li>
        <li>Gregorian</li>
    </ul>
    <p>According to research, there are approximately 40 different calendar systems around the world. These are the most used <strong>Julian</strong> and <strong>Gregorian</strong> calendars. According to this calendar, if we are in <?= $nowyear ?> now, this figure may
        be different compared to other calendars. Looking back, the calendars have been in use for 2000 years. People followed the seasons of the seasons thousands of years ago. The aim was entirely to protect themselves from hunting and climate activities
        in nature. Now we are experiencing the same events again. In addition, we can transform ourselves into a more structured, programmed and systematic person using calendars.</p>

    <p>&nbsp;</p>
    <p>With love,<br />Helena</p>
          </div>

        </div><!-- /.blog-main -->
<?php include "right_menu.php"; ?>

      </div><!-- /.row -->

    </main><!-- /.container -->
  </div>
<?php include "footer.php"; ?>
  </body>
</html>
