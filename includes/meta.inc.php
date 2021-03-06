<?php require_once 'includes/conf.inc.php';?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <?php if (isset($_SESSION['reader_options']) && $_SESSION['reader_options'] == "dark"){?><meta name="theme-color" content="#3e3f44" />
<?php }?>
    <meta name="Description" content="<?php if ($comic_summary){ echo truncate($comic_summary);}else{ echo "A webcomic directory and bookmark manager.";} ?>" />

    <meta name="twitter:site" content="@<?php echo $TW_HANDLE;?>">
    <meta property="og:title" content="<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "comicinfo.php"){echo $comic_name.' - ';}?>Archive Binge" />
    <meta name="twitter:title" content="<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "comicinfo.php"){echo $comic_name.' - ';}?>Archive Binge" />
    <meta property="og:type" content="website" />
<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "comicinfo.php" && $comic_image){?>
    <meta name="twitter:card" content="summary_large_image">
    <meta property="og:image" content="https://<?php echo $DOMAIN.'/assets/usr_imgs/banners/'.$comic_image.'?v='.rand(); ?>" />
    <meta name="twitter:image" content="https://<?php echo $DOMAIN.'/assets/usr_imgs/banners/'.$comic_image.'?v='.rand(); ?>">
    <!--<meta property="og:image:width" content="600" />-->
    <meta property="og:image:height" content="180" />
<?php }?>
    <meta property="og:description" content="<?php if ($comic_summary){ echo truncate($comic_summary);}else{ echo "A webcomic directory and bookmark manager.";} ?>" />
    <meta name="twitter:description" content="<?php if ($comic_summary){ echo truncate($comic_summary);}else{ echo "A webcomic directory and bookmark manager.";} ?>" />
    <meta property="og:site_name" content="Archive Binge" />

    <title><?php require_once 'includes/title.inc.php';?></title>

    <link rel="dns-prefetch" href="https://www.comicad.net/">
<?php if ($GOOGLE_ANALYTICS_ENABLED){ ?>
  <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $GOOGLE_ANALYTICS_ID; ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', '<?php echo $GOOGLE_ANALYTICS_ID; ?>');
    </script>
<?php } ?>
