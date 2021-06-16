<?php require_once("includes/header.inc.php");
$conn = dbConnect();

$comic = mysqli_real_escape_string($conn, $_GET['comic']);
$page = mysqli_real_escape_string($conn, $_GET['page']);

$stmt = $conn->prepare("SELECT comic_name, comic_pages from comics where comic_id=?");
$stmt->bind_param('i', $comic);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_name = $row["comic_name"];
        $comic_pages = json_decode(str_replace("'", '"',$row["comic_pages"]));
    }
} else {
    $errors[] = "Comic not found. Try the search bar?";
}

if ($_SESSION['user_id']){
    //Is the current user subscribed? Favorites?
    $stmt = $conn->prepare("SELECT sub_type FROM user_subs WHERE user_id=? and comic_id=?");
    $stmt->bind_param('ii', $_SESSION['user_id'], $comic);
    $stmt->execute();
    $stmt->bind_result($sub_type);
    $stmt->store_result();
    $stmt->fetch();
}

if (isset($_POST) && !empty($_POST) && !empty($_SESSION['user_id'])){
    //Subscribing, unsubscribing
    if ($_POST['subscribe'] == "yes"){
        if (!$sub_type){
            //if not already subscribed
            $stmt = $conn->prepare("INSERT INTO user_subs (user_id, comic_id, sub_type) VALUES (?, ?, 'public');");
            $stmt->bind_param('ii', $_SESSION['user_id'], $comic);
            $stmt->execute();
            $sub_type = "public";
        }
    }
    if ($_POST['subscribe'] == "no" && $sub_type){
        //if subscribed but hit unsub
        $stmt = $conn->prepare("DELETE FROM user_subs WHERE user_id=? and comic_id=?;");
        $stmt->bind_param('ii', $_SESSION['user_id'], $comic);
        $stmt->execute();
        unset($sub_type);
    }
}

if ($_SESSION['user_id'] && $sub_type){
    //Update bookmark
    $stmt = $conn->prepare("UPDATE user_subs SET bookmark = ? WHERE user_id = ? and comic_id = ?;");
    $stmt->bind_param('iii', $page, $_SESSION['user_id'], $comic);
    $stmt->execute();
}

$stmt->close();
$conn->close();

require_once("includes/meta.inc.php");
if (isset($page) && $comic_pages[$page+1]){ ?><link rel="prerender" href="<?php echo $comic_pages[$page+1]; ?>"><?php }
require_once("includes/css.inc.php");
?>    <style type="text/css">
        body, html
        {
            margin: 0; padding: 0; height: 100%; overflow: hidden; background-color:#2C2D32;
        }
        .collapsed{
            text-align: center;
            display:flex;
            justify-content: space-around;
        }
        .navbar-brand{
            float:inherit;
        }
        .navbar{
            margin-bottom:inherit;
        }
        .visible-xs-flex {
          display: none !important;
        }
        @media (max-width: 767px) {
          .visible-xs-flex {
            display: flex !important;
          }
        }
        .icon-flipped {
            transform: scaleX(-1);
            -moz-transform: scaleX(-1);
            -webkit-transform: scaleX(-1);
            -ms-transform: scaleX(-1);
        }
        .btn-link{
          border:none;
          outline:none;
          background:none;
          cursor:pointer;
        }
        .btn-link:hover{
          text-decoration:none;
        }
        .disabled{opacity: 0.25;}
        .frame{
            height:100%;
            padding-bottom:52px;
        }
    </style>
  </head>
  <body>

<?php require_once("includes/menu.inc.php");?>

<div class="frame">
<iframe width="100%" height="100%" frameborder="0" src="<?php if (isset($page)) {echo $comic_pages[$page];} else { echo end($comic_pages[$page]);}?>"></iframe>
<!-- the following is some code added because the comic "Futility in Action",
listed under id 1008, wasn't accurately seeing the hits through the reader,
blocking some of their ad revenue. It's comments out because, if FiA is listed
on your site, you'll need to update the ID or do this a smarter way -->
<?php // if ($comic == 1008) { ?>
<script type="text/javascript">
  ( function() {
    if (window.CHITIKA === undefined) { window.CHITIKA = { 'units' : [] }; };
    var unit = {"calltype":"async[2]","publisher":"fiacomic","width":550,"height":250,"sid":"Chitika Default"};
    var placement_id = window.CHITIKA.units.length;
    window.CHITIKA.units.push(unit);
    document.write('<div id="chitikaAdBlock-' + placement_id + '"></div>');
}());
</script>
<script type="text/javascript" src="//cdn.chitika.net/getads.js" async></script>
<?php // } ?>
<script type="text/javascript">
document.onkeydown = function(e) {
    if(!e) e = window.event;
    switch (e.keyCode) {
        case 37: //left arrow
            <?php if(isset($prev)){?>window.location = "<?php echo '/reader/'.$comic.'/'.$prev; ?>";
            <?php }?>break;
        case 39: //right arrow
            <?php if(isset($next)){?>window.location = "<?php echo '/reader/'.$comic.'/'.$next; ?>";
            <?php }?>break;
    }
}
</script>
</div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
