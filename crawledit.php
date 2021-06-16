<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
require_once("includes/formtoken.inc.php");

//input data into the host_switch table. If it's good, drop it into comics

if (!isset($_POST) || empty($_POST)){
    unset($_SESSION['newComic']);
}

//if not logged in, gtfo
if (empty($_SESSION['user_id'])){
    //if not logged in, immediately kicks back to comic info
    $newComicURL = "Location: /claim/".$_GET['comic'];
    header($newComicURL);
}

$conn = dbConnect();
$comic_id = mysqli_real_escape_string($conn, $_GET['comic']);

//get comic owner
$stmt = $conn->prepare("select users.user_id from users left join comic_owners on users.user_id = comic_owners.user_id where comic_id=?");
$stmt->bind_param('i', $comic_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    $i=0;
    while($row = $result->fetch_assoc()) {
        if ($row["user_id"] == $_SESSION['user_id']){$user_is_owner = True;}
        $comic_owners[$i]['id'] = $row["user_id"];
        $i++;
    }
}

$is_owner = False;
//check if the current user is one of the owners
if ($comic_owners){
    foreach ($comic_owners as $owners){
        //if the comic has owners, only allow progress if logged in user is one of them
        if ($_SESSION['user_id'] == $owners['id']){
            $is_owner = True;
            break;
        }
    }
}

//only let the comic owner or the admin proceed past this point
//if (!$is_owner){
if (!$is_owner && $_SESSION['user_id'] != 1){
    //user doesn't have edit access, kick them out
    $conn->close();
    $newComicURL = "Location: /claim/".$comic_id;
    header($newComicURL);
}

//user is permitted to be here, so let's grab info from the db
$stmt = $conn->prepare("SELECT comic_name, comic_crawler, comic_pages from comics where comic_id=?");
$stmt->bind_param('i', $comic_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_name = $row["comic_name"];
        $comic_crawler = str_replace('"crawler": ','',str_replace("'", '"',$row["comic_crawler"]));
        $comic_pages = json_decode(str_replace("'", '"',$row["comic_pages"]));
    }
} else {
    $errors[] = "Comic not found. Try the search bar?";
}
$comic_crawler = json_decode($comic_crawler, True);

if(empty($_POST)){
    $newToken = generateFormToken('form1');
    if (isset($_SESSION['errorReturn'])){
        $errors[] = $_SESSION['errorReturn'];
        unset($_SESSION['errorReturn']);
    }
}elseif (isset($_POST['crawlSuccess']) || isset($_POST['crawlMissing'])){
	//check that post came from crawlcheck.php
    if (verifyFormToken('form2')){
        $whitelist = array('token','crawlSuccess','crawlMissing');

		//check that post only contains expected values
        foreach ($_POST as $key=>$item) {
            // Check if the value $key (fieldname from $_POST) can be found in the whitelisting array, if not, die with a short message
            if (!in_array($key, $whitelist)) {
                $errors[]="Please use only the fields in the form";
            }
        }
        if (($_POST['crawlSuccess'] == "yes" || $_POST['crawlMissing'] == "no") && empty($errors)){
			//if crawler was correct
            $conn = dbConnect();
            //save new comic into the DB and redirect to its page
            $rawjson = $_SESSION['newComic']['tempjson'];
            $jsonfile = $rawjson.'.pagefound';

            if($_SESSION['newComic']['crawlerType'] != "manual"){
                $json = json_decode(file_get_contents($jsonfile), true);
                $comic_pages = json_encode($json['pages']);
            }else{
                $comic_pages = $_SESSION['newComic']['pages'];
            }
            if($_SESSION['newComic']['crawlerType'] == "page"){
                $comic_crawler = '"crawler": {"status": "working", "firstpage": "'.$_SESSION["newComic"]["firstPage"].'", "position": "'.$json['crawler']['position'].'", "tag": "'.$json['crawler']['tag'].'", "type": "taghunt", "identifier": "'.$json['crawler']['identifier'].'"}';
            }elseif($_SESSION['newComic']['crawlerType'] == "archive"){
                $comic_crawler = '"crawler": {"status": "working", "firstpage": "'.$_SESSION["newComic"]["firstPage"].'", "type": "archive"}';
            }elseif($_SESSION['newComic']['crawlerType'] == "tapas"){
                $comic_crawler = '"crawler": {"status": "working", "firstpage": "'.$_SESSION["newComic"]["firstPage"].'", "type": "tapas"}';
            }elseif($_SESSION['newComic']['crawlerType'] == "increment"){
                $comic_crawler = '"crawler": {"status": "working", "firstpage": "'.$_SESSION["newComic"]["firstPage"].'", "type": "increment"}';
            }elseif($_SESSION['newComic']['crawlerType'] == "webtoons"){
                $comic_crawler = '"crawler": {"status": "working", "firstpage": "'.$_SESSION["newComic"]["firstPage"].'", "type": "webtoons"}';
            }elseif($_SESSION['newComic']['crawlerType'] == "smackjeeves"){
                $comic_crawler = '"crawler": {"status": "working", "firstpage": "'.$_SESSION["newComic"]["firstPage"].'", "type": "smackjeeves"}';
            }else{
                $comic_crawler = '"crawler": {"status": "working", "type": "manual"}';
            }

            //insert comic
            //$stmt = $conn->prepare("INSERT INTO host_switch (comic_crawler, comic_pages, comic_id) VALUES (?, ?, ?)");
            $stmt = $conn->prepare("update comics set comic_crawler = ?, comic_pages = ?, last_crawl = NULL, last_update = NULL where comic_id = ?");
            $stmt->bind_param('ssi', $comic_crawler, $comic_pages, $comic_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
			unlink($jsonfile);
			unlink($rawjson);
            $newComicURL = "Location: /comic/".$comic_id;
            header($newComicURL);
        }
        elseif ($_POST['crawlSuccess'] == "no" || $_POST['crawlMissing'] == "yes"){
            $rawjson = $_SESSION['newComic']['tempjson'];
            $jsonfile = $rawjson.'.pagefound';
            unlink($jsonfile);
			unlink($rawjson);
            $newToken = generateFormToken('form1');
            $errors[]="Unable to find page three. Please try another crawl method.";
        }
    } else {$errors[]="Incorrect entry detected.";$newToken = generateFormToken('form1');}
}elseif(isset($_POST) && !empty($_POST)){
    $whitelist = array('token', 'crawlerType', 'firstPage', 'secondPage', 'pageList', 'manual');

    foreach ($_POST as $key=>$item) {
        // Check if the value $key (fieldname from $_POST) can be found in the whitelisting array, if not, die with a short message
        if (!in_array($key, $whitelist)) {
            $errors[]="Please use only the fields in the form";
        }
    }
    if (($_POST['firstPage'] == $_POST['secondPage']) and (!isset($_POST['pageList']))){
        $errors[] = "Please make sure the page URLs provided are different";
        $newToken = generateFormToken('form1');
    }

    if (empty($errors)){
        if (verifyFormToken('form1')) {
            $conn = dbConnect();
            $_SESSION['newComic']['crawlerType'] = $_POST['crawlerType'];
            $_SESSION['newComic']['firstPage'] = filter_var($_POST['firstPage'], FILTER_SANITIZE_URL);
            $_SESSION['newComic']['secondPage'] = filter_var($_POST['secondPage'], FILTER_SANITIZE_URL);
            //make this so it has to match the provided options
            $_SESSION['newComic']['manual'] = mysqli_real_escape_string($conn, filter_var($_POST['pageList'], FILTER_SANITIZE_STRING));
            $conn->close();
            if (empty($errors) and $_POST['crawlerType'] != "manual"){
                $checkURL = "Location: /checkcrawledit/".$comic_id;
                header($checkURL);
            }elseif($_POST['crawlerType'] == "manual"){
                #since this is a manual entry, skip over the crawl page
                $conn = dbConnect();
                $raw_pages = preg_split("/\\r\\n|\\r|\\n/", $_POST['pageList']);
                $comic_pages = [];
                foreach ($raw_pages as $page){
                    $page_san = filter_var($page,FILTER_SANITIZE_URL);
                    if (filter_var($page_san, FILTER_VALIDATE_URL)){
                        $comic_pages[] = mysqli_real_escape_string($conn,$page_san);
                    }
                }
                if (!empty($comic_pages)){
                    $comic_pages = json_encode($comic_pages);
                    $comic_crawler = '"crawler": {"status": "working", "type": "manual"}';

                    //insert comic
                    //$stmt = $conn->prepare("INSERT INTO host_switch (comic_crawler, comic_pages, comic_id) VALUES (?, ?, ?)");
                    $stmt = $conn->prepare("update comics set comic_crawler = ?, comic_pages = ?, last_crawl = NULL, last_update = NULL where comic_id = ?");
                    $stmt->bind_param('ssi', $comic_crawler, $comic_pages, $comic_id);
                    $stmt->execute();
                    $stmt->close();
                } else {$errors[] = "Please verify that you've provided at least one valid URL.";$newToken = generateFormToken('form1');}

                $conn->close();

                if (!$errors) {
                    $newComicURL = "Location: /comic/".$_GET['comic'];
                    header($newComicURL);
                }
            }
        } else {$errors[]="Incorrect entry detected. (Err. 2)";$newToken = generateFormToken('form1');}
    }
}


?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">

        <h1>Editing Crawler for <a href="/comic/<?php echo $comic_id;?>"><?php echo $comic_name;?></a></h1>
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
<form class="form-group" action="/crawledit/<?php echo $comic_id; ?>" method="post">
<div class="form-group">
    <label>Page Finder</label><br />
    <div class="btn-group" data-toggle="buttons">
      <label class="btn btn-default<?php if($comic_crawler["type"] == "taghunt"){ ?> active<?php } ?>" onClick="crawlerFields('page');">
        <input type="radio" name="crawlerType" value="page"<?php if($comic_crawler["type"] == "taghunt"){ ?> checked<?php } ?>> Page Crawl
      </label>
      <label class="btn btn-default<?php if($comic_crawler["type"] == "increment"){ ?> active<?php } ?>" onClick="crawlerFields('increment');">
        <input type="radio" name="crawlerType" value="increment"<?php if($comic_crawler["type"] == "increment"){ ?> checked<?php } ?>> Increment
      </label>
      <label class="btn btn-default<?php if($comic_crawler["type"] == "archive"){ ?> active<?php } ?>" onClick="crawlerFields('archive');">
        <input type="radio" name="crawlerType" value="archive"<?php if($comic_crawler["type"] == "archive"){ ?> checked<?php } ?>> Archive Binge
      </label>
      <label class="btn btn-default<?php if($comic_crawler["type"] == "webtoons"){ ?> active<?php } ?>" onClick="crawlerFields('webtoons');">
        <input type="radio" name="crawlerType" value="webtoons"<?php if($comic_crawler["type"] == "webtoons"){ ?> checked<?php } ?>> Webtoon
      </label>
      <label class="btn btn-default<?php if($comic_crawler["type"] == "smackjeeves"){ ?> active<?php } ?>" onClick="crawlerFields('smackjeeves');">
        <input type="radio" name="crawlerType" value="smackjeeves"<?php if($comic_crawler["type"] == "smackjeeves"){ ?> checked<?php } ?>> SmackJeeves
      </label>
      <label class="btn btn-default<?php if($comic_crawler["type"] == "manual"){ ?> active<?php } ?>" onClick="crawlerFields('manual');">
        <input type="radio" name="crawlerType" value="manual"<?php if($comic_crawler["type"] == "manual"){ ?> checked<?php } ?>> Manual
      </label>
    </div>
</div>

<div class="well">

<ul>
    <li><strong>Page Crawl:</strong> Default crawl method. Choose this one if you're not sure what to use. Works with Tapas comics!</li>
    <li><strong>Increment:</strong> If Page Crawl doesn't work, but the url of the comic increments (comic/1, comic/2, and so on), try this.</li>
    <li><strong>Archive Binge:</strong> If Page Crawl and Increment don't work, try this <strong>(Warning: Does not crawl for updates yet)</strong>.</li>
    <li><strong>Webtoons:</strong> LINE Webtoons comics only.</li>
    <li><strong>Manual Entry:</strong> Manually enter the list of comic pages (last resort).</li>
</ul>

</div>

    <div id="crawler">
	<?php if ($comic_crawler['type'] == 'taghunt'){ ?>
      <div class="form-group">
        <label for="first_page">First Page</label>
        <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
      </div>

      <div class="form-group">
        <label for="second_page">Second Page</label>
        <input type="url" class="form-control" name="secondPage" value="<?php echo $comic_pages[1]; ?>" required>
      </div>

      <input type="hidden" name="crawlerType" value="page">
	<?php }
	elseif ($comic_crawler['type'] == 'increment'){ ?>
      <div class="form-group">
    <label for="first_page">First Page</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
  </div>

    <input type="hidden" name="crawlerType" value="increment">
	<?php }
	elseif ($comic_crawler['type'] == 'archive'){ ?>
      <div class="form-group">
    <label for="archive">Archive Page</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_crawler['archive']; ?>" required>
  </div>

    <input type="hidden" name="crawlerType" value="archive">
	<?php }
	elseif ($comic_crawler['type'] == 'tapas'){ ?>
      <div class="form-group">
    <label for="episode">Tapas Episode</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
    <small class="form-text text-muted">Any Tapas episode from the series can be used here as long as the episode has the episode list menu.</small>
  </div>

    <input type="hidden" name="token" value="<?php echo $newToken; ?>">
    <input type="hidden" name="crawlerType" value="tapas">
	<?php }
    elseif ($comic_crawler['type'] == 'smackjeeves'){ ?>
      <div class="form-group">
    <label for="episode">Smack Jeeves comic (profile page or comic page)</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
  </div>

    <input type="hidden" name="token" value="<?php echo $newToken; ?>">
    <input type="hidden" id="crawlerType" value="smackjeeves">
    <?php }
	elseif ($comic_crawler['type'] == 'webtoons'){ ?>
      <div class="form-group">
    <label for="episode">Webtoons Episode List</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
    <small class="form-text" text-muted">URL should end in "episode_no=#". Example: <a href=""></a></small>
  </div>

    <input type="hidden" name="token" value="<?php echo $newToken; ?>">
    <input type="hidden" id="crawlerType" value="webtoons">
	<?php }
	else { ?>
      <div class="form-group">
    <label for="manual">Comic Pages</label>
    <textarea class="form-control" rows="15" name="pageList"><?php echo join("\r\n",$comic_pages); ?></textarea>
    <small class="form-text text-muted">List of comic page URLs, one on each line.</small>
  </div>

    <input type="hidden" name="crawlerType" value="manual">
	<?php }	?>
    </div>

    <input type="hidden" name="token" value="<?php echo $newToken; ?>">

  <button type="submit" class="btn btn-default">Submit</button>
</form>

    </div><!--panel body-->
</div> <!-- /container -->

<script type="text/javascript">
function crawlerFields(crawler){
    if (crawler == "page"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="first_page">First Page</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
  </div>

  <div class="form-group">
    <label for="second_page">Second Page</label>
    <input type="url" class="form-control" name="secondPage" value="<?php echo $comic_pages[1]; ?>" required>
  </div>

    <input type="hidden" name="crawlerType" value="page">
`;
    }
    else if (crawler == "archive"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="archive">Archive Page</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_crawler['archive']; ?>" required>
  </div>

    <input type="hidden" name="crawlerType" value="archive">
`
    }
    else if (crawler == "increment"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="first_page">First Page</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
  </div>

    <input type="hidden" name="crawlerType" value="increment">
`
    }
    else if (crawler == "smackjeeves"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="episode">Smack Jeeves comic (profile page or comic page)</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
  </div>

    <input type="hidden" name="token" value="<?php echo $newToken; ?>">
    <input type="hidden" id="crawlerType" value="smackjeeves">
`
    }
    else if (crawler == "webtoons"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="episode">Webtoons Episode List</label>
    <input type="url" class="form-control" name="firstPage" value="<?php echo $comic_pages[0]; ?>" required>
    <small class="form-text" text-muted">URL should end in "episode_no=#". Example: <a href=""></a></small>
  </div>

    <input type="hidden" name="token" value="<?php echo $newToken; ?>">
    <input type="hidden" id="crawlerType" value="webtoons">
`
    }
    else{
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="manual">Comic Pages</label>
    <textarea class="form-control" rows="15" name="pageList"><?php echo join("\r\n",$comic_pages); ?></textarea>
    <small class="form-text text-muted">List of comic page URLs, one on each line.</small>
  </div>

    <input type="hidden" name="crawlerType" value="manual">
`
    }
}

</script>
<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
