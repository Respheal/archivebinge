<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");

$conn = dbConnect();
if($_GET['page']){
    $page = mysqli_real_escape_string($conn, filter_var($_GET['page'],FILTER_SANITIZE_STRING));
    $page = (int)$page;
}else{$page = 1;}

$query = "SELECT SQL_CALC_FOUND_ROWS distinct comics.comic_id, comic_name, comic_image, comic_summary, comic_status from comics";
if($_GET['sort']){
    if ($_GET['sort'] == "abc"){$query = $query." order by comic_name asc"; $sort="abc";}
    elseif ($_GET['sort'] == "zyx"){$query = $query." order by comic_name desc"; $sort="zyx";}
    elseif ($_GET['sort'] == "123"){$query = $query." order by last_update desc"; $sort="123";}
    elseif ($_GET['sort'] == "321"){$query = $query." order by last_update asc"; $sort="321";}
    elseif ($_GET['sort'] == "newest"){$query = $query." order by comic_id desc"; $sort="newest";}
    elseif ($_GET['sort'] == "oldest"){$query = $query." order by comic_id asc"; $sort="oldest";}
    elseif ($_GET['sort'] == "popular"){$query = $query." inner join user_subs on comics.comic_id = user_subs.comic_id order by (SELECT count(comic_id) as total FROM user_subs where comic_id=comics.comic_id) desc"; $sort="popular";}
}else{$query = $query." order by last_update desc";}
$query = $query." limit ?,30";

$pagemax = $page*30;
$pagemin = $pagemax-30;

//Get tag list: $comic_tags[tag_name]
$sql = "SELECT tag_name from tags ORDER BY tag_name ASC";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $alltags[] = $row["tag_name"];
}

//Get warning list: $comic_warnings[warning_name, warning_category]
$sql = "SELECT warning_name from warnings";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $allwarnings[] = $row["warning_name"];
}

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $pagemin);
        
//find comics that match criteria
if($stmt->execute()){
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $comics[] = array(
                "id"=>$row["comic_id"],
                "name"=>$row["comic_name"],
                "image"=>$row["comic_image"],
                "summary"=>str_replace('\r\n',' ',$row["comic_summary"]),
                "status"=>$row["comic_status"]);
        }

        $stmt = $conn->prepare("SELECT FOUND_ROWS();");
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->store_result();
        $stmt->fetch();
            
        //grab the tags for those comics
        $stmt = $conn->prepare("select tag_name from tags inner join comic_tags on tags.tag_id = comic_tags.tag_id where comic_id=? limit 10");
        $stmt->bind_param('i', $comic_id);
        $i = 0;
        foreach($comics as $comic){
            $comic_id = $comic["id"];
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $comics[$i]["tags"][] = $row["tag_name"];
                }
            }
            $i++;
        }
            
        $pagecount = ceil($count/30);

    }else {
        $error = "0 results";
    }
}else {
    $error = "0 results";
}

    $stmt->close();
    $conn->close();

?>
<script>
function disableEmptyInputs(form) {
  var controls = form.elements;
  for (var i=0, iLen=controls.length; i<iLen; i++) {
    controls[i].disabled = controls[i].value == '';
  }
}
</script>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
        <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9">
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <a data-toggle="collapse" data-parent="#accordion" href="#pagelist" class="collapsed link-unstyled">
                        <div class="panel-heading">
                            <h4 class="panel-title">Advanced Search Options <span class="caret"></span></h4>
                        </div><!--panel heading-->
                    </a>
                    <div id="pagelist" class="panel-collapse collapse">
                        <div class="panel-body">
                        <p>All fields optional.</p>

                        <form onsubmit="disableEmptyInputs(this)" method="get" action="/search">
                            <div class="form-group">
                                <label for="name">Comic Name</label>
                                <input type="text" class="form-control" name="name">
                            </div>
                            <div class="form-group">
                                <label for="creator">Creator</label>
                                <input type="text" class="form-control" name="creator">
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status">
                                    <option></option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="On Hiatus">Hiatus</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>                                    
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="yestags">Include Tags</label>
                                <select class="multipleSelectTags form-control" multiple name="yestags[]">
                                <?php foreach ($alltags as $tag) {
                                    echo '<option value="'.$tag.'">'.$tag.'</option>
                                ';}?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="notags">Exclude Tags</label>
                                <select class="multipleSelectTags form-control" multiple name="notags[]">
                                <?php foreach ($alltags as $tag) {
                                    echo '<option value="'.$tag.'">'.$tag.'</option>
                                ';}?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="warnings">Exclude Warnings</label>
                                <select class="multipleSelectWarnings form-control" multiple name="warnings[]">
                                <?php foreach ($allwarnings as $warning) {
                                    echo '<option value="'.$warning.'">'.$warning.'</option>
                                ';}?>
                                </select>
                                <p class="help-block">Not all comics will have content labeled appropriately. Proceed with caution.</p>
                            </div>
                            <div class="form-group">
                                <label for="pagecount">Pages</label>
                                    <div class="input-group">
                                      <div class="input-group-addon">
                                        <select name="pagesmods">
                                          <option></option>
                                          <option>&gt;</option>
                                          <option>&lt;</option>
                                          <option>=</option>
                                        </select>
                                      </div>
                                      <input class="form-control" type="number" min="0" placeholder="Number of pages" name="pagecount">
                                    </div>
                            </div>
                            <input type="hidden" name="advanced" value="True" />
                            <button type="submit" class="btn btn-default">Search</button>
                        </form>
                        
                        </div><!--list body-->
                    </div><!--pagelist-->
                </div><!--accordion-->
            </div><!--accordion group-->
        <h1>Search Results

<div class="dropdown" style="display:inline-block">
  <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Sort <span class="caret"></span></button>
  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
    <li><a href="/search/all?sort=123">Last Update</a></li>
    <li><a href="/search/all?sort=321">Last Update (Reverse)</a></li>
    <li><a href="/search/all?sort=abc">Alphabetical</a></li>
    <li><a href="/search/all?sort=zyx">Alphabetical (Reverse)</a></li>
    <li><a href="/search/all?sort=newest">Newest</a></li>
    <li><a href="/search/all?sort=oldest">Oldest</a></li>
    <li><a href="/search/all?sort=popular">Popularity (Subscriptions)</a></li>
  </ul>
</div></h1>
<?php

if(!$error){
    $first = $pagemin+1;
    echo '<div class="list-group">';
    foreach($comics as $comic){
        echo '<a href="/comic/'.$comic["id"].'" class="list-group-item list-group-item-action">';
        if($comic["image"]){
            echo '<style>.comic'.$comic["id"].':before {background-image: url("/assets/usr_imgs/banners/'.$comic["image"].'");}</style>';
            echo '<div class="list-group-item-heading comic-bar comic'.$comic["id"].'"><h3>'.$comic["name"].'</h3></div>';
        }
        else{
            echo '<style>.comic'.$comic["id"].'{background: inherit;}</style>';
            echo '<div class="list-group-item-heading comic-bar comic'.$comic["id"].'"><h3 class="white-text">'.$comic["name"].'</h3></div>';
        }
        if (strlen($comic["summary"]) > 250){echo '<p class="list-group-item-text">'.truncate($comic["summary"]).'</p>';}else{echo '<p class="list-group-item-text">'.$comic["summary"].'</p>';}
        echo '<div class="row"><div class="panel-body">';

        if($comic["tags"] && count($comic["tags"]) > 0){
            $tags = implode(", ", $comic["tags"]);
            if(strlen($tags)>74){echo '<div class="col-md-9"><label>Tags: </label> '.truncate($tags,75).'</div>';}else{echo '<div class="col-md-9"><label>Tags: </label> '.$tags.'</div>';}
        }
        echo '<div class="col-md-3"><label>Status: </label> '.$comic["status"].'</div>';
        echo '</dl>';
        echo '</div></div>';
        echo '</a>
        ';
    }
    echo '</div>';
}else{
    echo '<div class="alert alert-info">
  <strong>'.$error.'</strong>
</div>';

}
if ($pagecount > 1){
?>
<ul class="pagination">
<?php for ($i = 1; $i <= $pagecount; $i++) {
 ?><li<? if ($page == $i){echo ' class="active"';} ?>><a href="/search/all?page=<?php echo $i; if ($sort){echo '&sort='.$sort;}?>"><?php echo $i; ?></a></li><?php
 }?>
</ul>
<?php } ?>
    	</div>
            <div class="hidden-xs hidden-sm col-md-3 text-center">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <?php include_once 'includes/pw/skyscraper.inc.php'; ?>
                    </div>
                </div>

            </div>
            
    	</div><!--row-->
    </div><!--panel body-->
</div> <!-- /container -->
	
	<?php require_once 'includes/footer.inc.php';?>
    <script>
    $(document).ready(function() {
        $.Fastselect.defaults = {
                    elementClass: 'fstElement',
    singleModeClass: 'fstSingleMode',
    noneSelectedClass: 'fstNoneSelected',
    multipleModeClass: 'fstMultipleMode',
    queryInputClass: 'fstQueryInput',
    queryInputExpandedClass: 'fstQueryInputExpanded',
    fakeInputClass: 'fstFakeInput',
    controlsClass: 'fstControls',
    toggleButtonClass: 'fstToggleBtn',
    activeClass: 'fstActive',
    itemSelectedClass: 'fstSelected',
    choiceItemClass: 'fstChoiceItem',
    choiceRemoveClass: 'fstChoiceRemove',
    userOptionClass: 'fstUserOption',

    resultsContClass: 'fstResults',
    resultsOpenedClass: 'fstResultsOpened',
    resultsFlippedClass: 'fstResultsFilpped',
    groupClass: 'fstGroup',
    itemClass: 'fstResultItem',
    groupTitleClass: 'fstGroupTitle',
    loadingClass: 'fstLoading',
    noResultsClass: 'fstNoResults',
    focusedItemClass: 'fstFocused',

    matcher: null,

    url: null,
    loadOnce: false,
    apiParam: 'query',
    initialValue: null,
    clearQueryOnSelect: true,
    minQueryLength: 1,
    focusFirstItem: false,
    flipOnBottom: true,
    typeTimeout: 150,
    userOptionAllowed: false,
    valueDelimiter: ',',
    maxItems: null,

    parseData: null,
    onItemSelect: null,
    onItemCreate: null,
    onMaxItemsReached: null,

    placeholder: 'Select',
    searchPlaceholder: 'Search options',
    noResultsText: 'No results',
    userOptionPrefix: 'Add '
        }
        $('.multipleSelectTags').fastselect();
        $('.multipleSelectWarnings').fastselect();
    });
    </script>
  </body>
</html>
