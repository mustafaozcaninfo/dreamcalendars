
    <footer class="blog-footer" id="footer">
      <p class="text-center">Have you tried our best printable blank calendars? Now is the time to live life planned. Use our printable <?= $nowyear ?> calendars that let you do everything on time.</p> <p> You can easily download and edit our free printable calendars from your computer, mobile phone and tablet.</p>
      <p> We use cookies to personalize content and ads and to provide social functions and analyze traffic on our site. By continuing using our site, you accept our cookie policy and consent to the use of cookies.</p>
    <p class="text-center">
<a href="https://www.dreamkalender.de/kalender/<?= $nowyear ?>" title="Kalendar <?= $nowyear ?>">Kalendar <?= $nowyear ?></a>
   </p>

<p class="text-center"><a href="/pages/terms-of-use">Terms of Use</a> - <a href="/pages/privacy-policy">Privacy Policy</a> - <a href="/pages/contact">Contact</a>
</p>

<p>&copy; <?= $nowyear ?> <?= $sitename ?>  All Rights Reserved. </p>
    </footer>

    <script defer type="text/javascript">
    (function() {
      function loadAdSenseScript() {
        if (window.__dcAdsRequested) {
          return;
        }
        window.__dcAdsRequested = true;
        var element = document.createElement("script");
        element.src = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6725480146756741";
        element.async = true;
        element.crossOrigin = "anonymous";
        document.body.appendChild(element);
      }

      function loadAnalytics() {
        if (window.__dcGaRequested) {
          return;
        }
        window.__dcGaRequested = true;
        var element = document.createElement("script");
        element.async = true;
        element.src = "https://www.googletagmanager.com/gtag/js?id=G-6LP1BRVPT1";
        element.onload = function() {
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'G-6LP1BRVPT1');
        };
        document.body.appendChild(element);
      }

      window.addEventListener("load", function() {
        loadAdSenseScript();
        if ("requestIdleCallback" in window) {
          requestIdleCallback(loadAnalytics, { timeout: 3000 });
        } else {
          setTimeout(loadAnalytics, 2000);
        }
      });

      document.addEventListener("DOMContentLoaded", function() {
        if (typeof LazyLoad !== "undefined") {
          new LazyLoad({
            elements_selector: ".lazy"
          });
        }

        if (typeof jQuery === "undefined") {
          return;
        }

        jQuery(function($) {
          if ($("#sticker").length && window.matchMedia("(min-width: 992px)").matches) {
            $("#sticker").sticky({ topSpacing: 20 });
          }

          $(document).on("click", '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox();
          });

          $(".click-me").on("click", function(event) {
            event.preventDefault();
            window.location.href = $(this).data("href");
          });
        });
      });
    })();
    </script>

    <script defer src="<?= $cdn ?>js/appv3.js"></script>
