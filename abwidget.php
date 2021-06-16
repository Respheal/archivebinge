<?php
header('Content-Type: application/javascript');

require_once("includes/conf.inc.php");
$conn = dbConnect();
$imgpath = "https://DOMAIN.COM/assets/images/hotlink-ok/";

$comic = mysqli_real_escape_string($conn, filter_var($_GET['comic'], FILTER_SANITIZE_NUMBER_INT));
$info = filter_var($_GET['info-only'], FILTER_SANITIZE_STRING);
$size = filter_var($_GET['size'], FILTER_SANITIZE_STRING);
if ($size == "rectangle"){ $img=$imgpath."rectangle.png"; $height=60; $width=117;
}elseif ($size=="halfbanner"){ $img=$imgpath."halfbanner.png"; $height=60; $width=234;
}elseif ($size=="square"){ $img=$imgpath."square.png"; $height=125; $width=125;
}elseif ($size=="tiny"){ $img=$imgpath."tiny.png"; $height=31; $width=88;
}else{ $img=$imgpath."button.png"; $height=30; $width=117;}

?>
comic_id = <?php echo $comic; ?>;

<?php if (!$info) {
    $stmt = $conn->prepare("SELECT comic_pages from comics where comic_id=?");
    $stmt->bind_param('i', $comic);
    $stmt->execute();
    $stmt->bind_result($comic_pages);
    $stmt->store_result();
    $stmt->fetch();

    $stmt->close();

    echo "pages = "; print_r($comic_pages); ?>;

url = location.href;
pageindex = pages.indexOf(url);

var css = document.createElement('style');
css.type = 'text/css';
style = ".ab_widget { background-image: url('<?php echo $img; ?>'); background-size: cover; display: inline-block; height: <?php echo $height; ?>px; width: <?php echo $width; ?>px; }";

if (css.styleSheet)
	css.styleSheet.cssText = style;
else
	css.appendChild(document.createTextNode(style));

document.getElementsByTagName("head")[0].appendChild(css);

if (pageindex < 0) {
    document.write("<a href='https://DOMAIN.COM/comic/" + comic_id + "' title='Read this comic on Archive Binge'><div class='ab_widget ab_override'></div></a>");
} else {
    document.write("<a href='http://DOMAIN.COM/reader/" + comic_id + "/" + pageindex + "' title='Read this comic on Archive Binge'><div class='ab_widget ab_override'></div></a>");}

<?php } else { ?>
document.write("<a href='https://DOMAIN.COM/comic/" + comic_id + "' title='Read this comic on Archive Binge'><div class='ab_widget ab_override'></div></a>");
<?php } $conn->close(); ?>
