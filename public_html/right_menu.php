<aside class="col-md-12 col-lg-3 blog-sidebar">


  <div class="p-3 mb-3 bg-aqua rounded">
  <h4>Today Info</h4>
  <ul class="newslist">
  <li>
  <p><i class="fas fa-calendar"></i> <b><?=$nowdaydesc.' '.$nowmonthdesc.' '.$nowday.', '.$nowyear?></b></p>
  </li>
  <li>
  <p><b>Week Number: <?=$nowweek?></b></p>
  </li>
  <li>
  <p><b>The day of the year: <?=$nowdaynumber?></b></p>
  </li>
  <li>
      <p><b>Year Progress: <?=$yearProgress?>%</b></p>
    </li>

  <li>
  <p>	This month's calendar:  <a href="/calendar/<?=strtolower($nowmonthdesc)?>-<?=$nowyear?>"><?=$nowmonthdesc.' '.$nowyear?> </a>
  </p>

  </li>

  <li>
  <p>	Next month's calendar: <a href="/calendar/<?=strtolower($nextmonthdesc)?>-<?=$nextyear?>"><?=$nextmonthdesc.' '.$nextyear?></a>
  </p>
  </li>
  </ul>
  </div>

    <div class="p-3 mb-3 bg-aqua rounded">
    <h4><?=$nowyear?> Monthly Calendars</h4>
                <div class="card-body">
                  <ol class="list-unstyled mb-0 rightmenu">
                    <?php  for ($m=1; $m<=12; $m++) {
                            echo '  <li><a href="/calendar/' . strtolower(date('F', mktime(0,0,0,$m, 1, date('Y')))) . '-'.$nowyear.'" title="Printable ' . ucfirst(date('F', mktime(0,0,0,$m, 1, date('Y')))) . ' '.$nowyear.' Calendar">' . ucfirst(date('F', mktime(0,0,0,$m, 1, date('Y')))) . ' '.$nowyear.'</a></li>' . PHP_EOL;
                        }
                        ?>
                  </ol>
                </div>
  </div>

  <div class="card hovercard">
                <div class="cardheader lazy">

                </div>

                <div class="avatar lazy">
                    <img class="lazy" alt="Helena Orstem" width="100" height="100" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="<?=$cdn?>images/avatar-helena.jpg">
                </div>
                <div class="info">
                    <div class="title">
                        <a href="#">Helena Orstem</a>
                    </div>
                    <div class="desc">
    <div class="span4 collapse-group">
    <p>Hi there! I’m Helena, a college senior studying software engineer. I share lifestyle, magazine, and printable calendar. <span class="collapse" id="moreabout"> During my time at the university, I evaluated my free time and turned to drawing printable calendar template. I'm preparing a calendar and a printable document. I don't design colorful themes now, but I'm offering more simple templates free of charge. <br><br> You can send me your questions or comments about my content. <br><br> Best Regards,<br> Helena <br></span> <a data-toggle="collapse" data-target="#moreabout">More... &raquo;</a></p>
    </div>
    </div>
                </div>

            </div>




  <div id="accordion">


      <div class="d-none d-lg-block d-xl-block">
        <div id="sticker" style="width:300px">
          <ins class="adsbygoogle"
               style="display:block"
               data-ad-client="ca-pub-6725480146756741"
               data-ad-slot="2026140996"
               data-ad-format="auto"
               data-full-width-responsive="true"></ins>
          <?php require __DIR__ . '/includes/dc_ad_push.php'; ?>
        </div>
      </div>

  </div>



</aside><!-- /.blog-sidebar -->
