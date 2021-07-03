<?php require_once 'includes/conf.inc.php';?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (isset($_SESSION['reader_options']) && $_SESSION['reader_options'] == "dark"){?><meta name="theme-color" content="#3e3f44" />
<?php }?>
<meta property="og:title" content="Archive Binge">
<meta property="og:description" content="">
<meta property="og:image" content="">
<meta property="og:url" content="https://<?php echo $DOMAIN; ?>/">
<meta name="twitter:card" content="summary_large_image">
<meta property="og:site_name" content="Archive Binge">
<meta name="twitter:image:alt" content="">
<meta name="twitter:site" content="@HANDLE">

    <title><?php require_once 'includes/title.inc.php';?></title>
