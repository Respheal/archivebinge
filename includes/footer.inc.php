<div class="container panel panel-default text-center" style="margin-top:-10px;padding:15px;">
    <div class="row">
        <div class="col-md-12">
            <div id="footer">
				          <p>All listed comics are the property of their respective creators.</p>
            </div>
        </div>
    </div>
</div>
<?php if ($currentPage == 'comicinfo.php'){ echo'
<script>
$( document ).ready(function() {
    $(".clickable-row").click(function() {
        window.document.location = $(this).data("href");
    });
});
</script>';}?>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<?php if ($currentPage == 'edit.php' || $currentPage == 'browse.php' || $currentPage == 'editdev.php' || $currentPage == 'search.php'){echo '<script src="/assets/js/fastselect.standalone.min.js"></script>';} ?>
