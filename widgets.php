<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");

$conn = dbConnect();

$comic = mysqli_real_escape_string($conn, $_GET['comic']);

$stmt = $conn->prepare("SELECT comic_id, comic_name from comics where comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$stmt->bind_result($comic_id, $comic_name);
$stmt->store_result();
$stmt->fetch();
?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
<h1>Widgets - <?php echo $comic_name; ?></h1>

<p><small><a href="/comic/<?php echo $comic_id; ?>"><span class="glyphicon glyphicon-chevron-left"></span> Back to <?php echo $comic_name; ?></a></small></p>

<p>These widgets will link to the entry for <em><?php echo $comic_name; ?></em> on Archive Binge. If the standard widget is placed on a comic page with a URL that matches a page as listed on AB, the widget will link directly to that page in the Reader. Alternatively, the widget can just link directly to the info page. You can find all of the widget options below:</p>

<h2>Standard</h2>

<p>If placed on a comic page, these widgets will link to that same page in the Archive Binge reader, letting a reader bookmark their place <em>if</em> the URL of the comic page matches the Archive Binge listing exactly. If the URL does not match any pages in the Archive Binge listing, it will link to the <a href="/comic/<?php echo $comic_id; ?>">Info Page</a> instead.</p>

<div class="panel panel-default">
    <div class="panel-heading">Button (117x30px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&size=button"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&size=button"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Rectangle (117x60px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&size=rectangle"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&size=rectangle"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Half Banner (234x60px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&size=halfbanner"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&size=halfbanner"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Square (125x125px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&size=square"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&size=square"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>

<h2>Info Page Only</h2>

<p>These widgets will link directly to <a href="/comic/<?php echo $comic_id; ?>">http://DOMAIN.com/comic/<?php echo $comic_id; ?></a> regardless of where they are placed.</a>

<div class="panel panel-default">
    <div class="panel-heading">Button (117x30px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&info=y&size=button"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&info=y&size=button"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Rectangle (117x60px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&info=y&size=rectangle"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&info=y&size=rectangle"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Half Banner (234x60px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&info=y&size=halfbanner"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&info=y&size=halfbanner"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Square (125x125px)</div>
    <div class="panel-body">
        <p><script src="/abwidget2.php?comic=<?php echo $comic_id; ?>&info=y&size=square"></script></p>
        <p><pre>&lt;script src="https://DOMAIN.com/abwidget.php?comic=<?php echo $comic_id; ?>&info=y&size=square"&gt;&lt;/script&gt;</pre></p>
    </div>
</div>


<h2>CSS</h2>

<p>You can modify the appearance of the banners with CSS on the .ab_widget.ab_override class. For example, to make the button grayscale, add this to your site CSS:</p>

<p><pre>.ab_widget.ab_override{filter: grayscale(100%);}</pre>

<p>You can also modify the image the widget uses in the same manner:</p>

<p><pre>.ab_widget.ab_override{background-image: url('yourimage.png');}</pre>

    </div><!--panel body-->
</div> <!-- /container -->

	<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
