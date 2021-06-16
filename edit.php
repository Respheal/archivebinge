<?php
require_once("includes/header.inc.php");
require_once("includes/formtoken.inc.php");
require_once("includes/img/bulletproof.php");
require_once("includes/img/func.image-resize.php");

if (empty($_SESSION['user_id'])){
    //if not logged in, immediately kicks back to comic info
    $newComicURL = "Location: /claim/".$_GET['comic'];
    header($newComicURL);
}

$whitelist = array('MAX_FILE_SIZE','comic_pages','currentbanner','token','comic_name','comic_image','comic_rss','comic_summary','comic_status','socialmedia','mirrors','rating_enabled','support','comic_tags','adult','violence','language','nudesex','content','comic_warnings','day');
$update_array = array('Ongoing', 'On Hiatus', 'Completed', 'Cancelled', 'Deleted');

$conn = dbConnect();
$comic = mysqli_real_escape_string($conn, $_GET['comic']);

//Get warning list: $comic_warnings[warning_name, warning_category]
$sql = "SELECT warning_name,warning_category from warnings order by warning_name asc";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $possible_warnings[] = array($row["warning_name"],$row["warning_category"]);
    }
} else {
    echo "0 results";
}

//Get tag list: $comic_tags[tag_name]
if ($_SESSION['user_id'] == 1){
    $sql = "SELECT tag_name from tags ORDER BY tag_name ASC";
}else{
    $sql = "SELECT tag_name from tags where restricted=0 ORDER BY tag_name ASC";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $possible_tags[] = $row["tag_name"];
    }
} else {
    echo "0 results";
}

if(empty($_POST['token'])){
    $newToken = generateFormToken('form1');
}elseif(isset($_POST) && !empty($_POST)){
    foreach ($_POST as $key=>$item) {
        // Check if the value $key (fieldname from $_POST) can be found in the whitelisting array, if not, die with a short message
		if (!in_array($key, $whitelist)) {
			$errors[]="Please use only the fields in the form";
        }
    }
    
    if (!in_array($_POST['comic_status'], $update_array)) {
            $errors[]="Please use only the fields in the form";
        }
    
    if (verifyFormToken('form1')) {
        //generate new token for the next submission
        $newToken = generateFormToken('form1');
        
        if (empty($errors)){
            //everything looks kosher so far. Proceed with prepping data for the db
            
            //comic image
            if ($_FILES['comic_image']['error'] === 0){
                $image = new Bulletproof\Image($_FILES);

                // define the min/max image upload size (size in bytes) 
                $image->setSize(1, 400000); //1B to 400kB

                // define allowed mime types to upload
                $image->setMime(array('jpeg', 'gif', 'png'));

                // pass name (and optional chmod) to create folder for storage
                $image->setLocation('assets/usr_imgs/banners', 0644);

                if($image["comic_image"]){
                    $upload = $image->upload(); 
                    
                    if($upload){
                        $img_name = $image->getName().'.'.$image->getMime(); // uploads/cat.gif
                        if ($currentbanner) {
                            unlink("assets/usr_imgs/banners/".$currentbanner);
                        }
                    }else{
                        $errors[] = $image["error"];
                    }
                }
            }else{
                $img_name = mysqli_real_escape_string($conn, filter_var($_POST['currentbanner'],FILTER_SANITIZE_STRING));
            }
                        
            //parse data
            $comic_name = mysqli_real_escape_string($conn, filter_var($_POST['comic_name'],FILTER_SANITIZE_STRING));
            $comic_summary = mysqli_real_escape_string($conn, filter_var($_POST['comic_summary'],FILTER_SANITIZE_STRING));
            if ($_POST['comic_pages'] || $_SESSION['user_id'] == 1){
                $pages = mysqli_real_escape_string($conn, filter_var($_POST['comic_pages'],FILTER_SANITIZE_STRING));
                $comic_pages = "['".str_replace('\r\n', "', '", $pages)."']";
            }
            $comic_status = mysqli_real_escape_string($conn, filter_var($_POST['comic_status'],FILTER_SANITIZE_STRING));
            
            $comic_rss = mysqli_real_escape_string($conn, filter_var($_POST['comic_rss'],FILTER_SANITIZE_URL));
            
            if($_POST['rating_enabled'] && $_POST['rating_enabled'] == 1){
                $rating_enabled = 1;
            }else{$rating_enabled = 0;}
            
            foreach ($_POST['socialmedia'] as $smedia){
                if ($smedia){
                    $comic_smedia[] = mysqli_real_escape_string($conn, filter_var($smedia,FILTER_SANITIZE_URL));
                }
            }
            if ($comic_smedia){$comic_smedia_sql = implode(",",$comic_smedia);}
            else{$comic_smedia = "";$comic_smedia_sql="";}
            
            foreach ($_POST['mirrors'] as $mirror){
                if ($mirror){
                    $comic_mirrors[] = mysqli_real_escape_string($conn, filter_var($mirror,FILTER_SANITIZE_URL));
                }
            }
            if ($comic_mirrors){$comic_mirrors_sql = implode(",",$comic_mirrors);}
            else{$comic_mirrors = "";$comic_mirrors_sql="";}
            
            foreach ($_POST['support'] as $support){
                if ($support){
                    $comic_supports[] = mysqli_real_escape_string($conn, filter_var($support,FILTER_SANITIZE_URL));
                }
            }
            if ($comic_supports){$comic_supports_sql = implode(",",$comic_supports);}
            else{$comic_supports = "";$comic_supports_sql="";}
            
            if($_POST['day']){
                foreach ($_POST['day'] as $day){
                    if ($day){
                        $comic_freq[] = mysqli_real_escape_string($conn, filter_var($day,FILTER_SANITIZE_URL));
                    }
                }
                if ($comic_freq){$comic_freq = implode(",",$comic_freq);}
                else{$comic_freq = "";}
            }
            
            $adult = filter_var($_POST['adult'], FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0, 
                    'max_range' => 3
                    )
                )
            );
            $violence = filter_var($_POST['violence'], FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0, 
                    'max_range' => 3
                    )
                )
            );
            $language = filter_var($_POST['language'], FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0, 
                    'max_range' => 3
                    )
                )
            );
            $nudesex = filter_var($_POST['nudesex'], FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0, 
                    'max_range' => 3
                    )
                )
            );
            $content = filter_var($_POST['content'], FILTER_VALIDATE_INT, array(
                'options' => array(
                    'min_range' => 0, 
                    'max_range' => 3
                    )
                )
            );

            $comic_warning_levels_sql = $adult.','.$violence.','.$language.','.$nudesex.','.$content;
            $comic_warning_levels = array($adult,$violence,$language,$nudesex,$content);

            //get the tags from the db
            if ($_SESSION['user_id'] == 1){
                $stmt = $conn->prepare("select tag_name from tags inner join comic_tags on tags.tag_id = comic_tags.tag_id where comic_id=? order by tag_name limit 100");
            }else{
                $stmt = $conn->prepare("select tag_name from tags inner join comic_tags on tags.tag_id = comic_tags.tag_id where comic_id=? and restricted=0 order by tag_name limit 100");
            }
            $stmt->bind_param('i', $comic);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
            // output data of each row
                while($row = $result->fetch_assoc()) {
                    $used_comic_tags[] = $row["tag_name"];
                }
            }

            if (!empty($_POST['comic_tags'])){
                foreach ($_POST['comic_tags'] as $tag){
                    $form_tags[] = mysqli_real_escape_string($conn, filter_var($tag,FILTER_SANITIZE_STRING));
                }
            }else {$form_tags = []; }
            
            if ($used_comic_tags){
                //if tag in used_comic_tags but not form_tags, delete it
                $delete_tags = array_diff($used_comic_tags,$form_tags);
                        
                //if tag in form_tags but not used_comic_tags, add it
                $add_tags = array_diff($form_tags,$used_comic_tags);
            }elseif($form_tags){
                $add_tags = $form_tags;
            }
            
            //warnings from the db
            $stmt = $conn->prepare("select warning_name from warnings inner join comic_warnings on warnings.warning_id = comic_warnings.warning_id where comic_id=?");
            $stmt->bind_param('i', $comic);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $used_comic_warnings[] = $row["warning_name"];
                }
            }
            
            if (!empty($_POST['comic_warnings'])){
                foreach ($_POST['comic_warnings'] as $warning){
                    $form_warnings[] = mysqli_real_escape_string($conn, filter_var($warning,FILTER_SANITIZE_STRING));
                }
            }else {$form_warnings = [];}
            
            if ($used_comic_warnings){
                //if tag in used_comic_tags but not form_tags, delete it
                $delete_warnings = array_diff($used_comic_warnings,$form_warnings);

                //if tag in form_tags but not used_comic_tags, add it
                $add_warnings = array_diff($form_warnings,$used_comic_warnings);
            }elseif($form_warnings){
                $add_warnings = $form_warnings;
            }

            //prep MySQL
            mysqli_autocommit($conn, false);

            if ($_POST['comic_pages'] || $_SESSION['user_id'] == 1){
                $stmt = $conn->prepare("UPDATE comics SET
                    comic_name = ?,
                    comic_image = ?,
                    comic_rss = ?,
                    comic_summary = ?,
                    rating_enabled = ?,
                    comic_status = ?,
                    comic_freq = ?,
                    comic_smedia = ?,
                    comic_mirrors = ?,
                    comic_support = ?,
                    comic_warning = ?,
                    comic_pages = ?,
                    last_edit = ?
                    WHERE comic_id = ?;");
                $stmt->bind_param('ssssisssssssii', $comic_name, $img_name, $comic_rss, $comic_summary, $rating_enabled, $comic_status, $comic_freq, $comic_smedia_sql, $comic_mirrors_sql, $comic_supports_sql, $comic_warning_levels_sql, $comic_pages, $_SESSION['user_id'], $comic);
            }
            else{
                $stmt = $conn->prepare("UPDATE comics SET
                    comic_name = ?,
                    comic_image = ?,
                    comic_rss = ?,
                    comic_summary = ?,
                    rating_enabled = ?,
                    comic_status = ?,
                    comic_freq = ?,
                    comic_smedia = ?,
                    comic_mirrors = ?,
                    comic_support = ?,
                    comic_warning = ?,
                    last_edit = ?
                    WHERE comic_id = ?;");
                $stmt->bind_param('ssssissssssii', $comic_name, $img_name, $comic_rss, $comic_summary, $rating_enabled, $comic_status, $comic_freq, $comic_smedia_sql, $comic_mirrors_sql, $comic_supports_sql, $comic_warning_levels_sql, $_SESSION['user_id'], $comic);
            }
            if ($stmt->execute()){
                //successfull query, attempt commit
                if(!$conn->commit()){$errors[] = "Commit failed".$stmt->error;}
            }else{$conn->rollback();$errors[] = "Transaction failed".$stmt->error;}
            
            //update tags
            if(!empty($add_tags)){
                $stmt = $conn->prepare("INSERT into comic_tags (comic_id, tag_id) values (?, (select tag_id from tags where tag_name=?));");
                $stmt->bind_param("is", $comic, $insert_tag);
                $conn->query("START TRANSACTION");
                foreach ($add_tags as $insert_tag) {
                    $stmt->execute();
                }
                $stmt->close();
                $conn->query("COMMIT");
            }
            
            if(!empty($delete_tags)){
                $stmt = $conn->prepare("delete from comic_tags where comic_id = ? and tag_id = (select tag_id from tags where tag_name=?)");
                $stmt->bind_param("is", $comic, $delete_tag);
                $conn->query("START TRANSACTION");
                foreach ($delete_tags as $delete_tag) {
                    $stmt->execute();
                }
                $stmt->close();
                $conn->query("COMMIT");
            }
            
            //update warnings
            if(!empty($add_warnings)){
                $stmt = $conn->prepare("INSERT into comic_warnings (comic_id, warning_id) values (?, (select warning_id from warnings where warning_name=?));");
                $stmt->bind_param("is", $comic, $insert_warning);
                $conn->query("START TRANSACTION");
                foreach ($add_warnings as $insert_warning) {
                    $stmt->execute();
                }
                $stmt->close();
                $conn->query("COMMIT");
            }
            
            if(!empty($delete_warnings)){
                $stmt = $conn->prepare("delete from comic_warnings where comic_id = ? and warning_id = (select warning_id from warnings where warning_name=?);");
                $stmt->bind_param("is", $comic, $delete_warning);
                $conn->query("START TRANSACTION");
                foreach ($delete_warnings as $delete_warning) {
                    $stmt->execute();
                }
                $stmt->close();
                $conn->query("COMMIT");
            }

            mysqli_autocommit($conn, true);
        }
        $newComicURL = "Location: /comic/".$comic;
        header($newComicURL);
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
else{
    //comic has no owners, so let anyone edit
    $is_owner = True;
}

if (!$is_owner && $_SESSION['user_id'] != 1){
    //user doesn't have edit access, kick them out
    $conn->close();
    $newComicURL = "Location: /claim/".$comic;
    header($newComicURL);
}

if ($comic == 152 && !$is_actual_owner && $_SESSION['user_id'] != 1){
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
        $comic_image = $row["comic_image"];
		$comic_crawler = json_decode(str_replace('"crawler": ','',str_replace("'", '"',$row["comic_crawler"])), True);
        $comic_summary = $row["comic_summary"];
        if (!isset($comic_smedia) && $row["comic_smedia"]){$comic_smedia = explode(',', $row["comic_smedia"]);}
        if (!isset($comic_supports) && $row["comic_support"]){$comic_supports = explode(',', $row["comic_support"]);}
        if (!$comic_warning_levels) {$comic_warning_levels = explode(',', $row["comic_warning"]);}
        if (!$rating_enabled) {$rating_enabled = $row["rating_enabled"];}
        if (!$comic_status) {$comic_status = $row["comic_status"];}
        if (!$comic_freq) {$comic_freq = $row["comic_freq"];}
        $comic_pages = json_decode(str_replace("'", '"',$row["comic_pages"]));
        if (!isset($comic_mirrors) && $row["comic_mirrors"]){$comic_mirrors = explode(',', $row["comic_mirrors"]);}
        if (!$comic_rss) {$comic_rss = $row["comic_rss"];}
    }
} else {
    $errors[] = "Comic not found. Try the search bar?";
}

if ($comic_summary){
    $comic_summary = str_replace('\r\n',"\r\n",$comic_summary);
}

$stmt = $conn->prepare("select warning_name, warning_category from warnings inner join comic_warnings on warnings.warning_id = comic_warnings.warning_id where comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_warnings[] = array($row["warning_name"], $row["warning_category"]);
    }
}

if($comic_warning_levels){
    foreach($comic_warning_levels as $level){
        $warning_levels[] = $level;
    }
}

if($comic_warnings){
    foreach($comic_warnings as $warnings){
        //print_r($warnings);
        if($warnings[1] == "Adult Themes"){
            $a[] = $warnings[0];
        }
        if($warnings[1] == "Violence"){
            $b[] = $warnings[0];
        }
        if($warnings[1] == "Language"){
            $c[] = $warnings[0];
        }
        if($warnings[1] == "Nudity/Sex"){
            $d[] = $warnings[0];
        }
        if($warnings[1] == "Content"){
            $e[] = $warnings[0];
        }
    }
    $adult    = array($warning_levels[0],$a);
    $violence = array($warning_levels[1],$b);
    $language = array($warning_levels[2],$c);
    $sn       = array($warning_levels[3],$d);
    $content  = array($warning_levels[4],$e);
    $warning_array = array("Adult Themes"=>$adult,"Violence"=>$violence,"Language"=>$language,"Sex/Nudity"=>$sn,"Content"=>$content);
}

if ($_SESSION['user_id'] == 1){
    $stmt = $conn->prepare("select tag_name from tags inner join comic_tags on tags.tag_id = comic_tags.tag_id where comic_id=? order by tag_name limit 100");
}else{
    $stmt = $conn->prepare("select tag_name from tags inner join comic_tags on tags.tag_id = comic_tags.tag_id where comic_id=? and restricted=0 order by tag_name limit 100");
}
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
// output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_tags[] = $row["tag_name"];
    }
}

if ($is_actual_owner || $_SESSION['user_id'] == 1) {$stmt = $conn->prepare("SELECT count(comic_id) as total FROM user_subs WHERE comic_id=?");}
else {$stmt = $conn->prepare("SELECT count(comic_id) as total FROM user_subs WHERE comic_id=? and sub_type='public'");}
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

<!--Adds more fields to mirrors and social media. Also creators if we go that route-->
<script type="text/javascript">
var counter = 1;
var limit = 15;
function addInput(divName){
    if (divName == "socialmedia" || divName == "mirrors" || divName == "support"){
        if (counter == limit){alert("You have reached the limit of adding " + counter + " inputs");}
        else{
          var newdiv = document.createElement('div');
          newdiv.innerHTML = '<input type="url" class="form-control" name="'+divName+'[]" placeholder="http://...">';
          document.getElementById(divName).appendChild(newdiv);
        counter++;
    }}
    else{
    counter++;
     if (counter == limit)  {
          //alert("You have reached the limit of adding " + counter + " inputs");
          var newdiv = document.createElement('div');
          newdiv.innerHTML = '<div class="row"><div class="col-md-3"><div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>You have reached the limit of ' + (counter-1) + ' creators.</div></div></div>';
          document.getElementById(divName).appendChild(newdiv);
     }
     else if (counter < limit) {
          var newdiv = document.createElement('div');
          newdiv.innerHTML = '<div class="row"><div class="col-md-3"><input type="text" class="form-control col-xs-3" name=name="comic_creator[]" placeholder="Creator ' + counter + '"></div></div>';
          document.getElementById(divName).appendChild(newdiv);
     }
}}

</script>

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
<div class="row">
<?php if ($is_actual_owner==True || $_SESSION['user_id'] == 1){echo '<div class="col-sm-12 col-md-8 col-lg-9">';}else{echo '<div class="col-md-12">';}?>

<h1>Editing <a href="/comic/<?php echo $comic_id;?>"><?php echo $comic_name;?></a></h1>
<form id="editform" method="post" action="/edit/<?php echo $comic_id;?>" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>

  <div class="form-group">
    <label for="comic_name">Comic Name</label>
    <input type="text" class="form-control" name="comic_name" value="<?php echo $comic_name;?>" required>
  </div>

  <div class="form-group">
    <label for="comic_image">Banner Image</label>
    <input type="file" name="comic_image" accept="image/*">
    <p class="help-block">Optional. Recommended size: 824x248 or larger, less than 400kB.<?php if ($comic_image) {?> <a target="_blank" href="/assets/usr_imgs/banners/<?php echo $comic_image; ?>">Current banner</a></p><?php } ?>
  </div>
  
    <?php if ($comic_image) {?><input type="hidden" name="currentbanner" value="<?php echo $comic_image; ?>"><?php } ?>
  
  <div class="form-group">
    <label for="comic_summary">RSS Link</label>
    <input type="url" class="form-control" name="comic_rss" placeholder="http://..."<?php if ($comic_rss){echo ' value="'.$comic_rss.'"';} ?>>
  </div>

  <div class="form-group">
    <label for="comic_summary">Summary</label>
    <textarea id="comic_summary" class="form-control" maxlength="1000" rows="4" name="comic_summary" placeholder="<?php if(empty($comic_summary)){echo '(Optional) Comic Summary';}?>"><?php if($comic_summary){echo $comic_summary;}?></textarea>
    <div id="summary_feedback" class="help-block"></div>
  </div>
  
  <div class="form-group">
    <label>Update Status</label><br/>
    <label class="radio-inline"><input type="radio" name="comic_status" value="Ongoing"<?php if($comic_status == "Ongoing"){echo ' checked="checked"';} ?>>Ongoing</label>
    <label class="radio-inline"><input type="radio" name="comic_status" value="On Hiatus"<?php if($comic_status == "On Hiatus"){echo ' checked="checked"';} ?>>On Hiatus</label>
    <label class="radio-inline"><input type="radio" name="comic_status" value="Completed"<?php if($comic_status == "Completed"){echo ' checked="checked"';} ?>>Completed</label>
    <label class="radio-inline"><input type="radio" name="comic_status" value="Cancelled"<?php if($comic_status == "Cancelled"){echo ' checked="checked"';} ?>>Cancelled</label>
    <label class="radio-inline"><input type="radio" name="comic_status" value="Deleted"<?php if($comic_status == "Deleted"){echo ' checked="checked"';} ?>>Deleted</label>
    <!--<p class="help-block">Comics that haven't been had new pages added by the crawler in a three month span will automatically be moved to "On Hiatus".</p>-->
  </div>

<div class="form-group">
  <label>Update Frequency</label><br/>
    <div class="btn-group" data-toggle="buttons">
      <label class="btn btn-default<?php if (strpos($comic_freq, "Sunday") !== False || $comic_freq == "Sunday"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Sunday"<?php if (strpos($comic_freq, "Sunday") !== False || $comic_freq == "Sunday"){echo ' checked';} ?>> Sunday
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Monday") !== False || $comic_freq == "Monday"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Monday"<?php if (strpos($comic_freq, "Monday") !== False || $comic_freq == "Monday"){echo ' checked';} ?>> Monday
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Tuesday") !== False || $comic_freq == "Tuesday"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Tuesday"<?php if (strpos($comic_freq, "Tuesday") !== False || $comic_freq == "Tuesday"){echo ' checked';} ?>> Tuesday
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Wednesday") !== False || $comic_freq == "Wednesday"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Wednesday"<?php if (strpos($comic_freq, "Wednesday") !== False || $comic_freq == "Wednesday"){echo ' checked';} ?>> Wednesday
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Thursday") !== False || $comic_freq == "Thursday"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Thursday"<?php if (strpos($comic_freq, "Thursday") !== False || $comic_freq == "Thursday"){echo ' checked';} ?>> Thursday
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Friday") !== False || $comic_freq == "Friday"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Friday"<?php if (strpos($comic_freq, "Friday") !== False || $comic_freq == "Friday"){echo ' checked';} ?>> Friday
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Saturday") !== False || $comic_freq == "Saturday"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Saturday"<?php if (strpos($comic_freq, "Saturday") !== False || $comic_freq == "Saturday"){echo ' checked';} ?>> Saturday
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Weekly") !== False || $comic_freq == "Weekly"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Weekly"<?php if (strpos($comic_freq, "Weekly") !== False || $comic_freq == "Weekly"){echo ' checked';} ?>> Weekly
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Biweekly") !== False || $comic_freq == "Biweekly"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Biweekly"<?php if (strpos($comic_freq, "Biweekly") !== False || $comic_freq == "Biweekly"){echo ' checked';} ?>> Biweekly
      </label>
      <label class="btn btn-default<?php if (strpos($comic_freq, "Monthly") !== False || $comic_freq == "Monthly"){echo ' active';} ?>">
        <input type="checkbox" name="day[]" value="Monthly"<?php if (strpos($comic_freq, "Monthly") !== False || $comic_freq == "Monthly"){echo ' checked';} ?>> Monthly
      </label>
    </div>
</div>

  <div class="form-group">
    <label for="socialmedia">Social Media</label>
        <div id="socialmedia">
            <?php if (isset($comic_smedia) and !empty($comic_smedia)){
                    foreach ($comic_smedia as $smedia) { echo '<input type="url" class="form-control" name="socialmedia[]" value="' . $smedia  . '">';}
            }?>
            <input type="url" class="form-control" name="socialmedia[]" placeholder="http://...">
        </div>
        <input type="button" class="btn btn-default" value="+Link" onClick="addInput('socialmedia');" />
  </div>

  <div class="form-group">
    <label for="mirrors">Mirrors</label>
        <div id="mirrors">
            <?php if (isset($comic_mirrors) and !empty($comic_mirrors)){
                    foreach ($comic_mirrors as $mirror) { echo '<input type="url" class="form-control" name="mirrors[]" value="' . $mirror . '">';}
            }?>
            <input type="url" class="form-control" name="mirrors[]" placeholder="http://...">
        </div>
        <input type="button" class="btn btn-default" value="+Link" onClick="addInput('mirrors');" />
  </div>
  
  <div class="form-group">
    <label for="support">Support Links</label>
        <div id="support">
            <?php if (isset($comic_supports) and !empty($comic_supports)){
                    foreach ($comic_supports as $support) { echo '<input type="url" class="form-control" name="support[]" value="' . $support  . '">';}
            }?>
            <input type="url" class="form-control" name="support[]" placeholder="http://...">
        </div>
        <input type="button" class="btn btn-default" value="+Link" onClick="addInput('support');" />
  </div>

  <div class="form-group">
    <label for="comic_tags">Tags</label>
    <select class="multipleSelectTags" multiple name="comic_tags[]">
        <?php foreach ($possible_tags as $tag) {
            echo '<option';
            if ($comic_tags && in_array($tag, $comic_tags)){echo " selected";}
            echo ' value="'.$tag.'">'.$tag.'</option>
            ';}?>
    </select>
  </div>

  <div class="form-group">
    <label for="content_warnings">Content Warnings</label><br />
	
	<table class="table table-borderless">
	<tr>
	<td><label for="adult">Adult Themes</label></td>
	<td><div class="btn-group" data-toggle="buttons">
	    <label class="btn btn-default<?php if ($comic_warning_levels[0] == 0){echo ' active';} ?>">
		    <input type="radio" name="adult" value="0"<?php if ($comic_warning_levels[0] == 0){echo ' checked';} ?>> None
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[0] == 1){echo ' active';} ?>">
            <input type="radio" name="adult" value="1"<?php if ($comic_warning_levels[0] == 1){echo ' checked';} ?>> Mild
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[0] == 2){echo ' active';} ?>">
            <input type="radio" name="adult" value="2"<?php if ($comic_warning_levels[0] == 2){echo ' checked';} ?>>Moderate
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[0] == 3){echo ' active';} ?>">
            <input type="radio" name="adult" value="3"<?php if ($comic_warning_levels[0] == 3){echo ' checked';} ?>>Explicit
        </label>
	</div></td>
	</tr>
	
	<tr>
	<td><label for="adult">Violence</label></td>
	<td><div class="btn-group" data-toggle="buttons">
	    <label class="btn btn-default<?php if ($comic_warning_levels[1] == 0){echo ' active';} ?>">
		    <input type="radio" name="violence" value="0"<?php if ($comic_warning_levels[1] == 0){echo ' checked';} ?>> None
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[1] == 1){echo ' active';} ?>">
            <input type="radio" name="violence" value="1"<?php if ($comic_warning_levels[1] == 1){echo ' checked';} ?>> Mild
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[1] == 2){echo ' active';} ?>">
            <input type="radio" name="violence" value="2"<?php if ($comic_warning_levels[1] == 2){echo ' checked';} ?>>Moderate
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[1] == 3){echo ' active';} ?>">
            <input type="radio" name="violence" value="3"<?php if ($comic_warning_levels[1] == 3){echo ' checked';} ?>>Explicit
        </label>
	</div></td>
	</tr>
	
	<tr>
	<td><label for="adult">Language</label></td>
	<td><div class="btn-group" data-toggle="buttons">
	    <label class="btn btn-default<?php if ($comic_warning_levels[2] == 0){echo ' active';} ?>">
		    <input type="radio" name="language" value="0"<?php if ($comic_warning_levels[2] == 0){echo ' checked';} ?>> None
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[2] == 1){echo ' active';} ?>">
            <input type="radio" name="language" value="1"<?php if ($comic_warning_levels[2] == 1){echo ' checked';} ?>> Mild
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[2] == 2){echo ' active';} ?>">
            <input type="radio" name="language" value="2"<?php if ($comic_warning_levels[2] == 2){echo ' checked';} ?>>Moderate
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[2] == 3){echo ' active';} ?>">
            <input type="radio" name="language" value="3"<?php if ($comic_warning_levels[2] == 3){echo ' checked';} ?>>Explicit
        </label>
	</div></td>
	</tr>

	<tr>
	<td><label for="nudesex">Nudity/Sex</label></td>
	<td><div class="btn-group" data-toggle="buttons">
	    <label class="btn btn-default<?php if ($comic_warning_levels[3] == 0){echo ' active';} ?>">
		    <input type="radio" name="nudesex" value="0"<?php if ($comic_warning_levels[3] == 0){echo ' checked';} ?>> None
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[3] == 1){echo ' active';} ?>">
            <input type="radio" name="nudesex" value="1"<?php if ($comic_warning_levels[3] == 1){echo ' checked';} ?>> Mild
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[3] == 2){echo ' active';} ?>">
            <input type="radio" name="nudesex" value="2"<?php if ($comic_warning_levels[3] == 2){echo ' checked';} ?>>Moderate
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[3] == 3){echo ' active';} ?>">
            <input type="radio" name="nudesex" value="3"<?php if ($comic_warning_levels[3] == 3){echo ' checked';} ?>>Explicit
        </label>
	</div></td>
	</tr>
	
	<tr>
	<td><label for="adult">Content</label></td>
	<td><div class="btn-group" data-toggle="buttons">
	    <label class="btn btn-default<?php if ($comic_warning_levels[4] == 0){echo ' active';} ?>">
		    <input type="radio" name="content" value="0"<?php if ($comic_warning_levels[4] == 0){echo ' checked';} ?>> None
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[4] == 1){echo ' active';} ?>">
            <input type="radio" name="content" value="1"<?php if ($comic_warning_levels[4] == 1){echo ' checked';} ?>> Mild
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[4] == 2){echo ' active';} ?>">
            <input type="radio" name="content" value="2"<?php if ($comic_warning_levels[4] == 2){echo ' checked';} ?>>Moderate
        </label>
        <label class="btn btn-default<?php if ($comic_warning_levels[4] == 3){echo ' active';} ?>">
            <input type="radio" name="content" value="3"<?php if ($comic_warning_levels[4] == 3){echo ' checked';} ?>>Explicit
        </label>
	</div></td>
	</tr>
	</div>
</table>

<!--
<datalist id="warnings">
    <?php foreach ($possible_warnings as $warning) {echo '<option value="'.$warning[0].'">';
    if ($warning[1]){echo $warning[1].': '.$warning[0].'</option>
';}
    else{echo $warning[0].'</option>
';}}?>
</datalist>
-->

<?php
//stuff the used warnings into a simple list
if($comic_warnings){
    foreach ($comic_warnings as $warnings){
        $applied_warnings[] = $warnings[0];
    }
}
?>

  <div class="form-group">
    <label for="warninglist">Warnings List</label>
    <select class="multipleSelectWarnings" multiple name="comic_warnings[]">
        <?php foreach ($possible_warnings as $warning) {
            echo '<option';
            if ($applied_warnings && in_array($warning[0], $applied_warnings)){echo " selected";}
            echo ' value="'.$warning[0].'">'.$warning[0].' ('.$warning[1].')</option>
            ';}?>
    </select>
  </div>

  <?php if ($is_actual_owner==True || $_SESSION['user_id'] == 1){ ?>
  <div class="form-group">
    <label for="warninglist">Pages</label>
    <textarea id="comic_pages" class="form-control" rows="20" name="comic_pages"><?php echo join("\r\n",$comic_pages); ?></textarea>
    <p class="help-block">Add or remove pages from the comic, one per row. Please note if the comic's layout has changed, you may need to update the <a href="/crawledit/<?php echo $comic_id; ?>">crawler</a>.</p>
  </div>
  <?php } ?>

  <input type="hidden" name="token" value="<?php echo $newToken; ?>">
  <?php if ($comic_image) {?><input type="hidden" name="currentbanner" value="<?php echo $comic_image;?>"><?php } ?>
  <button type="submit" class="btn btn-default">Submit</button>
</form>

</div><!--main col-->
</div>
        <?php if ($is_actual_owner==True || $_SESSION['user_id'] == 1){ ?>
        <div class="col-sm-12 col-md-4 col-lg-3">
            <div class="panel panel-default">
                <div class="panel-heading">Comic Info</div>
                <div class="panel-body">
                
                    Readers: <?php if ($comic_readers['total'] > 0){ echo '<a href="/subscribers/'.$comic_id.'">'.$comic_readers['total'].'</a>';}else{echo "0";} ?><br />
                    Pages: <?php echo count($comic_pages)?><br />
                    Crawler Status: <?php echo $comic_crawler['status'];?><br />
                    Crawl Type: <?php echo $comic_crawler['type'];?><br />
                    <a href="/crawledit/<?php echo $comic_id; ?>">[Edit Crawl Config]</a>
                    
                </div>
            </div><!--panel-->
        </div><!--col-->
        <?php } ?>

</div><!--row-->
</div><!--panel body-->
</div> <!-- /container -->

		<?php require_once 'includes/footer.inc.php';?>
    <script>
    $(document).ready(function() {
        $('.multipleSelectTags').fastselect();
        $('.multipleSelectWarnings').fastselect();
        
        var text_max = 1000;
        var text_length = $('#comic_summary').val().length;
        $('#summary_feedback').html(text_length +"/"+text_max + ' characters');

        $('#comic_summary').keyup(function() {
            var text_length = $('#comic_summary').val().length;
            var text_remaining = text_max - text_length;

            $('#summary_feedback').html(text_length +"/"+text_max + ' characters');
        });
    });
    </script>
  </body>
</html>
