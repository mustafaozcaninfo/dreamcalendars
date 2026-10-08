<?php
error_reporting(1);
ini_set('display_errors', 1);
include("config.php");
@$page = $_GET['article'];
include 'templates/articles/'.$page.'.php';
if (empty($title)) {
header('Location: '.$site.'');
exit;
}
if (isset($redirect)) {
header("HTTP/1.1 301 Moved Permanently");
header('location: '.$redirect.'');
exit();
}
require_once __DIR__ . '/includes/classes/MetaManager.php';
require_once __DIR__ . '/includes/dc_breadcrumb.php';
require_once __DIR__ . '/includes/seo_config.php';
if (dc_article_is_noindex($page)) {
    dc_meta()->setRobots('noindex, follow');
}
$articleFaq = [];
if ($page === 'how-many-days-in-a-year') {
    $articleFaq = [
        [
            'question' => 'How many days are in a year?',
            'answer' => 'A common year has 365 days. A leap year has 366 days because February has 29 days instead of 28.',
        ],
        [
            'question' => 'How many days are in a leap year?',
            'answer' => 'A leap year has 366 days. Leap years occur every four years, with exceptions for century years not divisible by 400.',
        ],
    ];
} elseif ($page === 'how-many-weeks-in-a-year') {
    $articleFaq = [
        [
            'question' => 'How many weeks are in a year?',
            'answer' => 'A year has 52 weeks plus one or two extra days, depending on whether it is a common year or a leap year.',
        ],
        [
            'question' => 'Is it always 52 weeks in a year?',
            'answer' => 'Most years have 52 full weeks and 1 or 2 remaining days. That is why week counts can differ slightly between calendar systems.',
        ],
    ];
}
dc_meta()->configureArticle($page, $title, $article_desc, $articleFaq);
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
  </head>

  <body>

    <div class="container">
<?php include ("nav.php"); ?>
    <main class="container">
<?php dc_render_breadcrumb(); ?>
      <div class="row">
        <div class="col-md-12 col-lg-9 blog-main">
          <h1 class="pb-3 mb-4 border-bottom text-center">
        <?=$title?>
          </h1>
          <div class="blog-post">
          <?=$content?>
          </div>

        </div><!-- /.blog-main -->
<?php include("right_menu.php");?>

      </div><!-- /.row -->

    </main><!-- /.container -->
  </div>
<?php include("footer.php"); ?>
  </body>
</html>
