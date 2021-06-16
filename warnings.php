<?php
require_once("includes/header.inc.php");

$conn = dbConnect();

//Get tag list: $comic_tags[tag_name]
$sql = "select warning_name, warning_category from warnings ORDER BY `warning_name` ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $i = 0;
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $comic_warnings[$i]['name'] = $row["warning_name"];
        $comic_warnings[$i]['category'] = $row["warning_category"];
        $i++;
    }
} else {
    echo "0 results";
}

$conn->close();

$categories = array("Adult Themes", "Content", "Language", "Nudity/Sex", "Violence");

require_once("includes/meta.inc.php");
require_once("includes/css.inc.php");
?>
  </head>
  <body>

<?php require_once 'includes/menu.inc.php';?>

<div class="container panel panel-default">
    <div class="panel-body">
        <div class="row">
    	
<h1>Warnings</h1>
<h2>Warnings in Alphabetical Order:</h2>
<div class="col-md-12">
    <div id="alphabet-list">
        <div class="row">
            <ul class="list-unstyled">
            <?php
            foreach($comic_warnings as $warning) {
                echo '      <li>'.$warning['name'].'</li>';
            }?>
            </ul>
        </div><!--row-->
    </div><!--alphabet list-->
</div><!--col-->

<div style="margin-top:50px;">&nbsp;</div>

<h2>Warnings by Type:</h2>
<div class="col-md-12">
<div id="alphabet-list">
<div class="row">
<ul class="list-unstyled">

<?php
foreach($categories as $category){
    echo '      <li><h3>'.$category.'</h3></li>';
    foreach($comic_warnings as $warning) {
        if ($warning['category'] == $category){
            echo '      <li>'.$warning['name'].'</li>';
        }
    }
}

?>

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
