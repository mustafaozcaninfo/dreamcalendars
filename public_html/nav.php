<header class="blog-header py-3">
  <div class="row flex-nowrap justify-content-between align-items-center">
    <div class="col-12 text-center">
      <a class="blog-header-logo text-dark" href="/" title="Printable Calendar">Dream Calendars</a>
    </div>
  </div>
</header>
  <nav class="navbar navbar-expand-sm navbar-dark bg-pink py-1 mb-3 border-bottom shadow rounded">
    <button class="navbar-toggler navbar-toggler-right text-light" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon"></span>
    </button>
       <div class="collapse navbar-collapse" id="navbarSupportedContent">
         <ul class="navbar-nav">
           <li class="nav-item dropdown">
             <a class="nav-link p-2 text-dark dropdown-toggle" href="#" id="navbarDropdownYearly" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Calendars
             </a>
             <div class="dropdown-menu dropdown-menu-right bg-aqua" aria-labelledby="navbarDropdownYearly">
            <a class="dropdown-item" href="/calendar/blank" title="Blank Calendar"><i class="fas fa-calendar-alt"></i> Blank Calendar</a>

            <ul class="dropdown-submenu dropdown-item">
              <a href="#"><i class="fas fa-folder"></i> Yearly</a>
              <ul class="dropdown-menu">
                <?php

              for($i = $nowyear; $i<$nowyear+6; $i++) {

                echo '<a class="dropdown-item" href="/calendar/'.$i.'" title="'.$i.' Calendar"><i class="fas fa-calendar-alt"></i> '.$i.' Calendar</a>';
                }
              ?>

              </ul>
            </ul>

            <ul class="dropdown-submenu dropdown-item">
              <a href="#"><i class="fas fa-folder"></i> Monthly</a>
              <ul class="dropdown-menu">
                <?php foreach ([$nowyear, $nextyear_date] as $navYear) { ?>
                <ul class="dropdown-submenu dropdown-item">
                  <a href="#"><i class="fas fa-folder"></i> <?=$navYear?></a>
                  <ul class="dropdown-menu">
                    <?php for ($m = 1; $m <= 12; $m++) {
                        $monthName = date('F', mktime(0, 0, 0, $m, 1, $navYear));
                        $monthSlug = strtolower($monthName);
                        echo '<a class="dropdown-item" title="Printable ' . $monthName . ' ' . $navYear . ' calendar" href="/calendar/' . $monthSlug . '-' . $navYear . '"><i class="fas fa-calendar-alt"></i> ' . $monthName . ' ' . $navYear . '</a>';
                    } ?>
                  </ul>
                </ul>
                <?php } ?>
              </ul>
            </ul>

             </div>
           </li>


           <li class="nav-item dropdown">
             <a class="nav-link p-2 text-dark dropdown-toggle" href="#" id="navbarDropdownHolidays" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               Holidays
             </a>
             <div class="dropdown-menu dropdown-menu-right bg-aqua" aria-labelledby="navbarDropdownHolidays">
               <?php

             for($i = $nowyear; $i<$nowyear+6; $i++) {

               echo '<a class="dropdown-item" href="/holidays/'.$i.'"><i class="fas fa-calendar-alt"></i> '.$i.' Holidays</a>';
               }
             ?>
             </div>
           </li>

           <li class="nav-item dropdown">
             <a class="nav-link p-2 text-dark dropdown-toggle" href="#" id="navbarDropdownUseful" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               Useful Dates
             </a>
             <div class="dropdown-menu dropdown-menu-right bg-aqua" aria-labelledby="navbarDropdownUseful">
               <ul class="dropdown-submenu dropdown-item">
                 <a href="#"><i class="fas fa-folder"></i> Day Numbers</a>
                 <ul class="dropdown-menu">
                   <?php

                 for($i = $nowyear; $i<$nowyear+6; $i++) {

                   echo '<a class="dropdown-item" href="/day-numbers/'.$i.'"><i class="fas fa-calendar-alt"></i> Day Numbers '.$i.'</a>';
                   }
                 ?>
                 </ul>
               </ul>
               <ul class="dropdown-submenu dropdown-item">
                 <a href="#"><i class="fas fa-folder"></i> Week Numbers</a>
                 <ul class="dropdown-menu">
                   <?php

                 for($i = $nowyear; $i<$nowyear+6; $i++) {

                   echo '<a class="dropdown-item" href="/week-numbers/'.$i.'"><i class="fas fa-calendar-alt"></i> Week Numbers '.$i.'</a>';
                   }
                 ?>
                 </ul>
               </ul>
                <a class="dropdown-item" href="/leap-years" title="when is the next leap year"><i class="fas fa-calendar-alt"></i> Leap Years</a>
             </div>
           </li>


         </ul>

       </div>
     </nav>
</div>
