<?php
http_response_code(404);
include 'config.php';
require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->setTitle('404 - Page Not Found | Dream Calendars');
dc_meta()->setDescription('The page you requested could not be found. Browse our printable calendars, holidays, and planning tools.');
dc_meta()->setRobots('noindex, follow');
dc_meta()->configureNoindex();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?= dc_meta()->render() ?>
    <?= $dc_head_assets ?>
  </head>
  <body>
    <div class="container">
<?php include 'nav.php'; ?>
    <main class="container">
      <div class="row">
        <div class="col-md-12 blog-main">
          <h1 class="pb-3 mb-4 border-bottom text-center">Page Not Found</h1>
          <div class="blog-post">
            <p>Sorry, we could not find the page you were looking for. Try one of these popular pages:</p>
            <ul>
              <li><a href="/"><?= $nowyear ?> printable calendar home</a></li>
              <li><a href="/calendar/<?= $nowyear ?>"><?= $nowyear ?> yearly calendar</a></li>
              <li><a href="/holidays/<?= $nowyear ?>"><?= $nowyear ?> US holidays</a></li>
              <li><a href="/calendar/blank">Blank calendar templates</a></li>
              <li><a href="/leap-years">Leap years</a></li>
            </ul>
          </div>
        </div>
      </div>
    </main>
    </div>
<?php include 'footer.php'; ?>
  </body>
</html>
