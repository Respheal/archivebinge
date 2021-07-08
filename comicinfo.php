<?php require_once("includes/header.inc.php");
$conn = dbConnect();

$comic = mysqli_real_escape_string($conn, $_GET['comic']);

$stmt = $conn->prepare("SELECT * from comics where comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_id = $row["comic_id"];
        $comic_name = $row["comic_name"];
        $comic_image = $row["comic_image"];
        $comic_summary = $row["comic_summary"];
        //$comic_tags = str_replace('"','',explode(', ', $row["comic_tags"]));
        if ($row["comic_smedia"]){$comic_smedia = explode(',', $row["comic_smedia"]);}
        if ($row["comic_support"]){$comic_supports = explode(',', $row["comic_support"]);}
        $comic_warning_levels = explode(',', $row["comic_warning"]);
        //$comic_rating = $row["comic_rating"];
        $rating_enabled = $row["rating_enabled"];
        $comic_status = $row["comic_status"];
        $comic_freq = $row["comic_freq"];
        $comic_pages = json_decode(str_replace("'", '"',$row["comic_pages"]));
        if ($row["comic_mirrors"]){$comic_mirrors = explode(',', $row["comic_mirrors"]);}
        $comic_rss = $row["comic_rss"];
        $reader_options = json_decode($row["reader_options"], true);
    }
} else {
    $errors[] = "Comic not found. Try the search bar?";
}

//dns prefetch prep
$prefetch = parse_url($comic_pages[0], PHP_URL_HOST);


if ($comic_summary){
    $comic_summary = str_replace('\r\n','<br />',$comic_summary);
}

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

//Is the current user subscribed? Favorites?
$stmt = $conn->prepare("SELECT bookmark, sub_type, favorite FROM user_subs WHERE user_id=? and comic_id=?");
$stmt->bind_param('ii', $_SESSION['user_id'], $comic);
$stmt->execute();
$stmt->bind_result($bookmark, $sub_type, $favorite);
$stmt->store_result();
$stmt->fetch();

//Ratings
$stmt = $conn->prepare("select avg(rating) as rating, count(rating) as number from comic_rating WHERE comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$stmt->bind_result($comic_rating, $ratings_rec);
$stmt->store_result();
$stmt->fetch();

//tags
$stmt = $conn->prepare("select tag_name from tags inner join comic_tags on tags.tag_id = comic_tags.tag_id where comic_id=? order by tag_name limit 100");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
// output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_tags[] = $row["tag_name"];
    }
}

//warnings
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


if (isset($_POST) && !empty($_POST) && !empty($_SESSION['user_id'])){
    //Subscribing, unsubscribing, favorites
    if ($_POST['subscribe'] == "public"){
        if ($sub_type){
            //UPDATE user_subs SET sub_type = 'public' WHERE comic_id=? and user_id=?
            $stmt = $conn->prepare("UPDATE user_subs SET sub_type = 'public' WHERE comic_id=? and user_id=?");
            $stmt->bind_param('ii', $comic_id, $_SESSION['user_id']);
            $stmt->execute();
            $sub_type = "public";
        }else{
            //INSERT INTO user_subs (user_id, comic_id, sub_type) VALUES (?, ?, ?);
            $stmt = $conn->prepare("INSERT INTO user_subs (user_id, comic_id, sub_type) VALUES (?, ?, 'public');");
            $stmt->bind_param('ii', $_SESSION['user_id'], $comic_id);
            $stmt->execute();
            $sub_type = "public";
            $favorite=0;
        }
    }elseif ($_POST['subscribe'] == "private"){
        if ($sub_type){
            //UPDATE user_subs SET sub_type = 'private' WHERE comic_id=? and user_id=?
            $stmt = $conn->prepare("UPDATE user_subs SET sub_type = 'private' WHERE comic_id=? and user_id=?");
            $stmt->bind_param('ii', $comic_id, $_SESSION['user_id']);
            $stmt->execute();
            $sub_type = "private";
        }else{
            //INSERT INTO user_subs (user_id, comic_id, sub_type) VALUES (?, ?, ?);
            $stmt = $conn->prepare("INSERT INTO user_subs (user_id, comic_id, sub_type) VALUES (?, ?, 'private');");
            $stmt->bind_param('ii', $_SESSION['user_id'], $comic_id);
            $stmt->execute();
            $sub_type = "private";
            $favorite=0;
        }
    }elseif ($_POST['subscribe'] == "no" && $sub_type){
        //DELETE FROM user_subs WHERE user_id=? and comic_id=?
        $stmt = $conn->prepare("DELETE FROM user_subs WHERE user_id=? and comic_id=?;");
        $stmt->bind_param('ii', $_SESSION['user_id'], $comic_id);
        $stmt->execute();
        unset($sub_type);
        unset($favorite);
    }elseif ($_POST['favorite'] == "yes" && $sub_type){
        if ($favorite==0){
            //UPDATE user_subs SET favorite = 1 WHERE comic_id=? and user_id=?
            $stmt = $conn->prepare("UPDATE user_subs SET favorite = 1 WHERE comic_id=? and user_id=?;");
            $stmt->bind_param('ii', $comic_id, $_SESSION['user_id']);
            $stmt->execute();
            $favorite=1;
        }else{
            $errors[] = "You've already marked this comic as a favorite";
        }
    }elseif ($_POST['favorite'] == "no" && $sub_type){
        if ($favorite==1){
            //UPDATE user_subs SET favorite = 0 WHERE comic_id=? and user_id=?
            $stmt = $conn->prepare("UPDATE user_subs SET favorite = 0 WHERE comic_id=? and user_id=?;");
            $stmt->bind_param('ii', $comic_id, $_SESSION['user_id']);
            $stmt->execute();
            $favorite=0;
        }else{
            $errors[] = "This comic is not in your favorites.";
        }
    }elseif ($_POST['bookmark'] || ($_POST['bookmark'] == 0 && $_POST['bookmark'] !== False)){
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
        $bookmark=$bmpage;
    }

}

if ($user_is_owner) {$stmt = $conn->prepare("SELECT count(comic_id) as total FROM user_subs WHERE comic_id=?");}
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
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
?>
  <link rel=dns-prefetch" href="//<?php echo $prefetch; ?>">
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
    <div class="row">
        <div class="col-sm-12 col-md-8 col-lg-8">
            <!--errors-->
            <?php
            if($errors) {echo'<div class="row">
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
            <!--add comic success-->
            <?php if (isset($_SESSION['newComic'])){
                unset($_SESSION['newComic']);
                echo '<div class="alert alert-success">
  <strong>Success!</strong> '.$comic_name.' has been added! We\'ll go fetch the rest of the pages over the next 15-30 minutes. Please check back and report if the crawler had any issues! <a href="/submit">Add another?</a>
</div>';
            }?>

            <!--Comic banner and header. If no image, just the h2-->
            <div class="col-sm-12 comicbanner<? if(!empty($comic_image)){echo ' thumbnail';}?>">
                <?php if(!empty($comic_image)){echo '<img alt="" class="img-responsive" src="/assets/usr_imgs/banners/'.$comic_image.'"><div class="caption"><h2>'.$comic_name.'</h2></div><!--caption-->';}else{echo '<h2>'.$comic_name.'</h2>';}?>
            </div><!--inner col-->

            <!--Read tools-->
            <?php if ($_SESSION['user_id']){ echo '<form method="post" action="/comic/'.$comic_id.'">';} ?>
            <div class="btn-group bottom-space" role="group" aria-label="...">
                <?php if (strpos($comic_pages[0], 'webtoon') !== false || strpos($comic_pages[0], 'smackjeeves') !== false || $reader_options['style'] == 'webtoons') { ?>
                    <a href="<?php if ($bookmark){echo $comic_pages[$bookmark];}else{echo $comic_pages[0];} ?>" target="_blank" class="btn btn-default">Read<?php if (strpos($comic_pages[0], 'smackjeeves') !== false) { echo " (SmackJeeves)";}?><?php if (strpos($comic_pages[0], 'webtoon') !== false) { echo " (Webtoons)";} ?></a>
                    <?php if (!empty($_SESSION['user_id']) && $sub_type){ ?>
                        <select name="bookmark" class="btn btn-static" style="height:34px;">
                          <?php
                          $pageno = 0;
                          foreach ($comic_pages as $pages){
                              echo '<option value="'.$pageno.'"';
                              if($pageno == $bookmark){echo ' selected';}
                              echo '>';
                              $pageno++;
                              echo $pageno.'</option>';
                          }
                          ?>
                        </select>
                        <button type="submit" class="btn btn-default" name="bmsubmit" value="<?php echo $comic_id; ?>">Bookmark</button><?php }?>
                      <a href="<?php end($comic_pages); echo $comic_pages[key($comic_pages)]; ?>" target="_blank" class="btn btn-default">Latest</a>
                      <a href="<?php echo $comic_pages[array_rand($comic_pages)]; ?>" target="_blank" class="btn btn-default">Random</a>
                <?php }else{?>
                    <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/reader/<?php echo $comic_id;?>/<?php if($bookmark){echo $bookmark;}else{echo "0";} ?>" class="btn btn-default">Read</a>
                    <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/reader/<?php echo $comic_id;?>/<?php end($comic_pages); echo key($comic_pages); ?>" class="btn btn-default">Latest</a>
                    <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/reader/<?php echo $comic_id;?>/<?php echo array_rand($comic_pages); ?>" class="btn btn-default">Random</a>
                <? } ?>
                <?php if (!empty($comic_rss)) {echo '<a href="' . $comic_rss . '" class="btn btn-default">RSS</a>';}?>
                <?php if ($comic_readers['total'] > 0) {echo '<a href="/subscribers/'.$comic_id.'" class="btn btn-default">Readers <span class="badge">'.$comic_readers['total'].'</span></a>';}?>
                <!--quick sub-->

                <?php if (!empty($_SESSION['user_id'])){ if (!$sub_type){ echo '<button type="submit" class="btn btn-default" title="Quick Sub" name="subscribe" value="public">Subscribe</button>';}else{ echo '<button type="submit" class="btn btn-default" title="Quick Un-Sub" name="subscribe" value="no">Unsubscribe</button>';}} ?>
            </div>
            <?php if ($_SESSION['user_id']){ echo '</form>';} ?>

            <?php if (strpos($comic_pages[0], 'webtoon') !== false || strpos($comic_pages[0], 'smackjeeves') !== false ||$reader_options['style'] == 'webtoons') { ?><!--Webtoons Reader-->
            <div class="alert alert-info" role="alert">
                <strong>Heads up!</strong> The Reader iframe is disabled on this comic. <a href="https://<?php echo $DOMAIN; ?>/faq#question-14">More info here!</a>
            </div><?php } ?>

            <!--summary-->
            <p class="text-justify"><?php echo $comic_summary;?></p>

            <!--comic info table-->
            <dl class="dl-horizontal">
                <!--if comic claimed by a user or users-->
                <?php if(isset($comic_owners) and !empty($comic_owners)) { echo '<dt>Creator';if(count($comic_owners) > 1) { echo "s";}echo '</dt>
                    <dd>'; foreach ($comic_owners as $owner) { echo '<a href="/profile/' . $owner['id'] . '">' . $owner['username'] . '</a><br />';}}?></dd>

                <!--comic status-->
                <?php if (isset($comic_status)){ ?>
                    <dt>Status</dt>
                    <dd><?php echo $comic_status;?></dd>
                <?php } ?>

                <!--update day-->
                <?php if (isset($comic_freq)){
                    echo '<dt>Update Day'; if (strpos($comic_freq, ",") == True) { echo "s";}
                    echo '</dt>';
                    echo '<dd>'.str_replace(",", ", ", $comic_freq).'</dd>';
                }?>

                <!--social media link(s)--><?php if (isset($comic_smedia) and !empty($comic_smedia)){echo'<dt>Social Media</dt>
                    <dd class="truncate">
                        <ul class="list-unstyled">';
                            foreach ($comic_smedia as $smedia) { echo '<li><a href="' . $smedia  . '">' . $smedia. '</a></li>';}echo'
                        </ul>
                    </dd>';}?>

                <!--mirror link(s)-->
                <?php if (isset($comic_pages) and !empty($comic_pages)){echo'<dt>First Page</dt>
                    <dd><a href="'.$comic_pages[0].'">'.$comic_pages[0].'</a></dd>';}?>

                <!--mirror link(s)-->
                <?php if (isset($comic_mirrors) and !empty($comic_mirrors)){echo'<dt>Mirrors</dt>
                    <dd class="truncate">
                        <ul class="list-unstyled">';
                            foreach ($comic_mirrors as $mirror) { echo '<li><a href="' . $mirror  . '">' . $mirror . '</a></li>';}echo'
                        </ul>
                    </dd>';}?>

                <!--support link(s)-->
                <?php if (isset($comic_supports) and !empty($comic_supports)){echo'<dt>Support</dt>
                    <dd class="truncate">
                        <ul class="list-unstyled">';
                            foreach ($comic_supports as $support) { echo '<li><a href="' . $support  . '">' . $support . '</a></li>';}echo'
                        </ul>
                    </dd>';}?>

                <!--list of comic tags (requires many-to-many setup) -->
                <?php if ($comic_tags){echo '<dt>Tags</dt>
                    <dd>';foreach ($comic_tags as $tag) {
                        echo '<a href="/search.php?yestags%5B%5D='.str_replace(" ","+",$tag).'&advanced=True"><span class="label label-default">' . $tag . '</span></a> ';
                    }echo '</dd>';} ?>
<br />
                <!--list of comic warnings (requires many-to-many and refacoring how the colored-labels work)-->
                    <dt>Content Warnings</dt>
                    <dd>
                        <?php
                            if (!empty($warning_array)){
                                $i=0;
                                $warnexists = False;
                                foreach ($warning_array as $warntype=>$contents) {
                                    if ($contents[0] > 0){
                                        $warnexists=true;
                                        switch ($contents[0]) {
                                            case 1:
                                                $content_alert = "warning";
                                                $warning_desc = "Mild";
                                                break;
                                            case 2:
                                                $content_alert = "danger";
                                                $warning_desc = "Moderate";
                                                break;
                                            case 3:
                                                $content_alert = "nsfw";
                                                $warning_desc = "Explicit";
                                                break;
                                            }
                                        echo '<span title="Warning Level: ' . $warning_desc . '" class="label label-' . $content_alert . '">'.$warntype.'</span>';
                                        if($contents[1]){echo ' <span class="warninglist">'.implode(", ",$contents[1]).'</span>';}else{echo '&nbsp;';}
                                        echo '<br />';
                                        }
                                }
                                if (!$warnexists){echo '<abbr title="Although the creator has listed this comic as being safe for all readers, there may still be untagged content in this comic. Proceed with caution.">Comic marked as having no content warnings.</abbr>';}
                            }else{echo "There is no content warning information for this comic. Proceed with caution.";}?>
                    </dd><br />
            </dl>

            <!--page list-->
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <a data-toggle="collapse" data-parent="#accordion" href="#pagelist" class="collapsed link-unstyled">
                        <div class="panel-heading">
                            <h4 class="panel-title">Pages (<?php echo count($comic_pages)?>) <span class="caret"></span></h4>
                        </div><!--panel heading-->
                    </a>
                    <div id="pagelist" class="panel-collapse collapse">
                        <div class="panel-body">
                            <ol>
                                <?php if (strpos($comic_pages[0], 'webtoon') !== false || strpos($comic_pages[0], 'smackjeeves') !== false ||$reader_options['style'] == 'webtoons') {foreach ($comic_pages as $page) {echo '<li class="pagelist"><a target="_blank" href="' . $page . '">' . $page . '</a></li>';}}
                                else{ $i=0; foreach ($comic_pages as $page) {echo '<li class="pagelist"><a href="http://'.$DOMAIN.'/reader/'.$comic_id . '/' . $i . '">' . $page . '</a></li>';$i++;}} ?>
                            </ol>
                        </div><!--list body-->
                    </div><!--pagelist-->
                </div><!--accordion-->
            </div><!--accordion group-->
        </div><!--col-->
        <!--comic control panel-->
		<div class="row">
            <div class="col-sm-12 col-md-4 col-lg-4">
			    <div class="col-sm-6 col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Controls</div>
                        <div class="panel-body">
                            <form method="post" action="/comic/<?php echo $comic_id; ?>">
                            <div class="list-group">
                                <?php if (!empty($_SESSION['user_id'])){
                                //if user logged in
                                    if ($comic_owners){
                                    //if comic has owners
                                        foreach ($comic_owners as $owners){
                                            //only show edit button if the user is an owner; otherwise, only show claim
                                            if ($_SESSION['user_id'] == $owners['id'] || $_SESSION['user_id'] == 1){
                                                //is user an owner
                                                echo '<a href="/edit/'.$comic_id.'" class="list-group-item">Edit</a>';break;
                                            }else{ echo '<a href="/claim/'.$comic_id.'" class="list-group-item">Claim</a>';}
                                        }
                                    }
                                    else{
                                        echo '<a href="/edit/'.$comic_id.'" class="list-group-item">Edit</a>';
                                        echo '<a href="/claim/'.$comic_id.'" class="list-group-item">Claim</a>';
                                    }
                                ?>

                                <?php if ($_SESSION['user_id'] == $owners['id'] || $_SESSION['user_id'] == 1){?><a href="/crawledit/<?php echo $comic_id;?>" class="list-group-item">Crawl Edit</a><?php } ?>
                                <span class="" id="accordion">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#subscribe" class=" list-group-item collapsed link-unstyled">Subscribe<?php if ($sub_type){echo"d";}?> <span class="caret"></span></a>
                                    <span id="subscribe" class="collapse">
                                        <?php if ($sub_type!="public"){ echo '<button type="submit" style="border-radius: 0 !important" class="list-group-item" name="subscribe" value="public">Public</button>';}else{ echo '<button type="submit" style="border-radius: 0 !important" class="list-group-item" name="subscribe" value="no">Unsubscribe: Public</button>';} ?>
                                        <?php if ($sub_type!="private"){ echo '<button type="submit" style="border-radius: 0 !important" class="list-group-item" name="subscribe" value="private">Private</button>';}else{ echo '<button type="submit" style="border-radius: 0 !important" class="list-group-item" name="subscribe" value="no">Unsubscribe: Private</button>';} ?>
                                    </span><!--subscribe-->
                                </span><!--accordion group-->
                                <?php if (isset($favorite)){
                                    if($favorite==0){ echo '<button type="submit" style="border-radius: 0 !important" class="list-group-item" name="favorite" value="yes">Favorite</button>';}
                                    else{ echo '<button type="submit" style="border-radius: 0 !important" class="list-group-item" name="favorite" value="no">Un-Favorite</button>';}}
                                ?>
                                <?php }else{echo '<a href="/login.php?page=comic&id='.$comic_id.'" class="list-group-item">Login</a>'; } ?>
                                <a href="/widgets/<?php echo $comic_id; ?>" class="list-group-item">Widgets</a>
                                <a href="mailto:<?php echo $SUPPORT_EMAIL; ?>?Subject=Issue%20Report: <?php echo urlencode($comic_name); ?>" target="_blank" class="list-group-item">Report Issue</a>
                            </div><!--list group-->
                            </form>
                        </div><!--panel body-->
                    </div><!--panel-->
		        </div><!--col-->
            </div><!--col-->
        </div><!--row-->
    </div><!--row-->
    </div><!--panel body-->
</div> <!-- /container -->

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog text-center">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-body">
          <form>
              <input type="number" data-max="10" data-min="1" name="rating" class="rating" />
              <br />
              <input type="submit" class="btn btn-default" value="Save"></input>
          </form>
        </div>
      </div>

    </div>
  </div>

</div>
<!--<?php print_r($reader_options); ?>-->
<?php require_once 'includes/footer.inc.php';?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rating-input/0.4.0/bootstrap-rating-input.min.js" type="text/javascript"></script>
</body>
</html>
