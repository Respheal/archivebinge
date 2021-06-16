<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
require_once("includes/formtoken.inc.php");

if (!isset($_POST) || empty($_POST)){
    unset($_SESSION['newComic']);
}

//if not logged in, gtfo
if(!$_SESSION['user_id']){
    header('Location: /');
}elseif(empty($_POST)){
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

            $comic_name = $_SESSION['newComic']['comicName'];
            $comic_rss = $_SESSION['newComic']['rss'];

            //insert comic
            $stmt = $conn->prepare("INSERT INTO comics (comic_name, comic_crawler, comic_pages, comic_rss) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $comic_name, $comic_crawler, $comic_pages, $comic_rss);
            $stmt->execute();
            $cid = $stmt->insert_id;

            //insert tags
            if(!empty($_SESSION['newComic']['genre'])){
                $stmt = $conn->prepare("INSERT INTO `comic_tags` (comic_id, tag_id) values (?, (select tag_id from tags where tags.tag_name = ?))");
                foreach ($_SESSION['newComic']['genre'] as $tag){
                    $stmt->bind_param('ss', $cid, $tag);
                    $stmt->execute();
                }
            }

            $stmt->close();
            $conn->close();
			unlink($jsonfile);
			unlink($rawjson);
            $newComicURL = "Location: /comic/".$cid;
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
    $whitelist = array('token','comicName','crawlerType','firstPage', 'secondPage', 'rss', 'pageList', 'manual', 'genre');

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
            $_SESSION['newComic']['comicName'] = mysqli_real_escape_string($conn, filter_var($_POST['comicName'],FILTER_SANITIZE_STRING));
            $_SESSION['newComic']['crawlerType'] = $_POST['crawlerType'];
            $_SESSION['newComic']['firstPage'] = filter_var($_POST['firstPage'], FILTER_SANITIZE_URL);
            $_SESSION['newComic']['secondPage'] = filter_var($_POST['secondPage'], FILTER_SANITIZE_URL);
            $_SESSION['newComic']['rss'] =  filter_var($_POST['rss'], FILTER_SANITIZE_URL);
            $_SESSION['newComic']['genre'] =  $_POST['genre'];
            //make this so it has to match the provided options
            $_SESSION['newComic']['manual'] = mysqli_real_escape_string($conn, filter_var($_POST['pageList'], FILTER_SANITIZE_STRING));
            $conn->close();
            if (empty($errors) and $_POST['crawlerType'] != "manual"){
                header('Location: /crawltest');
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
                    $comic_name = $_SESSION['newComic']['comicName'];
                    $comic_rss = $_SESSION['newComic']['rss'];

                    //insert comic
                    $stmt = $conn->prepare("INSERT INTO comics (comic_name, comic_crawler, comic_pages, comic_rss) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssss', $comic_name, $comic_crawler, $comic_pages, $comic_rss);
                    $stmt->execute();
                    $cid = $stmt->insert_id;

                    //insert tags
                    if(!empty($_SESSION['newComic']['genre'])){
                        $stmt = $conn->prepare("INSERT INTO `comic_tags` (comic_id, tag_id) values (?, (select tag_id from tags where tags.tag_name = ?))");
                        foreach ($_SESSION['newComic']['genre'] as $tag){
                            $stmt->bind_param('ss', $cid, $tag);
                            $stmt->execute();
                        }
                    }

                    $stmt->close();
                } else {$errors[] = "Please verify that you've provided at least one valid URL.";$newToken = generateFormToken('form1');}

                $conn->close();

                if (!$errors) {
                    $newComicURL = "Location: /comic/".$cid;
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

        <h1>Submit a Comic</h1>
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
<form class="form-group" action="/submit" method="post">

<div class="alert alert-warning" role="warning">Note: Submission is still a hit-or-miss process that needs a lot of fine-tuning. While most comics <em>should</em> work, a lot won't yet. For now, you can submit comics that can't currently be crawled to <a href="mailto:support@DOMAIN.com?subject=Comic Submission&body=First page link: ">support@DOMAIN.com</a> and I'll work on adapting the crawler for them asap.<br />
The list of comics currently known to be broken with the crawler can be found <a href="">here.</a></div>

  <div class="form-group">
    <label for="comicName">Comic Name</label>
    <input type="text" class="form-control" name="comicName" placeholder="Awesome Comic"<?php if (isset($_SESSION['newComic']['comicName'])){echo ' value="'.$_SESSION['newComic']['comicName'].'"';}?> required>
  </div>

  <div class="form-group">
  <label for="sel1">Pick some starter tags</label>
  <small class="form-text" text-muted">Hold ctrl (or drag with the mouse) to select more than one.</small>
  <select multiple class="form-control" name="genre[]">
    <option>Action</option>
    <option>Adventure</option>
    <option>Autobiography</option>
    <option>Comedy</option>
    <option>Daily</option>
    <option>Drama</option>
    <option>Erotic</option>
    <option>Fancomic</option>
    <option>Fantasy</option>
    <option>Furry</option>
    <option>Horror</option>
    <option>Mystery</option>
    <option>Philosophical</option>
    <option>Queer</option>
    <option>Religious</option>
    <option>Romance</option>
    <option>SciFi</option>
    <option>Slice of Life</option>
    <option>Superhero</option>
    <option>Surreal</option>
    <option>Tragedy</option>
  </select>
</div>

<div class="form-group">
    <label>Page Finder</label><br />
    <div class="btn-group" data-toggle="buttons">
      <label class="btn btn-default active" onClick="crawlerFields('page');">
        <input type="radio" name="crawlerType" value="page" checked> Page Crawl
      </label>
      <label class="btn btn-default" onClick="crawlerFields('increment');">
        <input type="radio" name="crawlerType" value="increment"> Increment
      </label>
      <label class="btn btn-default" onClick="crawlerFields('archive');">
        <input type="radio" name="crawlerType" value="archive"> Archive Binge
      </label>
      <label class="btn btn-default" onClick="crawlerFields('webtoons');">
        <input type="radio" name="crawlerType" value="webtoons"> Webtoon
      </label>
      <label class="btn btn-default" onClick="crawlerFields('smackjeeves');">
        <input type="radio" name="crawlerType" value="smackjeeves"> Smack Jeeves
      </label>
      <label class="btn btn-default" onClick="crawlerFields('manual');">
        <input type="radio" name="crawlerType" value="manual"> Manual
      </label>
    </div>
</div>

<div class="well">

<ul>
    <li><strong>Page Crawl:</strong> Default crawl method. Choose this one if you're not sure what to use. Works for Tapas comics!</li>
    <li><strong>Increment:</strong> If Page Crawl doesn't work, but the url of the comic increments (comic/1, comic/2, and so on), try this.</li>
    <li><strong>Archive Binge:</strong> If Page Crawl and Increment don't work, try this <strong>(Warning: Does not crawl for updates yet)</strong>.</li>
    <li><strong>Webtoons:</strong> LINE Webtoons comics only.</li>
    <li><strong>Smack Jeeves:</strong> Smack Jeeves comics only.</li>
    <li><strong>Manual Entry:</strong> Manually enter the list of comic pages (last resort).</li>
</ul>

</div>

    <div id="crawler">
      <div class="form-group">
        <label for="first_page">First Page</label>
        <input type="url" class="form-control" name="firstPage" placeholder="First Page URL: http://..." required>
      </div>

      <div class="form-group">
        <label for="second_page">Second Page</label>
        <input type="url" class="form-control" name="secondPage"  placeholder="Second Page URL: http://..." required>
      </div>

      <div class="form-group">
        <label for"comic_rss">RSS Feed</label>
        <input type="url" class="form-control" name="rss" placeholder="Comic's RSS URL (Optional): http://.../rss">
      </div>

      <input type="hidden" name="crawlerType" value="page">
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
    <input type="url" class="form-control" name="firstPage"  placeholder="First Page URL: http://..." required>
  </div>

  <div class="form-group">
    <label for="second_page">Second Page</label>
    <input type="url" class="form-control" name="secondPage" placeholder="Second Page URL: http://..." required>
  </div>

  <div class="form-group">
    <label for"comic_rss">RSS Feed</label>
    <input type="url" class="form-control" name="rss" placeholder="Comic's RSS URL (Optional): http://.../rss">
  </div>

    <input type="hidden" name="crawlerType" value="page">
`;
    }
    else if (crawler == "archive"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="archive">Archive Page</label>
    <input type="url" class="form-control" name="firstPage"  placeholder="Archive Page URL: http://..." required>
  </div>

  <div class="form-group">
    <label for"comic_rss">RSS Feed</label>
    <input type="url" class="form-control" name="rss" placeholder="Comic's RSS URL (Optional): http://.../rss">
  </div>

    <input type="hidden" name="crawlerType" value="archive">

`
    }
    else if (crawler == "increment"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="first_page">First Page</label>
    <input type="url" class="form-control" name="firstPage" placeholder="First Page URL: http://..." required>
  </div>

  <div class="form-group">
    <label for"comic_rss">RSS Feed</label>
    <input type="url" class="form-control" name="rss" placeholder="Comic's RSS URL (Optional): http://.../rss">
  </div>

    <input type="hidden" name="crawlerType" value="increment">

`
    }
    else if (crawler == "webtoons"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="episode">Webtoons Episode List</label>
    <input type="url" class="form-control" name="firstPage" placeholder="Any Webtoons episode: http://..." required>
    <small class="form-text" text-muted">URL should end in "episode_no=#". Example: <a href=""></a></small>
  </div>

  <div class="form-group">
    <label for"comic_rss">RSS Feed</label>
    <input type="url" class="form-control" name="rss" placeholder="Comic's RSS URL (Optional): http://.../rss">
  </div>
    <input type="hidden" name="token" value="<?php echo $newToken; ?>">
    <input type="hidden" id="crawlerType" value="webtoons">
`
    }
    else if (crawler == "smackjeeves"){
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="episode">Smack Jeeves comic (profile page or comic page)</label>
    <input type="url" class="form-control" name="firstPage" placeholder="Comic or Profile: http://..." required>
  </div>

    <input type="hidden" name="token" value="<?php echo $newToken; ?>">
    <input type="hidden" id="crawlerType" value="smackjeeves">
`
    }
    else{
        document.getElementById("crawler").innerHTML = `
  <div class="form-group">
    <label for="manual">Comic Pages</label>
    <textarea class="form-control" rows="15" name="pageList" placeholder="http://comic.com/page1
http://comic.com/page2
..."></textarea>
    <small class="form-text text-muted">List of comic page URLs, one on each line.</small>
  </div>

  <div class="form-group">
    <label for"comic_rss">RSS Feed</label>
    <input type="url" class="form-control" name="rss" placeholder="Comic's RSS URL (Optional): http://.../rss">
    <small class="form-text text-muted">Including the RSS feed URL is <strong>highly</strong> recommended with this method, as we may be able to pull new updates from RSS.</small>
  </div>

    <input type="hidden" name="crawlerType" value="manual">
`
    }
}

</script>
<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
