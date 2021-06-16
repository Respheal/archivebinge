<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <?php if (isset($_SESSION['reader_options']) && $_SESSION['reader_options'] == "dark"){?><meta name="theme-color" content="#3e3f44" />
<?php }?>
    <meta name="Description" content="<?php if ($comic_summary){ echo truncate($comic_summary);}else{ echo "A webcomic directory and bookmark manager.";} ?>" />

    <meta name="twitter:site" content="@HANDLE">
    <meta property="og:title" content="<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "comicinfo.php"){echo $comic_name.' - ';}?>Archive Binge" />
    <meta name="twitter:title" content="<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "comicinfo.php"){echo $comic_name.' - ';}?>Archive Binge" />
    <meta property="og:type" content="website" />
<?php if (basename($_SERVER['SCRIPT_FILENAME']) == "comicinfo.php" && $comic_image){?>
    <meta name="twitter:card" content="summary_large_image">
    <meta property="og:image" content="https://DOMAIN.com<?php echo '/assets/usr_imgs/banners/'.$comic_image.'?v='.rand(); ?>" />
    <meta name="twitter:image" content="https://DOMAIN.COM<?php echo '/assets/usr_imgs/banners/'.$comic_image.'?v='.rand(); ?>">
    <!--<meta property="og:image:width" content="600" />-->
    <meta property="og:image:height" content="180" />
<?php }?>
    <meta property="og:description" content="<?php if ($comic_summary){ echo truncate($comic_summary);}else{ echo "A webcomic directory and bookmark manager.";} ?>" />
    <meta name="twitter:description" content="<?php if ($comic_summary){ echo truncate($comic_summary);}else{ echo "A webcomic directory and bookmark manager.";} ?>" />
    <meta property="og:site_name" content="Archive Binge" />

    <title><?php require_once 'includes/title.inc.php';?></title>

    <link rel="dns-prefetch" href="https://www.comicad.net/">
