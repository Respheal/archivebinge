<?php
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once("includes/header.inc.php");
require_once("includes/formtoken.inc.php");

if (empty($_SESSION['user_id'])){
    //if not logged in, immediately kicks back to comic info
    $newComicURL = "Location: /claim/".$_GET['comic'];
    header($newComicURL);
}

$whitelist = array('token', 'comic_pages');

$conn = dbConnect();
$comic = mysqli_real_escape_string($conn, $_GET['comic']);

if(empty($_POST['token'])){
    $newToken = generateFormToken('form1');
}elseif(isset($_POST) && !empty($_POST)){
    foreach ($_POST as $key=>$item) {
        // Check if the value $key (fieldname from $_POST) can be found in the whitelisting array, if not, die with a short message
		if (!in_array($key, $whitelist)) {
			$errors[]="Please use only the fields in the form";
        }
    }
    
    if (verifyFormToken('form1')) {
        //generate new token for the next submission
        $newToken = generateFormToken('form1');
        
        if (empty($errors)){
            //everything looks kosher so far. Proceed with prepping data for the db
            $pages = mysqli_real_escape_string($conn, filter_var($_POST['comic_pages'],FILTER_SANITIZE_STRING));
            $comic_pages = "['".str_replace('\r\n', "', '", $pages)."']";
            //parse data
            //echo $comic_pages;
        }

        //prep MySQL
        mysqli_autocommit($conn, false);

        $stmt = $conn->prepare("UPDATE comics SET comic_pages = ? where comic_id = ?;");
        $stmt->bind_param('si', $comic_pages, $comic);
        if ($stmt->execute()){
            //successfull query, attempt commit
            if(!$conn->commit()){$errors[] = "Commit failed".$stmt->error;}
        } else{
            $conn->rollback();$errors[] = "Transaction failed".$stmt->error;
        }
        mysqli_autocommit($conn, true);

        //$newComicURL = "Location: /comic/".$_GET['comic'];
        //header($newComicURL);
    }else {$errors[]="Incorrect access detected.";}
}

//get comic owners
$stmt = $conn->prepare("select users.user_id, user_name from users left join comic_owners on users.user_id = comic_owners.user_id where comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    $i=0;
    while($row = $result->fetch_assoc()) {
        if ($row["user_id"] == $_SESSION['user_id']){$user_is_owner = True;}
        $comic_owners[$i]['id'] = $row["user_id"];
        $comic_owners[$i]['username'] = $row["user_name"];
        $i++;
    }
}

if ($comic_owners){
//if comic has owners
    foreach ($comic_owners as $owners){
        //if the comic has owners, only allow progress if logged in user is one of them
        if ($_SESSION['user_id'] == $owners['id']){
            $is_owner = True;
            $is_actual_owner = True;
            break;
        }
    }
}

if (!$is_owner && $_SESSION['user_id'] != 1){
    //user doesn't have edit access, kick them out
    $conn->close();
    $newComicURL = "Location: /claim/".$comic;
    header($newComicURL);
}

//user is permitted to be here, so let's grab info from the db
$stmt = $conn->prepare("SELECT * from comics where comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_id = $row["comic_id"];
        if (!$comic_name) {$comic_name = $row["comic_name"];}
        $comic_pages = json_decode(str_replace("'", '"',$row["comic_pages"]));
    }
} else {
    $errors[] = "Comic not found. Try the search bar?";
}

$stmt = $conn->prepare("SELECT count(comic_id) as total FROM user_subs WHERE comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
// output data of each row
    $comic_readers = $result->fetch_assoc();
}
$stmt->close();
$conn->close();

$newToken = generateFormToken('form1');
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
?>
  </head>
  <body>

	<?php require_once 'includes/menu.inc.php';?>

    <div class="container panel panel-default">
<div class="panel-body">
<?php
//error block
if(count($errors) > 0) {echo'<div class="row">
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
<div class="row">
    <div class="col-sm-12 col-md-8 col-lg-9">

        <h1>Editing <a href="/comic/<?php echo $comic_id;?>"><?php echo $comic_name;?></a> Pages</h1>
        <a href="">Edit Crawler</a>

        <form id="editform" method="post" action="/pages/<?php echo $comic_id;?>" enctype="multipart/form-data">

            <div class="form-group">
                <p class="help-block">Add or remove pages from the comic, one per row. Please note if the comic's layout has changed, you may need to update the crawler in the link above.</p>
                <textarea id="comic_pages" class="form-control" rows="20" name="comic_pages"><?php echo join("\r\n",$comic_pages); ?></textarea>
            </div>

            <input type="hidden" name="token" value="<?php echo $newToken; ?>">
            <button type="submit" class="btn btn-default">Submit</button>
        </form>

    </div><!--main col-->
    <div class="col-sm-12 col-md-4 col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading">Comic Info</div>
            <div class="panel-body">
                
                Readers: <?php if ($comic_readers['total'] > 0){ echo '<a href="/subscribers/'.$comic_id.'">'.$comic_readers['total'].'</a>';}else{echo "0";} ?><br />
                Pages: <?php echo count($comic_pages)?><br />
                <a href="/edit/<?php echo $comic_id; ?>">[Edit Comic Info]</a>
                    
            </div>
        </div><!--panel-->
    </div><!--col-->

</div><!--row-->
</div><!--panel body-->
</div> <!-- /container -->
		<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
