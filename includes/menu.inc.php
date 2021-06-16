<?php $currentPage = basename($_SERVER['SCRIPT_FILENAME']);
if ($_SESSION['user_id']){
    $conn = dbConnect();

    $stmt = $conn->prepare('select count(comics.comic_id) from comics inner join user_subs on comics.comic_id = user_subs.comic_id where user_id=? and (round((length(comic_pages) - length(replace(comic_pages, ",", ""))) /length(","))) > bookmark');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($updates);
    $stmt->store_result();
    $stmt->fetch();
    
    $stmt->close();
    $conn->close();
}
?>
    <!-- Static navbar -->
    <nav class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">Archive Binge</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav"><li><a href="/search/all">Browse</a></li></ul>
		  <div class="col-sm-3 col-md-4">
            <form class="navbar-form" role="search" action="/search">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" name="search">
                </div>
            </form>
        </div>
          <ul class="nav navbar-nav navbar-right">
		      <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Site <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <?php if($_SESSION['user_id']){echo '<li';if ($currentPage == 'submit.php'){echo ' class="active"';} echo '><a href="/submit">Add Comic</a></li>';
                echo '<li'; if ($currentPage == 'profile.php'){echo ' class="active"';} echo '><a href="/profile/'.$_SESSION['user_id'].'">Profile</a></li>';
                #echo '<li'; if ($currentPage == 'friends.php'){echo ' class="active"';} echo '><a href="/friends">Friends</a></li>';
                #echo '<li'; if ($currentPage == 'email.php'){echo ' class="active"';} echo '><a href="/email">Email Digest</a></li>';
                #echo '<li><a href="/rss">Sub. RSS</a></li>';
                echo '<li role="separator" class="divider"></li>';
                } ?>
                <li<?php if ($currentPage == 'faq.php'){echo ' class="active"';} ?>><a href="/faq">Site FAQs</a></li>
                <li><a href="/blog">Dev Blog</a></li>
                <li<?php if ($currentPage == 'contact.php'){echo ' class="active"';} ?>><a href="/contact">Contact</a></li>
              </ul>
            </li>
            <?php if($_SESSION['user_id']){echo '<li'; if ($currentPage == 'dashboard.php'){echo ' class="active"';} ?>><a href="/dashboard">My Comics<?php if ($updates){?> <span class="badge"><?php echo $updates;?></span><?php }?></a></li><?php }?>            <li<?php if ($currentPage == 'login.php'){echo ' class="active"';} ?>><?php if(!$_SESSION['user_id']){echo '<a href="/login">Log In</a></li>';}else{echo '<a href="/logout">Log Out</a></li>';}?>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
