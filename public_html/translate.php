<?php
error_reporting(1);
ini_set('display_errors', 1);
include("config.php");
@$page = $_GET['article'];
include 'templates/translate/'.$page.'.php';
if (empty($title)) {
header('Location: '.$site.'');
exit;
}
require_once __DIR__ . '/includes/classes/MetaManager.php';
dc_meta()->configureNoindex($title);
dc_meta()->setCanonical($site . 'translate/' . $page);
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
<?php include("footer.php");?>
  </body>
</html>
