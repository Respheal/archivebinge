<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");

function abc($a, $b)
{
    return strcmp($a["name"], $b["name"]);
}

function pages($a, $b)
{
    return ((count($a["pages"])-$a["bookmark"]) - (count($b["pages"])-$b["bookmark"]));
}

function pagesrev($a, $b)
{
    return ((count($b["pages"])-$b["bookmark"]) - (count($a["pages"])-$a["bookmark"]));
}

$conn = dbConnect();

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])){
    $user = mysqli_real_escape_string($conn, $_SESSION['user_id']);
}else{
    header("Location:/");
}

if (isset($_POST) && !empty($_POST)){
    //Subscribing, unsubscribing, favorites
    if ($_POST['private']){
        $stmt = $conn->prepare("UPDATE user_subs SET sub_type = 'private' WHERE comic_id=? and user_id=?");
        $stmt->bind_param('ii', $_POST['private'], $_SESSION['user_id']);
        $stmt->execute();
    }elseif ($_POST['public']){
        $stmt = $conn->prepare("UPDATE user_subs SET sub_type = 'public' WHERE comic_id=? and user_id=?");
        $stmt->bind_param('ii', $_POST['public'], $_SESSION['user_id']);
        $stmt->execute();
    }elseif ($_POST['unsubscribe']){
        $stmt = $conn->prepare("DELETE FROM user_subs WHERE user_id=? and comic_id=?;");
        $stmt->bind_param('ii', $_SESSION['user_id'], $_POST['unsubscribe']);
        $stmt->execute();
    }elseif ($_POST['favorite']){
        $stmt = $conn->prepare("UPDATE user_subs SET favorite = 1 WHERE comic_id=? and user_id=?;");
        $stmt->bind_param('ii', $_POST['favorite'], $_SESSION['user_id']);
        $stmt->execute();
    }elseif ($_POST['unfavorite']){
            $stmt = $conn->prepare("UPDATE user_subs SET favorite = 0 WHERE comic_id=? and user_id=?;");
            $stmt->bind_param('ii', $_POST['unfavorite'], $_SESSION['user_id']);
            $stmt->execute();
    }
    elseif ($_POST['bookmark'] || ($_POST['bookmark'] == 0 && $_POST['bookmark'] !== False)){
        if($_POST['bookmark'] == 0 && $_POST['bookmark'] !== False){
            $bmpage = 0;
        }
        else{
            $bmpage = mysqli_real_escape_string($conn, filter_var($_POST['bookmark'],FILTER_SANITIZE_NUMBER_INT));
        }
        $bmcomic = mysqli_real_escape_string($conn, filter_var($_POST['bmsubmit'],FILTER_SANITIZE_NUMBER_INT));

        $stmt = $conn->prepare("UPDATE user_subs SET bookmark = ? WHERE comic_id=? and user_id=?;");
        $stmt->bind_param('iii', $bmpage, $bmcomic, $_SESSION['user_id']);
        $stmt->execute();
    }
}

//get their owned comics
$stmt = $conn->prepare("select comics.comic_id, comic_name, comic_image, comic_crawler from comics left join comic_owners on comics.comic_id = comic_owners.comic_id where user_id=?");
$stmt->bind_param('i', $user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $owned_comics[] = array(
            "id"=>$row["comic_id"],
            "name"=>$row["comic_name"],
            "image"=>$row["comic_image"],
            "status"=>json_decode(str_replace('"crawler": ', '', str_replace("'", '"',$row["comic_crawler"])), true)
            );
    }
}

if (count($owned_comics) > 0){
//(SELECT count(favorite) as total FROM user_subs WHERE comic_id=3)
$stmt = $conn->prepare("SELECT count(comic_id) as subs, (select count(favorite) from user_subs where favorite=1 and comic_id=?) as favorites FROM user_subs WHERE comic_id=?");
$stmt->bind_param('ii', $comic_id, $comic_id);
$i = 0;
foreach ($owned_comics as $comics){
    $comic_id = $comics["id"];
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $owned_comics[$i]["readers"] = $row["subs"];
            $owned_comics[$i]["favorites"] = $row["favorites"];
        }
    }
    $i++;
}
}

//get subbed comics
$stmt = $conn->prepare("select comics.comic_id, comic_name, comic_image, comic_status, comic_pages, last_update, reader_options, sub_type, bookmark, favorite from comics inner join user_subs on comics.comic_id = user_subs.comic_id where user_id=? order by last_update desc");
$stmt->bind_param('i', $user);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $subbed_comics[] = array("id"=>$row["comic_id"],
            "name"=>$row["comic_name"],
            "image"=>$row["comic_image"],
            "status"=>$row["comic_status"],
            "pages" => json_decode(str_replace("'", '"',$row["comic_pages"])),
            "update"=>$row["last_update"],
            "reader_options"=>json_decode($row["reader_options"], true),
            "sub_type"=>$row["sub_type"],
            "bookmark"=>$row["bookmark"],
            "favorite"=>$row["favorite"]);
    }
}

if ($_GET['sort'] == 'update' || $_SESSION['sort'] == 'update'){
    $_SESSION['sort'] = 'update';
}
if ($_GET['sort'] == 'abc' || $_SESSION['sort'] == 'abc'){
    usort($subbed_comics, "abc");
    $_SESSION['sort'] = 'abc';
}
if ($_GET['sort'] == 'pages' || $_SESSION['sort'] == 'pages'){
    usort($subbed_comics, "pages");
    $_SESSION['sort'] = 'pages';
}
if ($_GET['sort'] == 'pages-reverse' || $_SESSION['sort'] == 'pages-reverse'){
    usort($subbed_comics, "pagesrev");
    $_SESSION['sort'] = 'pages-reverse';
}

$stmt->close();
$conn->close();

echo "<style>
";
if (count($owned_comics) > 0){
foreach ($owned_comics as $comic){
    if (!empty($comic["image"])){
        echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
    }else{
    echo '.comic'.$comic["id"].' {background: inherit;}
';
    }
}
}
foreach ($subbed_comics as $comic){
    if (!empty($comic["image"])){
        echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
    }
}
?>
.btn-group.special {
  display: flex;
  margin-bottom:15px;
}

.special .btn {
  flex: 1
}
.progress{
    margin:0;
    border-radius:0;
}
.btn-group>.btn:first-child:not(:last-child):not(.dropdown-toggle) {
    border-top-left-radius: 0;
}
.btn-group>.btn:last-child:not(:first-child), .btn-group>.dropdown-toggle:not(:first-child) {
    border-top-right-radius: 0;
}
.dashboard-comic {
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}
</style>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">

<div class="row">
<?php if (count($owned_comics) > 0){ ?>
    <div class="col-md-8 text-justify">
<?php } else { ?>
    <div class="col-md-12 text-justify">
<?php } ?>

<h1>Comic Dashboard</h1>

<div class="dropdown">
  <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
Sort
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
    <li><a href="/dashboard?sort=update">Last Update</a></li>
    <li><a href="/dashboard?sort=abc">Alphabetical</a></li>
    <li><a href="/dashboard?sort=pages">Pages to Read (Least to Most)</a></li>
    <li><a href="/dashboard?sort=pages-reverse">Pages to Read (Most to Least)</a></li>
  </ul>
</div>

<!--Favorites-->
<h2>Catch Up on Favorites</h2>
<?php if (isset($subbed_comics) && !empty($subbed_comics)){?>
    <?php foreach ($subbed_comics as $comic){
        if ($comic["favorite"] == 1 && $comic['bookmark']+1 != count($comic['pages'])){
            echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action dashboard-comic';
            if (!empty($comic["image"])){
                echo ' comic-bar comic'.$comic["id"];
            }
            echo '"><strong>'.$comic['name'].'</strong>';
            echo '<span class="pull-right"';
            if (!empty($comic["image"])){
                echo ' style="color:#000;-webkit-font-smoothing: antialiased;text-shadow: 1px 1px #fff;z-index:2"';
            }
            if ($comic['update']) {
                echo '>'.date("l, d M Y", strtotime($comic['update']));
                if ($comic['status'] == 'Completed'){
                    echo ' (Completed)';
                } elseif ($comic['status'] == 'Cancelled'){
                    echo ' (Cancelled)';
                }elseif ($comic['status'] == 'On Hiatus'){
                    echo ' (Hiatus)';
                }
            } else {
                echo '>N/A';
            }
            echo '</span>';?>
    </a>
    <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="<?php $percent = ($comic['bookmark']+1)/count($comic['pages'])*100; echo $percent; ?>"
  aria-valuemin="0" aria-valuemax="100" style="min-width:3em;width:<?php $percent = ($comic['bookmark']+1)/count($comic['pages'])*100; echo $percent; ?>%">
            <?php echo $comic['bookmark']+1; echo '/'.count($comic['pages']); ?>
        </div>
    </div>
        <form method="post" action="/dashboard">
        <div class="btn-group special" role="group" aria-label="Subscription Controls">
            <?php if (strpos($comic['pages'][0], 'webtoon') !== false || strpos($comic['pages'][0], 'smackjeeves') !== false || $comic['reader_options']['style'] == 'webtoons') {
                ?><a href="<?php echo $comic['pages'][$comic['bookmark']]; ?>" target="_blank" class="btn btn-default">Read</a>
                    <select name="bookmark" class="btn btn-static page-bookmark">
                      <?php
                      $pageno = 0;
                      foreach ($comic['pages'] as $pages){
                          echo '<option value="'.$pageno.'"';
                          if($pageno == $comic['bookmark']){echo ' selected';}
                          echo '>';
                          $pageno++;
                          echo $pageno.'</option>';
                      }
                      ?>
                    </select>
                  <button type="submit" class="btn btn-default" name="bmsubmit" value="<?php echo $comic['id']; ?>">Bookmark</button>
            <?php }else{?>
            <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/reader/<?php echo $comic['id'];?>/<?php if($comic['bookmark']){echo $comic['bookmark'];}else{echo "0";} ?>" class="btn btn-default">Read</a>
            <? }
            if ($comic['sub_type']=="public"){ echo '<button type="submit" class="btn btn-default" name="private" value="'.$comic['id'].'">Public</button>';}else{echo '<button type="submit" class="btn btn-default" name="public" value="'.$comic['id'].'">Private</button>';} ?>
            <button type="submit" class="btn btn-default" name="unsubscribe" value="<?php echo $comic['id']; ?>">Unsub</button>
            <button type="submit" class="btn btn-default" name="unfavorite" value="<?php echo $comic['id']; ?>">Unfave</button>
        </div>
        </form>
<?php }
    } ?>
<!--End Favorites-->

<h2>Reading List</h2>

<?php
//Not Favorites-->
    foreach ($subbed_comics as $comic){
        if ($comic["favorite"] == 0 && $comic['bookmark']+1 != count($comic['pages'])){
            echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action dashboard-comic';
            if (!empty($comic["image"])){
                echo ' comic-bar comic'.$comic["id"];
            }
            echo '"><strong>'.$comic['name'].'</strong>';
            echo '<span class="pull-right"';
            if (!empty($comic["image"])){
                echo ' style="color:#000;-webkit-font-smoothing: antialiased;text-shadow: 1px 1px #fff;z-index:2"';
            }
            if ($comic['update']) {
                echo '>'.date("l, d M Y", strtotime($comic['update']));
                if ($comic['status'] == 'Completed'){
                    echo ' (Completed)';
                } elseif ($comic['status'] == 'Cancelled'){
                    echo ' (Cancelled)';
                }elseif ($comic['status'] == 'On Hiatus'){
                    echo ' (Hiatus)';
                }
            } else {
                echo '>N/A';
            }
            echo '</span>';?>
    </a>
    <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="<?php $percent = ($comic['bookmark']+1)/count($comic['pages'])*100; echo $percent; ?>"
  aria-valuemin="0" aria-valuemax="100" style="min-width:3em;width:<?php $percent = ($comic['bookmark']+1)/count($comic['pages'])*100; echo $percent; ?>%">
            <?php echo $comic['bookmark']+1;echo '/'.count($comic['pages']); ?>
        </div>
    </div>
        <form method="post" action="/dashboard">
        <div class="btn-group special" role="group" aria-label="Subscription Controls">
            <?php if (strpos($comic['pages'][0], 'webtoon') !== false || strpos($comic['pages'][0], 'smackjeeves') !== false || $comic['reader_options']['style'] == 'webtoons') {
                ?><a href="<?php echo $comic['pages'][$comic['bookmark']]; ?>" target="_blank" class="btn btn-default">Read</a>
                    <select name="bookmark" class="btn btn-static page-bookmark">
                      <?php
                      $pageno = 0;
                      foreach ($comic['pages'] as $pages){
                          echo '<option value="'.$pageno.'"';
                          if($pageno == $comic['bookmark']){echo ' selected';}
                          echo '>';
                          $pageno++;
                          echo $pageno.'</option>';
                      }
                      ?>
                    </select>
                  <button type="submit" class="btn btn-default" name="bmsubmit" value="<?php echo $comic['id']; ?>">Bookmark</button>
            <?php }else{?>
            <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/reader/<?php echo $comic['id'];?>/<?php if($comic['bookmark']){echo $comic['bookmark'];}else{echo "0";} ?>" class="btn btn-default">Read</a>
            <? }
            if ($comic['sub_type']=="public"){ echo '<button type="submit" class="btn btn-default" name="private" value="'.$comic['id'].'">Public</button>';}else{echo '<button type="submit" class="btn btn-default" name="public" value="'.$comic['id'].'">Private</button>';} ?>
            <button type="submit" class="btn btn-default" name="unsubscribe" value="<?php echo $comic['id']; ?>">Unsub</button>
            <button type="submit" class="btn btn-default" name="favorite" value="<?php echo $comic['id']; ?>">Favorite</button>
        </div>
        </form>
<?php }
    } ?>
<!--End Not Favorites-->

<h2>Caught Up</h2>
<?php
    foreach ($subbed_comics as $comic){
        if ($comic['bookmark']+1 == count($comic['pages'])){
            echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action dashboard-comic';
            if (!empty($comic["image"])){
                echo ' comic-bar comic'.$comic["id"];
            }
            echo '"><strong>'.$comic['name'].'</strong>';
            echo '<span class="pull-right"';
            if (!empty($comic["image"])){
                echo ' style="color:#000;-webkit-font-smoothing: antialiased;text-shadow: 1px 1px #fff;z-index:2"';
            }
            if ($comic['update']) {
                echo '>'.date("l, d M Y", strtotime($comic['update']));
                if ($comic['status'] == 'Completed'){
                    echo ' (Completed)';
                } elseif ($comic['status'] == 'Cancelled'){
                    echo ' (Cancelled)';
                }elseif ($comic['status'] == 'On Hiatus'){
                    echo ' (Hiatus)';
                }
            } else {
                echo '>N/A';
            }
            echo '</span>';?>
    </a>
    <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="<?php $percent = ($comic['bookmark']+1)/count($comic['pages'])*100; echo $percent; ?>"
  aria-valuemin="0" aria-valuemax="100" style="min-width:3em;width:<?php $percent = ($comic['bookmark']+1)/count($comic['pages'])*100; echo $percent; ?>%">
            <?php echo $comic['bookmark']+1; echo '/'.count($comic['pages']); ?>
        </div>
    </div>
        <form method="post" action="/dashboard">
        <div class="btn-group special" role="group" aria-label="Subscription Controls">
            <?php if (strpos($comic['pages'][0], 'webtoon') !== false || strpos($comic['pages'][0], 'smackjeeves') !== false || $comic['reader_options']['style'] == 'webtoons') {
                ?><a href="<?php echo $comic['pages'][$comic['bookmark']]; ?>" target="_blank" class="btn btn-default">Read</a>
                    <select name="bookmark" class="btn btn-static page-bookmark">
                      <?php
                      $pageno = 0;
                      foreach ($comic['pages'] as $pages){
                          echo '<option value="'.$pageno.'"';
                          if($pageno == $comic['bookmark']){echo ' selected';}
                          echo '>';
                          $pageno++;
                          echo $pageno.'</option>';
                      }
                      ?>
                    </select>
                  <button type="submit" class="btn btn-default" name="bmsubmit" value="<?php echo $comic['id']; ?>">Bookmark</button>
            <?php }else{?>
            <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/reader/<?php echo $comic['id'];?>/<?php if($comic['bookmark']){echo $comic['bookmark'];}else{echo "0";} ?>" class="btn btn-default">Read</a>
            <? }
            if ($comic['sub_type']=="public"){ echo '<button type="submit" class="btn btn-default" name="private" value="'.$comic['id'].'">Public</button>';}else{echo '<button type="submit" class="btn btn-default" name="public" value="'.$comic['id'].'">Private</button>';} ?>
            <button type="submit" class="btn btn-default" name="unsubscribe" value="<?php echo $comic['id']; ?>">Unsub</button>
            <button type="submit" class="btn btn-default" name="<?php if ($comic["favorite"] == 1){echo 'un';}?>favorite" value="<?php echo $comic['id']; ?>"><?php if ($comic["favorite"] == 1){echo 'Unf';}else{echo 'F';}?>avorite</button>
        </div>
        </form>
<?php }
    }
}else{echo "You're not currently subscribed to any comics."; } ?>
    </div>
<?php if (count($owned_comics) > 0){ ?>
    <div class="col-md-4">
    <div class="panel panel-default">
    <div class="panel-heading">Claimed Comics</div>
    <div class="panel-body"><?php foreach($owned_comics as $comic){
        echo '<a href="/comic/'.$comic["id"].'" class="list-group-item list-group-item-action">';
        if($comic["image"]){
            echo '<div class="list-group-item-heading comic-bar comic'.$comic["id"].'"><h3>'.$comic["name"].'</h3></div>';
        }
        else{
            echo '<div class="list-group-item-heading comic-bar comic'.$comic["id"].'"><h3 class="white-text">'.$comic["name"].'</h3></div>';
        }
        echo '<div class="row"><div class="panel-body">';

        echo '<label>Crawler Status: </label> '.ucfirst($comic["status"]["status"]);
        echo '<br /><label>Readers: </label> '.$comic["readers"];
        echo '<br /><label>Favorites: </label> '.$comic["favorites"];
        echo '</div></div>'; //row and body
        echo '</a>
        ';
    }
    ?>
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
