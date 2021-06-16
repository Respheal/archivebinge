<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
require_once("includes/formtoken.inc.php");
$newToken = generateFormToken('form2');

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

if (isset($_SESSION['newComic'])){
    chdir('crawler');
    $filename = tempnam("./crawldumps", "tmp");
    $_SESSION['newComic']['tempjson'] = $filename;

    
    if ($_SESSION['newComic']['crawlerType'] == "page"){
        $firstpage = escapeshellarg($_SESSION["newComic"]["firstPage"]);
        $secondpage = escapeshellarg($_SESSION["newComic"]["secondPage"]);

        $cmd = './crawlerenv/bin/scrapy crawl typefinder -a starturl='.$firstpage.' -a secondurl='.$secondpage.' -a cid='.$filename;
        exec($cmd, $errors);

        if (empty($errors)){
            $cmd = './crawlerenv/bin/scrapy crawl binge -a starturl='.$firstpage.' -a cid='.$filename.' -a count=1';
            exec($cmd);
        }
        else{
            $_SESSION['errorReturn'] = $errors[0];
			$newComicURL = "Location: /crawledit/".$comic_id;
            header($newComicURL);
        }
    }elseif ($_SESSION['newComic']['crawlerType'] == "tapas"){
        $firstpage = escapeshellarg($_SESSION["newComic"]["firstPage"]);

        $cmd = './crawlerenv/bin/scrapy crawl tapas -a starturl='.$firstpage.' -a cid='.$filename;
        exec($cmd, $errors);
    }elseif ($_SESSION['newComic']['crawlerType'] == "webtoons"){
        $firstpage = escapeshellarg($_SESSION["newComic"]["firstPage"]);
        $cmd = './crawlerenv/bin/scrapy crawl webtoons -a starturl='.$firstpage.' -a cid='.$filename;
        exec($cmd, $errors);
    }elseif ($_SESSION['newComic']['crawlerType'] == "smackjeeves"){
        $parsedurl = parse_url($_SESSION["newComic"]["firstPage"],PHP_URL_QUERY);
        parse_str($parsedurl, $titleno);
        $_SESSION["newComic"]["firstPage"] = "https://www.smackjeeves.com/api/discover/articleList?titleNo=".$titleno['titleNo'];
        $firstpage = escapeshellarg($_SESSION["newComic"]["firstPage"]);
        $cmd = './crawlerenv/bin/scrapy crawl smackjeeves -a starturl='.$firstpage.' -a cid='.$filename;
        exec($cmd, $errors);
    }elseif ($_SESSION['newComic']['crawlerType'] == "archive"){
        $firstpage = escapeshellarg($_SESSION["newComic"]["firstPage"]);
        $cmd = './crawlerenv/bin/scrapy crawl archive -a starturl='.$firstpage.' -a cid='.$filename;
        exec($cmd, $errors);
    }elseif ($_SESSION['newComic']['crawlerType'] == "increment"){
        $firstpage = escapeshellarg($_SESSION["newComic"]["firstPage"]);
        $cmd = './crawlerenv/bin/scrapy crawl increment -a starturl='.$firstpage.' -a cid='.$filename.' -a count=1';
        exec($cmd, $errors);
    }
    elseif ($_SESSION['newComic']['crawlerType'] == "manual"){
        $_SESSION['newComic']['pages'] = json_encode(explode("\n", str_replace("\r", "", $_SESSION['newComic']['manual'])));
        $crawler = array("status"=>"working", "type"=>"manual");
        $rawjson = array($pages,$crawler);
        $_SESSION['newComic']['tempjson'] = $rawjson;
    }
}

?>
  </head>
  <body onload="showLoader()">

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">

        <h1>Submit a Comic</h1>
<?php
if($errors) {echo'<div class="row">
<div class="col-md-6 col-md-offset-3">';
    if(count($errors > 1)) {
        foreach ($errors as $error){
            //echo $error;
            echo'    <div class="alert alert-danger" role="alert">Error: '.$error.'</div>';
        }
    }else{
        echo'    <div class="alert alert-danger" role="alert">Error: '.$error.'</div>';
    }
echo '</div>
</div>';
}?>

<div id="status"></div>

    </div><!--panel body-->
</div> <!-- /container -->

	<?php require_once 'includes/footer.inc.php';?>
<script type="text/javascript">
$(document).ready(function() {
  $.ajaxSetup({ cache: false });
});
<?php $jsonname = basename($filename);?>
var ajaxRequestIntervalMs = 1000;
var ajaxRequestUrl = '../crawler/crawldumps/<?php echo $jsonname; ?>.pagefound';
var loaderImageUrl = '../assets/images/ajax-loader.gif'; 
// http://www.w3schools.com/js/js_obj_boolean.asp
var isLoading=new Boolean(); 
isLoading=false;
 
//JavaScript's built-in setInterval() function
setInterval(
    function(){                      
        $.ajax({
            url: ajaxRequestUrl,
            type: "GET",
            cache: false,                                  
            statusCode: {
                // HTTP-Code "Page not found"
                404: function() {
                    if (isLoading===false){
                        showLoader();
                    }
                },
                // HTTP-Code "Success"
                200: function() {
                    if (isLoading===true){
                        $.getJSON(ajaxRequestUrl, function(json) {
                            //var str = JSON.stringify(json, null, 2);
                            hideLoader(json);
                        });
                    }
                }    
            }
        });     
    },
    ajaxRequestIntervalMs
);
 
// ------------ show- and hide-functions for the overlay -----------------
function showLoader(){
    document.getElementById("status").innerHTML = "<img src='"+loaderImageUrl+"' />"
    isLoading=true;
};
  
function hideLoader(json){
    if (json.pages.length > 2){
        //document.getElementById("status").innerHTML = "<p>Is this page three? "+json.pages[2];
        document.getElementById("status").innerHTML = "<p>Does this look right so far?<br /><a href='"+json.pages[0]+"'target='_blank'>"+json.pages[0]+"</a><br /><a href='"+json.pages[1]+"'target='_blank'>"+json.pages[1]+"</a><br /><a href='"+json.pages[2]+"'target='_blank'>"+json.pages[2]+"</a><span title='and so on'>...</span></p><form action='/crawledit/<?php echo $comic_id;?>' method='post'><input type='hidden' name='token' value='<?php echo $newToken; ?>'><button type='submit' name='crawlSuccess' class='btn btn-success' aria-label='Yes' title='Accept crawl method' value='yes'>Yes</button> <button type='submit' name='crawlSuccess' class='btn btn-danger' aria-label='No' title='Back to the submission page to try another method' value='no'>No</button></form>";
    } else {
        document.getElementById("status").innerHTML = "<p>We found a path between page one and two, but not page three. Does this comic have a page three yet?</p><form action='/crawledit/<?php echo $comic_id;?>' method='post'><input type='hidden' name='token' value='<?php echo $newToken; ?>'><button type='submit' name='crawlMissing' class='btn btn-default' aria-label='Yes' title='Return to Submit to try another crawler' value='yes'>Yes</button> <button type='submit' name='crawlMissing' class='btn btn-default' aria-label='No' title='Tentatively approve crawler' value='no'>No</button></form>";
    }
    isLoading=false;
};

</script>
  </body>
</html>
