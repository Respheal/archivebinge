<?php require_once("includes/header.inc.php");

$feed_url = 'https://'.$DOMAIN.'/blog/feed/';
$content = file_get_contents($feed_url); // get XML string

$conn = dbConnect();
$date = date('Y-m-d H:i:s');
$stmt = $conn->prepare("delete FROM user_cookies WHERE created < NOW() - INTERVAL 3 MONTH");
$stmt->execute();

$stmt = $conn->prepare("SELECT comic_id, comic_name, comic_image FROM comics ORDER BY comic_id desc limit 5");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $newestcomics[] = array(
            "id"=>$row["comic_id"],
            "name"=>$row["comic_name"],
            "image"=>$row["comic_image"]);
    }

} else {
    $error = "0 results";
}

/*Pride Month
$stmt = $conn->prepare("SELECT DISTINCT comics.comic_id, comic_name, comic_image FROM comics left join comic_tags on comics.comic_id=comic_tags.comic_id where tag_id=37 or tag_id=39 or tag_id=51 or tag_id=148 or tag_id=151 or tag_id=447 or tag_id=452 or tag_id=472 or tag_id=496 or tag_id=542 or tag_id=572 or tag_id=494 or tag_id=195 or tag_id=575 order by rand() limit 10");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $pride[] = array(
            "id"=>$row["comic_id"],
            "name"=>$row["comic_name"],
            "image"=>$row["comic_image"]);
    }

} else {
    $error = "0 results";
}*/

$stmt = $conn->prepare("SELECT comic_id, comic_name, comic_image FROM comics where last_crawl is not null ORDER BY last_update desc limit 10");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $newestupdates[] = array(
            "id"=>$row["comic_id"],
            "name"=>$row["comic_name"],
            "image"=>$row["comic_image"]);
    }

} else {
    $error = "0 results";
}

$stmt = $conn->prepare("SELECT distinct comics.comic_id, comic_name, comic_image, (SELECT count(comic_id) as total FROM user_subs where comic_id=comics.comic_id) as subcount FROM comics inner join user_subs on comics.comic_id = user_subs.comic_id order by subcount desc limit 5");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $mostsubs[] = array(
            "id"=>$row["comic_id"],
            "name"=>$row["comic_name"],
            "image"=>$row["comic_image"]);
    }

} else {
    $error = "0 results";
}

//if logged in
if($_SESSION['user_id']){
    //subbed comics
    $stmt = $conn->prepare("select comics.comic_id, comic_name, comic_image from comics inner join user_subs on comics.comic_id = user_subs.comic_id where user_id=? order by last_update desc limit 10");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $subbed_comics[] = array(
                "id"=>$row["comic_id"],
                "name"=>$row["comic_name"],
                "image"=>$row["comic_image"]);
        }
    }

    //owned comics
    $stmt = $conn->prepare("select comics.comic_id, comic_name, comic_image from comics left join comic_owners on comics.comic_id = comic_owners.comic_id where user_id=?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $owned_comics[] = array(
                "id"=>$row["comic_id"],
                "name"=>$row["comic_name"],
                "image"=>$row["comic_image"]);
        }
    }
}

$stmt->close();
$conn->close();

require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");

echo "<style>
";
foreach ($newestcomics as $comic){
    if (!empty($comic["image"])){
        echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
    }
}
foreach ($newestupdates as $comic){
    if (!empty($comic["image"])){
        echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
    }
}

foreach ($mostsubs as $comic){
    if (!empty($comic["image"])){
        echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
    }
}
/*Pride Month
foreach ($pride as $comic){
    if (!empty($comic["image"])){
        echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
    }
}*/

if($_SESSION['user_id']){
    foreach ($subbed_comics as $comic){
        if (!empty($comic["image"])){
            echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
        }
    }
    foreach ($owned_comics as $comic){
        if (!empty($comic["image"])){
            echo '.comic'.$comic["id"].':before {background-image: -moz-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: -webkit-linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");background-image: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0) 60%), url("/assets/usr_imgs/banners/'.$comic["image"].'");filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#000000",endColorstr="#000000",GradientType=1), url("/assets/usr_imgs/banners/'.$comic["image"].'");}';
        }
    }
}
echo "</style>";


?>

  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">

<!--            <div class="row">
            <div class="col-md-12 text-center">
-<div class="alert alert-info" role="alert">
  <strong>Heads up!</strong> If you have issues logging in, please clear your <a href="https://www.lifewire.com/how-to-clear-cache-2617980" target="_blank"><u>cache</u></a> and <a href="https://www.digitaltrends.com/computing/how-to-delete-cookies/" target="_blank"><u>cookies</u></a>!</div>
            </div>
        </div>-->

        <div class="row">
            <div class="col-md-4">


<!--put the css here, using the loop that generates the content of this panel-->
                <?php if($_SESSION['user_id']){?>

                <?php if($owned_comics){?><div class="panel panel-default">
                    <div class="panel-heading">My Comics</div>
                    <div class="panel-body">
<div class="list-group">
<?php foreach ($owned_comics as $comic){
    echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
    if (!empty($comic["image"])){
        echo ' comic-bar comic'.$comic["id"];
    }
    echo '"><strong>'.$comic['name'].'</strong></a>';
}
?>
</div>
                    </div>
                </div>
                <?php } ?>
                <div class="panel panel-default">
                    <div class="panel-heading">Reading List</div>
                    <div class="panel-body">
                    <?php if($subbed_comics){?>
                        <div class="list-group">
                        <?php foreach ($subbed_comics as $comic){
                            echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
                            if (!empty($comic["image"])){
                                echo ' comic-bar comic'.$comic["id"];
                            }
                            echo '"><strong>'.$comic['name'].'</strong></a>';
                        }
                        ?>
                        </div>
                    <?php }else{ ?>
                    <p>No subscriptions</p>
                    <?php } ?>
                    </div>
                </div>
                <!--<div class="panel panel-default">
                    <div class="panel-heading">Friends' Reading Lists</div>
                    <div class="panel-body">
                        <strong>Coming soon</strong>

                    </div>
                </div>-->

                <?php } ?>
                <div class="panel panel-default">
                    <div class="panel-heading">About Archive Binge</div>
                    <div class="panel-body">
                        <p>AB is a webcomic aggregator and reader. Our mission is to aggregate comic updates from multiple sources, including Tapas, Webtoons, and self-hosted comics, allowing webcomic readers to track updates and find new favorite comics more easily.</p>

                    </div>
                </div>

</div>


            <div class="col-md-5">

<!--                <div class="panel panel-default">
                    <div class="panel-heading">Pride Month</div>
                    <div class="panel-body">

<div class="list-group">
<?php foreach ($pride as $comic){
    echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
    if (!empty($comic["image"])){
        echo ' comic-bar comic'.$comic["id"];
    }
    echo '"><strong>'.$comic['name'].'</strong></a>';
}
?>
</div>
                    </div>
                </div>-->

                <div class="panel panel-default">
                    <div class="panel-heading">Latest Updates</div>
                    <div class="panel-body">
<div class="list-group">
<?php foreach ($newestupdates as $comic){
    echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
    if (!empty($comic["image"])){
        echo ' comic-bar comic'.$comic["id"];
    }
    echo '"><strong>'.$comic['name'].'</strong></a>';
}
?>
</div>
                    </div>
                </div>


                <div class="panel panel-default">
                    <div class="panel-heading">Newest Comics</div>
                    <div class="panel-body">

<div class="list-group">
<?php foreach ($newestcomics as $comic){
    echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
    if (!empty($comic["image"])){
        echo ' comic-bar comic'.$comic["id"];
    }
    echo '"><strong>'.$comic['name'].'</strong></a>';
}
?>
</div>
                    </div>
                </div>

                <!--
                <div class="panel panel-default">
                    <div class="panel-heading">Most Popular (Binge Reads)</div>
                    <div class="panel-body">
<strong>Coming soon</strong>
                    </div>
                </div>
                -->

                <div class="panel panel-default">
                    <div class="panel-heading">Most Popular (Subscriptions)</div>
                    <div class="panel-body">
<div class="list-group">
<?php foreach ($mostsubs as $comic){
    echo '<a href="/comic/'.$comic['id'].'" class="list-group-item list-group-item-action';
    if (!empty($comic["image"])){
        echo ' comic-bar comic'.$comic["id"];
    }
    echo '"><strong>'.$comic['name'].'</strong></a>';
}
?>
</div>
                    </div>
                </div>


            </div>

            <div class="col-md-3 text-center">
                <div class="panel panel-default">
                    <div class="panel-heading">Comicad Network</div>
                    <div class="panel-body">
                        <?php include_once 'includes/pw/skyscraper.inc.php'; ?>
                    </div>
                </div>

            </div>

        </div><!--row-->
    </div><!--panel-body-->
</div> <!-- /container -->

	<?php require_once 'includes/footer.inc.php';?>

  </body>
</html>
