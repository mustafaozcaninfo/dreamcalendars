<?php
include("config.php");

require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
dc_meta()->configureDaylightSaving((int) $nowyear);

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
<style>

  .clock-container {
    display: flex;
    justify-content: space-around;
    flex-direction: column;
    align-items: center;
  }
  .clock {
    width: 200px;
    height: 200px;
    border: 10px solid black;
    border-radius: 50%;
    position: relative;
  }
  .hour, .minute {
    position: absolute;
    width: 50%;
    height: 6px;
    background-color: black;
    top: 50%;
    transform-origin: 100%;
    transform: rotate(0deg);
  }
  .hour {
    height: 8px;
  }
  .start-hour {
    transform: rotate(60deg); /* 2:00 */
  }
  .start-minute {
    transform: rotate(0deg); /* 0 minutes */
  }
  .end-hour {
    transform: rotate(90deg); /* 3:00 */
  }
  .end-minute {
    transform: rotate(0deg); /* 0 minutes */
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  table, th, td {
    border: 1px solid black;
  }
  th, td {
    padding: 10px;
    text-align: center;
  }
</style>
  </head>

  <body>

    <div class="container">
<?php include ("nav.php"); ?>
    <main class="container">
<?php dc_render_breadcrumb(null, true); ?>
      <div class="row">
        <div class="col-md-12">
          <h1 class="pb-3 mb-4 border-bottom text-center">
<?= $nowyear ?> Daylight Saving Time
          </h1>
          <div class="clock-container">
            <div class="clock">
              <div class="hour start-hour"></div>
              <div class="minute start-minute"></div>
            </div>
            <p>One hour forward at 02:00 a.m. to 03:00 a.m.<br>One hour less = So you can sleep one hour less!</p>
          </div>
          <div class="clock-container">
            <div class="clock">
              <div class="hour end-hour"></div>
              <div class="minute end-minute"></div>
            </div>
            <p>One hour backward at 03:00 a.m. to 02:00 a.m.<br>One hour more = So you can sleep one hour more!</p>
          </div>
        </div>

        <table id="dst-table">
          <thead>
            <tr>
              <th>Year</th>
              <th>Days to go</th>
              <th>Start DST</th>
              <th>Days to go</th>
              <th>End Daylight Saving</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
</div>
      </div><!-- /.row -->
<p>&nbsp;</p>
    </main><!-- /.container -->
  </div>
<?php include("footer.php");?>

<script>

function getSecondSundayOfMarch(year) {
    const date = new Date(year, 2, 1); // March 1
    const day = date.getDay();
    const secondSunday = day === 0 ? 8 : 15 - day; // Calculate the second Sunday
    return `In the night of Saturday, March ${secondSunday - 1}, ${year} on Sunday, March ${secondSunday}, ${year}`;
}

function getFirstSundayOfNovember(year) {
    const date = new Date(year, 10, 1); // November 1
    const day = date.getDay();
    const firstSunday = day === 0 ? 1 : 8 - day; // Calculate the first Sunday
    return `In the night of Saturday, November ${firstSunday - 1}, ${year} on Sunday, November ${firstSunday}, ${year}`;
}

const data = Array.from({ length: 76 }, (_, i) => {
    const year = 2024 + i;
    return {
        year: year,
        start: getSecondSundayOfMarch(year),
        end: getFirstSundayOfNovember(year),
    };
});

function calculateDaysToGo(targetDate) {
    const now = new Date();
    const [_, targetDateString] = targetDate.split(' on ');
    const target = new Date(targetDateString.trim());
    const differenceInTime = target.getTime() - now.getTime();
    const differenceInDays = Math.ceil(differenceInTime / (1000 * 3600 * 24));
    return differenceInDays;
}

function populateTable() {
    const tbody = document.getElementById('dst-table').querySelector('tbody');
    data.forEach(row => {
        const startDaysToGo = calculateDaysToGo(row.start);
        const endDaysToGo = calculateDaysToGo(row.end);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.year}</td>
            <td>${startDaysToGo >= 0 ? startDaysToGo : '-'}</td>
            <td>${row.start}</td>
            <td>${endDaysToGo >= 0 ? endDaysToGo : '-'}</td>
            <td>${row.end}</td>
        `;
        tbody.appendChild(tr);
    });
}

populateTable();
setInterval(() => {
    const tbody = document.getElementById('dst-table').querySelector('tbody');
    tbody.innerHTML = '';
    populateTable();
}, 86400000); // Update every 24 hours (86400000 milliseconds)
</script>

  </body>
</html>
