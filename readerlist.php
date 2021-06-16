<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
$conn = dbConnect();

$comic = mysqli_real_escape_string($conn, $_GET['comic']);


$stmt = $conn->prepare("select users.user_id, user_name, picture, bookmark from users inner join user_subs on users.user_id = user_subs.user_id where sub_type='public' and comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    $i=0;
    while($row = $result->fetch_assoc()) {
        $readers[$i]['id'] = $row["user_id"];
        $readers[$i]['username'] = $row["user_name"];
        $readers[$i]['picture'] = $row["picture"];
        $readers[$i]['bookmark'] = $row["bookmark"];
        $i++;
    }
} else {
    $stmt->close();
    $conn->close();
    $goto = "/comic/".$_GET['comic'];
    header('Location: '.$goto);
}

//Is the current user subscribed? Favorites?
$stmt = $conn->prepare("SELECT comic_name from comics WHERE comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$stmt->bind_result($comic_name);
$stmt->store_result();
$stmt->fetch();

$stmt->close();
$conn->close();
?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
    <h1><?php echo $comic_name; ?> Readers</h1>
    <small><a href="/comic/<?php echo $comic; ?>"><span class="glyphicon glyphicon-chevron-left"></span> Back to <?php echo $comic_name; ?></a></small>
    <hr />
    <div class="row">
        <?php foreach ($readers as $reader){ ?> 
        <div class="col-md-4">
            <div class="panel panel-default">
                    <div class="media-left">
                        <?php if ($reader['picture']){echo '<img class="media-object" src="/assets/usr_imgs/avatars/'.$reader['picture'].'" height="75" alt="'.$reader['username'].'">';} ?>
                    </div>
                    <div class="media-body" style="padding:15px;">
                        <h4 class="media-heading"><a href="/profile/<?php echo $reader['id']; ?>"><?php echo $reader['username']; ?></a></h4>
                        Bookmark: <?php echo $reader['bookmark']+1; ?>
                    </div>
            </div>
        </div>
        <?php } ?>
    </div>
    </div><!--panel body-->
</div> <!-- /container -->
	
	<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
