<?php require_once("includes/header.inc.php");
require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
$conn = dbConnect();
if($_GET['page']){
    $page = mysqli_real_escape_string($conn, filter_var($_GET['page'],FILTER_SANITIZE_STRING));
    $page = (int)$page;
}else{$page = 1;}

$querystring = htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES);
$querystring = preg_split("/&amp;page=[0-9]+|&amp;sort=[a-z]+/", $querystring);
$querystring = join($querystring);

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

//build query

if((isset($_GET['advanced']) && count($_GET)>1) || $_GET['search']){
    $modifier = array(">", "<", "=");
    if($_GET['search']){
        
        //if plain search
        $search = mysqli_real_escape_string($conn, filter_var($_GET['search'],FILTER_SANITIZE_STRING));
        $searchsql = "%".$search."%";
        $stmt = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS comic_id, comic_name, comic_image, comic_summary, comic_status from comics where comic_name like ? order by last_update desc limit ?,30");
        $stmt->bind_param('si', $searchsql, $pagemin);
        
    }elseif(isset($_GET['advanced'])){
        
        //if advanced search
        //set main query stuff
        $startquery = "SELECT SQL_CALC_FOUND_ROWS distinct comics.comic_id, comic_name, comic_image, comic_summary, comic_status FROM comics";
        if($_GET['sort'] && $_GET['sort'] == "popular"){$startquery = $startquery." inner join user_subs on comics.comic_id = user_subs.comic_id";}
        $middlequery = "";
        $joins = "";
        //$endquery = " group by comics.comic_id";
        $endquery = "";
        if($_GET['sort']){
            if ($_GET['sort'] == "abc"){$endquery = " order by comic_name asc"; $sort="abc";}
            elseif ($_GET['sort'] == "zyx"){$endquery = " order by comic_name desc"; $sort="zyx";}
            elseif ($_GET['sort'] == "123"){$endquery = " order by last_update desc"; $sort="123";}
            elseif ($_GET['sort'] == "321"){$endquery = " order by last_update asc"; $sort="321";}
            elseif ($_GET['sort'] == "newest"){$endquery = " order by comic_id desc"; $sort="newest";}
            elseif ($_GET['sort'] == "oldest"){$endquery = " order by comic_id asc"; $sort="oldest";}
            elseif ($_GET['sort'] == "popular"){$endquery = " order by (SELECT count(comic_id) as total FROM user_subs where comic_id=comics.comic_id) desc"; $sort="popular";}
        }else{$endquery = " order by last_update desc";}
        $endquery = $endquery." limit ?,30";
        $params = array("");

        //name
        if($_GET['name']){
            $name = mysqli_real_escape_string($conn, filter_var($_GET['name'],FILTER_SANITIZE_STRING));
            $params[0] = $params[0]."s";
            $params[] = "%".$name."%";
            $addsearch = " comic_name like ?";
            $middlequery = $middlequery.$addsearch;
        }
        
        if($_GET['creator']){
            $creator = mysqli_real_escape_string($conn, filter_var($_GET['creator'],FILTER_SANITIZE_STRING));
            $creatorquery = "%".$creator."%";

            $stmt_sub = $conn->prepare("SELECT user_id from users where user_name like ?");
            $stmt_sub->bind_param('s', $creatorquery);
            $stmt_sub->execute();
            $result = $stmt_sub->get_result();
            if ($result->num_rows > 0) {
            // output data of each row
                while($row = $result->fetch_assoc()) {
                    $creators[] = $row["user_id"];
                }
                
                $owners = implode(",", $creators);

                $joins = $joins." left join comic_owners on comics.comic_id=comic_owners.comic_id";
                $addsearch = " user_id in (".$owners.")";

                if (strlen($middlequery) < 1){$middlequery = $middlequery.$addsearch;}else{$middlequery = $middlequery." and".$addsearch;}
                
            }
        }
        
        if($_GET['status']){
            $params[] = mysqli_real_escape_string($conn, filter_var($_GET['status'],FILTER_SANITIZE_STRING));
			$status = mysqli_real_escape_string($conn, filter_var($_GET['status'],FILTER_SANITIZE_STRING));
            $params[0] = $params[0]."s";
            $addsearch = " comic_status = ?";
            if (strlen($middlequery) < 1){$middlequery = $middlequery.$addsearch;}else{$middlequery = $middlequery." and".$addsearch;}
        }
        
        if($_GET['yestags']){
            //convert tags into their respective ids
            foreach ($_GET['yestags'] as $tag){
                $clean_tags[] = mysqli_real_escape_string($conn, filter_var($tag,FILTER_SANITIZE_STRING));
            }
            
            $stmt_sub = $conn->prepare("select tag_id from tags where tag_name = ?");
            $stmt_sub->bind_param('s', $tag);
            foreach ($clean_tags as $tag){
                $stmt_sub->execute();
                $stmt_sub->bind_result($tag_ids[]);
                $stmt_sub->store_result();
                $stmt_sub->fetch();
            } 
            
            $tags = implode(",", $tag_ids);
            $addsearch = " comics.comic_id in (SELECT comics.comic_id FROM comics left join comic_tags on comics.comic_id=comic_tags.comic_id where tag_id in (".$tags.") group by comics.comic_id having count(*) = ".count($tag_ids).")";
            if (strlen($middlequery) < 1){$middlequery = $middlequery.$addsearch;}else{$middlequery = $middlequery." and".$addsearch;}
        }
        
        if($_GET['notags']){
            $nope = [];
            //convert tags into their respective ids
            foreach ($_GET['notags'] as $tag){
                $no_clean_tags[] = mysqli_real_escape_string($conn, filter_var($tag,FILTER_SANITIZE_STRING));
            }
            
            $stmt_sub = $conn->prepare("select tag_id from tags where tag_name = ?");
            $stmt_sub->bind_param('s', $tag);
            foreach ($no_clean_tags as $tag){
                $stmt_sub->execute();
                $stmt_sub->bind_result($no_tag_ids[]);
                $stmt_sub->store_result();
                $stmt_sub->fetch();
            }

            foreach ($no_tag_ids as $exclude){
                $nope[] = "tag_id = ".$exclude;
            }
            $exclusionlist = implode(" or ", $nope);

            $addsearch = " comics.comic_id not in (SELECT comics.comic_id FROM comics left join comic_tags on comics.comic_id=comic_tags.comic_id where ".$exclusionlist.")";
            if (strlen($middlequery) < 1){$middlequery = $middlequery.$addsearch;}else{$middlequery = $middlequery." and".$addsearch;}
        }
        
        if($_GET['warnings']){
            $joins = $joins." left join comic_warnings on comics.comic_id=comic_warnings.comic_id";
            //convert tags into their respective ids
            foreach ($_GET['warnings'] as $warning){
                $clean_warnings[] = mysqli_real_escape_string($conn, filter_var($warning,FILTER_SANITIZE_STRING));
            }
            
            $stmt_sub = $conn->prepare("select warning_id from warnings where warning_name = ?");
            $stmt_sub->bind_param('s', $warning);
            foreach ($clean_warnings as $warning){
                $stmt_sub->execute();
                $stmt_sub->bind_result($warning_ids[]);
                $stmt_sub->store_result();
                $stmt_sub->fetch();
            } 
            
            $warnings = implode(",", $warning_ids);
            $addsearch = " comics.comic_id not in (SELECT comics.comic_id FROM comics left join comic_warnings on comics.comic_id=comic_warnings.comic_id where warning_id in (".$warnings.") group by comics.comic_id having count(*) = ".count($warning_ids).")";
            if (strlen($middlequery) < 1){$middlequery = $middlequery.$addsearch;}else{$middlequery = $middlequery." and".$addsearch;}
        }
        
        if($_GET['pagecount']){
            if(in_array($_GET['pagesmods'],$modifier)){
                if ($_GET['pagesmods'] == ">"){$mod = ">";}
                elseif ($_GET['pagesmods'] == "<"){$mod = "<";}
                elseif ($_GET['pagesmods'] == "="){$mod = "=";}
                else {$mod = ">";}
            } else{$mod = ">";}
            $params[] = mysqli_real_escape_string($conn, filter_var($_GET['pagecount'],FILTER_SANITIZE_NUMBER_INT));
			$comicpages = mysqli_real_escape_string($conn, filter_var($_GET['pagecount'],FILTER_SANITIZE_NUMBER_INT));
            $params[0] = $params[0]."i";
            $addsearch = ' (round(
    (
        length(comic_pages) - length(replace(comic_pages, ",", ""))
        ) /length(",")+1
    )) '.$mod.' ?';
            if (strlen($middlequery) < 1){$middlequery = $middlequery.$addsearch;}else{$middlequery = $middlequery." and".$addsearch;}
        }
        
        $params[0] = $params[0]."i";
        $params[] = $pagemin;
        //print_r($params);
        
        //cap the query
        $hellquery = $startquery.$joins." where".$middlequery.$endquery;
        //echo $hellquery;
        //prep that sucker
        $stmt = $conn->prepare($hellquery);
        //bind params
        $tmp = array();
        foreach($params as $key => $value) $tmp[$key] = &$params[$key];
        // now us the new array
        call_user_func_array(array($stmt, 'bind_param'), $tmp);

    }else {
        $error = "0 results";
    }

    //find comics that match criteria
    if($stmt->execute()){
        $count = 0;
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $count++;
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
}else {
    $error = "0 results";
}

    
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
                    <div id="pagelist" class="panel-collapse collapse<?php //if($_GET['advanced']){echo 'in';} ?>">
                        <div class="panel-body">
                        <p>All fields optional.</p>

                        <form onsubmit="disableEmptyInputs(this)" method="get" action="">
                            <div class="form-group">
                                <label for="name">Comic Name</label>
                                <input type="text" class="form-control" name="name"<?php if ($name){echo "value=\"$name\"";} ?>>
                            </div>
                            <div class="form-group">
                                <label for="creator">Creator</label>
                                <input type="text" class="form-control" name="creator"<?php if ($creator){echo "value=\"$creator\"";} ?>>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status">
                                    <option></option>
                                    <option value="Ongoing"<?php if ($status && $status=="Ongoing"){echo " selected";} ?>>Ongoing</option>
                                    <option value="On Hiatus"<?php if ($status && $status=="On Hiatus"){echo " selected";} ?>>Hiatus</option>
                                    <option value="Completed"<?php if ($status && $status=="Completed"){echo " selected";} ?>>Completed</option>
                                    <option value="Cancelled"<?php if ($status && $status=="Cancelled"){echo " selected";} ?>>Cancelled</option>                                    
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="yestags">Include Tags</label>
                                <select class="multipleSelectTags form-control" multiple name="yestags[]">
                                <?php foreach ($alltags as $tag) {
                                    echo '<option';
                                    if ($clean_tags && in_array($tag, $clean_tags)){echo " selected";}
                                    echo ' value="'.$tag.'">'.$tag.'</option>
                                ';}?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="notags">Exclude Tags</label>
                                <select class="multipleSelectTags form-control" multiple name="notags[]">
                                <?php foreach ($alltags as $tag) {
                                    echo '<option';
                                    if ($no_clean_tags && in_array($tag, $no_clean_tags)){echo " selected";}
                                    echo ' value="'.$tag.'">'.$tag.'</option>
                                ';}?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="warnings">Exclude Warnings</label>
                                <select class="multipleSelectWarnings form-control" multiple name="warnings[]">
                                <?php foreach ($allwarnings as $warning) {
                                    echo '<option';
									if ($clean_warnings && in_array($warning, $clean_warnings)){echo " selected";}
									echo ' value="'.$warning.'">'.$warning.'</option>
                                ';}?>
                                </select>
                                <p class="help-block">Not all comics will have content labeled appropriately. Proceed with caution.</p>
                            </div>
                            <div class="form-group">
                                <label for="pagecount">Pages</label>
                                    <div class="input-group">
                                      <div class="input-group-addon">
                                        <select name="pagesmods">
                                          <option<?php if($comicpages && $mod && $mod == ">"){echo " selected";} ?>>&gt;</option>
                                          <option<?php if($comicpages && $mod && $mod == "<"){echo " selected";} ?>>&lt;</option>
                                          <option<?php if($comicpages && $mod && $mod == "="){echo " selected";} ?>>=</option>
                                        </select>
                                      </div>
                                      <input class="form-control" type="number" min="0" placeholder="Number of pages" name="pagecount"<?php if($comicpages){echo " value=\"$comicpages\"";} ?>>
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
    <li><a href="search?<?php echo $querystring; ?>&sort=123">Last Update</a></li>
    <li><a href="search?<?php echo $querystring; ?>&sort=321">Last Update (Reverse)</a></li>
    <li><a href="search?<?php echo $querystring; ?>&sort=abc">Alphabetical</a></li>
    <li><a href="search?<?php echo $querystring; ?>&sort=zyx">Alphabetical (Reverse)</a></li>
    <li><a href="search?<?php echo $querystring; ?>&sort=newest">Newest</a></li>
    <li><a href="search?<?php echo $querystring; ?>&sort=oldest">Oldest</a></li>
    <li><a href="search?<?php echo $querystring; ?>&sort=popular">Popularity (Subscriptions)</a></li>
  </ul>
</div></h1>
<?php

if(!$error){
    $first = $pagemin+1;
    echo '<div class="alert alert-success">
      <strong>Showing results '.$first.' - ';
    if ($pagemax <= $count){echo $pagemax;}else{echo $count;}
    echo '.</strong>
    </div>';
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
 ?><li<? if ($page == $i){echo ' class="active"';} ?>><a href="/search?<?php echo $querystring; ?><?php if ($sort){ echo "&sort=".$sort;}?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php
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
