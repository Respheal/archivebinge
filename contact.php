<?php
require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
?>

  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

    <div class="container panel panel-default">
    <div class="panel-body">

<div class="row">

<div class="col-md-12 text-justify">

<h1>Contact</h1>

<h2>Email</h2>

<p>Issues with comic listings, your account, site problems: <a href="mailto:<?php echo $SUPPORT_EMAIL; ?>"><?php echo $SUPPORT_EMAIL; ?></a></p>
<p>Address for feedback, suggestions, or bug reports: <a href="mailto:<?php echo $FEEDBACK_EMAIL; ?>"><?php echo $FEEDBACK_EMAIL; ?></a></p>
<p>Abuse complaints for listings that violate the <a href="/tos">Terms of Service</a>: <a href="mailto:<?php echo $ABUSE_EMAIL; ?>"><?php echo $ABUSE_EMAIL; ?></a><p>

<h2>Social Media</h2>

<p>Twitter: <a href="https://twitter.com/HANDLE">@<?php echo $TW_HANDLE; ?></a></p>

<h2>Other</h2>

<p></p>

</div>

</div><!--row-->
</div><!--panel body-->


    </div> <!-- /container -->

	<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
