<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");

$conn = dbConnect();

$comic = mysqli_real_escape_string($conn, $_GET['comic']);
$userid = mysqli_real_escape_string($conn, $_SESSION['user_id']);

$stmt = $conn->prepare("SELECT comic_id, comic_name, comic_pages from comics where comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$stmt->bind_result($comic_id, $comic_name, $pages);
$stmt->store_result();
$stmt->fetch();

$comic_pages = json_decode(str_replace("'", '"',$pages));
$page = $comic_pages[0];

$stmt = $conn->prepare("SELECT user_name, claim_code from users where user_id=?");
$stmt->bind_param('i', $userid);
$stmt->execute();
$stmt->bind_result($username, $claim);
$stmt->store_result();
$stmt->fetch();

if (isset($_POST) && !empty($_POST)){
    if ($_POST['claim'] == 'html'){
        chdir('crawler');
        $cmd = './crawlerenv/bin/scrapy crawl claim -a starturl='.$page;
        exec($cmd, $output);
        if ($output[0] == $claim){
            $match = True;
        }
    }
    if ($_POST['claim'] == 'dns'){
        $domain = str_replace("www.", "", parse_url($comic_pages[0], PHP_URL_HOST));
        $dns = dns_get_record($domain, DNS_TXT);
        foreach ($dns as $zone){
            if ($zone['txt'] == $claim){
                $match = True;
            }
        }
    }
    if ($match){
        $stmt = $conn->prepare("INSERT INTO comic_owners (comic_id, user_id) VALUES (?, ?);");
        $stmt->bind_param('ii', $comic, $userid);
        $stmt->execute();
        $message = $comic_name." has been claimed successfully by ".$username;
    }
}

?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
<?php if ($message){ ?>
<div class="alert alert-success" role="alert"><p><?php echo $message; ?></p></div>
<?php }?>
<h1>Claim Comic - <?php echo $comic_name; ?></h1>

<p><small><a href="/comic/<?php echo $comic_id; ?>"><span class="glyphicon glyphicon-chevron-left"></span> Back to <?php echo $comic_name; ?></a></small></p>

<p>Claiming a comic on Archive Binge locks the entry so only you or other owners can edit a comic. You may claim a comic by using any of the following three methods. You may remove the code/comment/record once you have successfully claimed the comic.</p>

<h2>HTML</h2>

<p>Copy the tag below and paste it into your comic site's <strong>first</strong> page. Usually this can be done by placing it in the "Overall Layout" or header.php. The code should go in the <code>&lt;head&gt;...&lt;/head&gt;</code> section.
<pre>&lt;meta name="archive-binge" content="<?php echo $claim; ?>" /&gt;</pre>
<p>Confirm that the code is in place:</p>
<form method="post" action="">
<button type="submit" class="btn btn-default" name="claim" value="html">Confirm</button>
</form>

<h2>DNS</h2>

<p>Create a TXT record in your DNS zone editor with the following value: <code><?php echo $claim; ?></code></p>
<p>Once you have done that, confirm by clicking this button:</p>
<p><form method="post" action="">
<button type="submit" class="btn btn-default" name="claim" value="dns">Confirm</button>
</form></p>

<div class="alert alert-info" role="alert"><p><strong>DNS changes may take up to 24 hours to update.</strong> Please see your nameserver provider (often your host or where you bought the domain) for details on editing your DNS information.</p></div>

<h2>Author Comment</h2>

<p>If you cannot edit the DNS or the HTML or your webcomic site, leave the following code in one of the <strong>author comments</strong> of your comic. Send <strong>a link to the page that contains the code</strong> to <a href="mailto:support@DOMAIN.COM&subject=<?php echo $comicid ?>+<?php echo $username; ?>%20Claim">support@DOMAIN.COM</a> and we can set your user as the owner of <a href="/comic/<?php echo $comic_id; ?>"><em><?php echo $comic_name; ?></em></a>. Please note that the code alone is not sufficient as we require some form of proof that you are the actual owner of the comic (such as the ability to make author comments or edit the comic site). This method is manual and may take up to 72 hours.</p>

<pre><?php echo $claim; ?></pre>
    </div><!--panel body-->
</div> <!-- /container -->

	<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
