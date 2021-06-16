<?php
require_once("includes/header.inc.php");
//this page -- lol what's nested indents? Will make prettier later

$conn = dbConnect();

//Get tag list: $comic_tags[tag_name]
//$sql = "SELECT tag_name from tags where restricted=0 order by tag_name";
$sql = "select tag_name, count(comic_tags.comic_id) as tag_freq from tags left join comic_tags on tags.tag_id = comic_tags.tag_id group by tags.tag_name order by tag_name;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $i = 0;
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_tags[$i]['name'] = $row["tag_name"];
        $comic_tags[$i]['usage'] = $row["tag_freq"];
        $i++;
    }
} else {
    echo "0 results";
}

$conn->close();

require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
        <div class="row">
    	
<h1>Tags</h1>
<h2>Tags in Alphabetical Order:</h2>
<div class="col-md-12">
<div id="alphabet-list">
<div class="row">
<ul class="list-unstyled">
<?php

$previous = null;
if(is_numeric(substr($comic_tags[0]['name'], 0, 1))){echo'  <li class="chunk"><h3>#</h3>
    <ul class="list-unstyled">
';}
foreach($comic_tags as $tag) {
    $firstLetter = substr($tag['name'], 0, 1);
    if($previous !== $firstLetter && !is_numeric($firstLetter)){
        echo '    </ul>
  </li>
  <li class="chunk"><h3>'.$firstLetter.'</h3>
    <ul class="list-unstyled">
';
    }
    echo '      <li><a href="search.php?yestags%5B%5D='.str_replace(" ","+",$tag['name']).'&advanced=True" title="'.$tag['usage'].' comic';
    if($tag['usage'] != 1){ echo 's are';}else{echo ' is';}
    echo ' tagged as '.$tag['name'].'">'.$tag['name'].'</a>';
    if ($tag['usage'] > 1000){echo ' <span class="badge">1000+</span></li>';}
    elseif ($tag['usage'] > 0){echo ' <span class="badge">'.$tag['usage'].'</span></li>';}
    else{echo '</li>';}

    $previous = $firstLetter;
    if($previous !== $firstLetter){
        echo '</ul></li></ul>';
    }
}?>
</ul>
</div><!--row-->
</div><!--alphabet list-->
</div><!--col-->
    	</div><!--row-->
    </div><!--panel body-->
</div> <!-- /container -->
	
	<?php require_once 'includes/footer.inc.php';?>
  </body>
</html>
