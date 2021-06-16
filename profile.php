<?php require_once("includes/header.inc.php");
require_once("includes/formtoken.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/img/bulletproof.php");
require_once("includes/img/func.image-resize.php");

$conn = dbConnect();

if (isset($_GET['user']) && !empty($_GET['user'])){
    $user = mysqli_real_escape_string($conn, $_GET['user']);
}elseif (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])){
    $user = mysqli_real_escape_string($conn, $_SESSION['user_id']);
}else{
    header("Location:/");
}

$whitelist = array('token','username','hide_faves','email','frame_position','dark_mode');
$mode_options = array('light','dark');
$frame_options = array('top','bottom');
$fave_options = array('yes','no');

if(empty($_POST['token'])){
    $newToken = generateFormToken('form1');
}elseif(isset($_POST) && !empty($_POST) && $user == $_SESSION['user_id']){
    foreach ($_POST as $key=>$item) {
        // Check if the value $key (fieldname from $_POST) can be found in the whitelisting array, if not, die with a short message
		if (!in_array($key, $whitelist)) {
			$errors[]="Please use only the fields in the form";
        }
    }
	if (!in_array($_POST['hide_faves'], $fave_options) || !in_array($_POST['frame_position'], $frame_options) || !in_array($_POST['dark_mode'], $mode_options)) {
		$errors[]="Please use only the fields in the form";
	}
    if (!$errors && verifyFormToken('form1')) {
        if (empty($errors)){
            $username = mysqli_real_escape_string($conn, filter_var($_POST['username'],FILTER_SANITIZE_STRING));
            $email = mysqli_real_escape_string($conn, filter_var($_POST['email'],FILTER_SANITIZE_EMAIL));
			$_SESSION['reader_options'] = mysqli_real_escape_string($conn, filter_var($_POST['dark_mode'],FILTER_SANITIZE_STRING));
			$_SESSION['frame_position'] = mysqli_real_escape_string($conn, filter_var($_POST['frame_position'],FILTER_SANITIZE_STRING));
            $_SESSION['hide_faves'] = mysqli_real_escape_string($conn, filter_var($_POST['hide_faves'],FILTER_SANITIZE_STRING));
			$reader_options = array("hide_faves"=>$_SESSION['hide_faves'], "reader_options"=>$_SESSION['reader_options'], "frame_position"=>$_SESSION['frame_position']);
			$reader_options_json = json_encode($reader_options);
            
            $stmt = $conn->prepare("UPDATE users SET user_name = ?, email = ?, site_settings = ? WHERE user_id = ?;");
            $stmt->bind_param('sssi', $username, $email, $reader_options_json, $user);
            $stmt->execute();
        }
    }
    $newToken = generateFormToken('form1');
}

//Get user details
$stmt = $conn->prepare("SELECT user_name, picture, email, site_settings from users where user_id=?");
$stmt->bind_param('i', $user);
$stmt->execute();
$stmt->bind_result($user_name, $picture, $email, $settings);
$stmt->store_result();
$stmt->fetch();

if ($settings) {
    $user_settings = json_decode($settings, True);
    $hide_faves = $user_settings["hide_faves"];
}

//get their owned comics
$stmt = $conn->prepare("select comics.comic_id, comic_name, comic_image from comics left join comic_owners on comics.comic_id = comic_owners.comic_id where user_id=?");
$stmt->bind_param('i', $user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $owned_comics[] = array("id"=>$row["comic_id"],"name"=>$row["comic_name"],"image"=>$row["comic_image"]);
    }
}

if ($_GET['user'] == $_SESSION['user_id']){
    $stmt = $conn->prepare("select comics.comic_id, comic_name, comic_image, bookmark, favorite, sub_type from comics inner join user_subs on comics.comic_id = user_subs.comic_id where user_id=? order by comic_name");
}
else{
    $stmt = $conn->prepare("select comics.comic_id, comic_name, comic_image, bookmark, favorite, sub_type from comics inner join user_subs on comics.comic_id = user_subs.comic_id where user_id=? and sub_type='public' order by comic_name");
}
//get subscribed comics
$stmt->bind_param('i', $user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $subbed_comics[] = array("id"=>$row["comic_id"],
            "name"=>$row["comic_name"],
            "image"=>$row["comic_image"],
            "bookmark"=>$row["bookmark"],
            "favorite"=>$row["favorite"],
            "sub_type"=>$row["sub_type"]);
    }
}

$stmt->close();
$conn->close();
require_once("includes/css.inc.php");

echo "<style>
";
if($owned_comics){
    foreach ($owned_comics as $comic){
        if (!empty($comic["image"])){
            echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
        }
    }
}
foreach ($subbed_comics as $comic){
    if (!empty($comic["image"])){
        echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
    }
}
echo "</style>";
?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
<?php
//error block
if(is_array($errors) && count($errors) > 0) {echo'<div class="row">
<div class="col-md-6 col-md-offset-3">';
    if(count($errors > 1)) {
        foreach ($errors as $error){
            echo'    <div class="alert alert-danger" role="alert">Error: '.$error.'</div>';
        }
    }else{
        echo'    <div class="alert alert-danger" role="alert">Error: '.$error.'</div>';
    }
echo '</div>
</div>';
}?>
    
<h1><?php echo $user_name; ?></h1>

<?php if ($_GET['user'] == $_SESSION['user_id']){ ?><button type="button" class="btn btn-default" onclick="myFunction()">Edit Profile and Settings</button>

<br /><br />
<form method="post" id="profileform" style="display:none;">
  <div class="form-group">
    <label for="username">Username:</label>
    <input type="text" class="form-control" name="username"<?php if($user_name){echo ' value="'.$user_name.'"';}?> required>
  </div>
  <div class="form-group">
    <label for="email">Email:</label>
    <input type="email" class="form-control" name="email"<?php if($email){echo ' value="'.$email.'"';}?>>
  </div>
  <div class="form-group">
    <label for "dark_mode">Light/Dark Mode:</label><br/>
	<label class="radio-inline"><input type="radio" name="dark_mode" value="light"<?php if($_SESSION['reader_options'] == "light" || !isset($_SESSION['reader_options'])){echo ' checked="checked"';} ?>>Light</label>
    <label class="radio-inline"><input type="radio" name="dark_mode" value="dark"<?php if($_SESSION['reader_options'] == "dark"){echo ' checked="checked"';} ?>>Dark</label>
  </div>
  <div class="form-group">
    <label for "frame_position">Reader Frame Position</label><br/>
	<label class="radio-inline"><input type="radio" name="frame_position" value="top"<?php if($_SESSION['frame_position'] == "top" || !isset($_SESSION['frame_position'])){echo ' checked="checked"';} ?>>Top</label>
    <label class="radio-inline"><input type="radio" name="frame_position" value="bottom"<?php if($_SESSION['frame_position'] == "bottom"){echo ' checked="checked"';} ?>>Bottom</label>
  </div>
  <div class="form-group">
    <label for "hide_faves">Hide Favorites</label><br/>
    <label class="radio-inline"><input type="radio" name="hide_faves" value="yes"<?php if($_SESSION['hide_faves'] == "yes"){echo ' checked="checked"';} ?>>Yes</label>
    <label class="radio-inline"><input type="radio" name="hide_faves" value="no"<?php if($_SESSION['hide_faves'] == "no" || !isset($_SESSION['hide_faves'])){echo ' checked="checked"';} ?>>No</label>
    <p class="help-block">Hide which comics are marked as favorites on your profile.</p>
  </div>

  <input type="hidden" name="token" value="<?php echo $newToken; ?>">
  <button type="submit" class="btn btn-default">Submit</button>
</form>

<hr /><?php } ?>


<div class="row">
    <div class="col-sm-2">
        <?php if (isset($picture)){ ?>
        <img class="img-responsive" alt="User avatar" src="/assets/usr_imgs/avatars/<?php echo $picture; ?>" />
        <?php } ?>
    </div>
    <div class="col-sm-10">
        <?php if ($_GET['user'] == $_SESSION['user_id']){ ?>
        <h3 class="media-heading">Site Settings</h2>
        <dl class="dl-horizontal">
            <?php if (!empty($email)){?><dt>Email</dt>
            <dd><?php echo $email; ?></dd>

            <dt>Update Digest</dt>
            <dd><!--<a href="/email">-->Never<!--</a>--></dd>
			
			<dt>Light/Dark Mode</dt>
			<dd><?php if ($_SESSION['reader_options']){echo ucfirst($_SESSION['reader_options']);}else{echo "Light";} ?></dd>
			
			<dt>Reader Frame Position</dt>
			<dd><?php if ($_SESSION['frame_position']){echo ucfirst($_SESSION['frame_position']);}else{echo "Top";} ?></dd>

            <dt>Hide Favorites</dt>
            <dd><?php if ($_SESSION['hide_faves']){echo ucfirst($_SESSION['hide_faves']);}else{echo "No";} ?></dd>

            <br /><?php } ?>
            <!--
            <h3 class="media-heading">Signin Accounts</h3>
            <dt>Google</dt>
            <dd><span class="btn btn-google">Connected</span></dd>
            
            <dt>Facebook</dt>
            <dd><span class="btn btn-facebook">Connected</span></dd>
            
            <dt>Twitter</dt>
            <dd><span class="btn btn-twitter">Connected</span></dd>-->
        </dl>
        <?php } ?>
    </div>

</div>

<?php if (isset($owned_comics) && !empty($owned_comics)){?>
<hr />


<h2>Comics Claimed</h2>

<div class="row">
    <?php foreach ($owned_comics as $comic){
        echo '<div class="col-md-6"><a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
        if (!empty($comic["image"])){
            echo ' comic-bar comic'.$comic["id"];
        }
        echo '"><strong>'.$comic['name'].'</strong></a></div>';
    }
    ?>
</div>
<?php } ?>

<h2>Reading List</h2>

<?php if (isset($subbed_comics) && !empty($subbed_comics)){?>
<div class="row">
    <?php foreach ($subbed_comics as $comic){
        echo '<div class="col-md-6"><a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
        if (!empty($comic["image"])){
            echo ' comic-bar comic'.$comic["id"];
        }
        echo '"><strong>'.$comic['name'].'</strong>';
        if ($comic["favorite"] == 1 && $hide_faves == "no"){echo '<span class="pull-right glyphicon glyphicon-star-empty" style="z-index:2;" title="Favorite Comic"></span>';}
        echo '</a></div>';
    }
    ?>
</div>
<?php }else{echo $user_name." isn't subscribed to any comics."; } ?>

    </div><!--panel body-->
</div> <!-- /container -->
	<?php require_once 'includes/footer.inc.php';?>
    <script>function myFunction() {
    var x = document.getElementById("profileform");
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}</script>
  </body>
</html>
