<?php $currentPage = basename($_SERVER['SCRIPT_FILENAME']);

//print_r($comic_pages);

if (isset($page)) {
    if($comic_pages[$page-1]){
        $prev = $page-1;
    }
    if($comic_pages[$page+1]){
        $next = $page+1;
    }
}?>
    <!-- Static navbar -->
    <nav class="navbar navbar-default <?php if (isset($_SESSION['frame_position']) && $_SESSION['frame_position'] == "bottom") { echo "navbar-fixed-bottom";}else{echo "navbar-static-top";}?>">
      <div class="container">
          
          <div class="collapsed visible-xs-flex" aria-expanded="false" aria-controls="navbar">
            <?php if($page > 0){?><a class="navbar-brand" href="<?php echo '/reader/'.$comic.'/0'; ?>"><span class="glyphicon glyphicon-backward" aria-hidden="true" aria-label="First"></span></a><?php }else{?><a class="navbar-brand disabled" href="#"><span class="glyphicon glyphicon-backward" aria-hidden="true" aria-label="First"></span></a><?php }?>
            <?php if(isset($prev)){?><a class="navbar-brand" href="<?php echo '/reader/'.$comic.'/'.$prev; ?>"><span class="glyphicon glyphicon-play icon-flipped" aria-hidden="true" aria-label="Previous"></span></a><?php }else{?><a class="navbar-brand disabled" href="#"><span class="glyphicon glyphicon-play icon-flipped" aria-hidden="true" aria-label="Previous"></span></a><?php }?>
            <?php if (!empty($_SESSION['user_id'])){ if (!$sub_type){ echo '<form method="post">
                <button type="submit" class="navbar-brand btn-link" title="Quick Sub" name="subscribe" value="yes"><span class="glyphicon glyphicon-heart-empty"></span></button>';}else{ echo '<button type="submit" class="navbar-brand btn-link" title="Quick Un-Sub" name="subscribe" value="no"><span class="glyphicon glyphicon-heart"></span></button>';}?></form><?php }?>
            <a class="navbar-brand" href="/comic/<?php echo $comic; ?>"><span class="glyphicon glyphicon-list-alt" aria-hidden="true" aria-label="Comic Details"></span></a>
            <?php if($_SESSION['user_id']){?><a class="navbar-brand" href="/dashboard"><span class="glyphicon glyphicon-home" aria-hidden="true" aria-label="Dashboard"></span></a><?php } ?>
            <?php if(isset($next)){?><a class="navbar-brand" href="<?php echo '/reader/'.$comic.'/'.$next; ?>"><span class="glyphicon glyphicon-play" aria-hidden="true" aria-label="Next"></span></a><?php }else{?><a class="navbar-brand disabled" href="#"><span class="glyphicon glyphicon-play" aria-hidden="true" aria-label="Next"></span></a><?php }?>
            <?php if(isset($next)){?><a class="navbar-brand" href="<?php echo '/reader/'.$comic.'/'; end($comic_pages); echo key($comic_pages); ?>"><span class="glyphicon glyphicon-forward" aria-hidden="true" aria-label="Last"></span></a><?php }else{?><a class="navbar-brand disabled" href="#"><span class="glyphicon glyphicon-forward" aria-hidden="true" aria-label="Last"></span></a><?php }?>
          </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a class="navbar-brand" href="/">AB Reader</a></li>
            <li><a href="/comic/<?php echo $comic; ?>">Info</a></li>
            <li><form method="post" class="navbar-form" style="padding:15px 0px 15px 0px;margin:0;">
              <?php if (!empty($_SESSION['user_id'])){ if (!$sub_type){ echo '<button class="btn-link" type="submit" title="Quick Sub" name="subscribe" value="yes">Subscribe</button>';}else{ echo '<button class="btn-link" type="submit" title="Unsubscribe" name="subscribe" value="no">Unsubscribe</button>';}} ?>
            </form></li>
            <?php if($page > 0){?><li><a href="<?php echo '/reader/'.$comic.'/0'; ?>"><span class="glyphicon glyphicon-backward" aria-hidden="true" aria-label="First"></span></a></li><?php }else{?><li class="disabled"><a href="#"><span class="glyphicon glyphicon-backward" aria-hidden="true" aria-label="First"></span></a></li><?php }?>
            <?php if(isset($prev)){?><li><a href="<?php echo '/reader/'.$comic.'/'.$prev; ?>"><span class="glyphicon glyphicon-play icon-flipped" aria-hidden="true" aria-label="Previous"></span></a></li><?php }else{?><li class="disabled"><a href="#"><span class="glyphicon glyphicon-play icon-flipped" aria-hidden="true" aria-label="Previous"></span></a></li><?php }?>
            <li><p class="navbar-text"><?php echo $page+1; ?>/<?php end($comic_pages); echo key($comic_pages)+1; ?></p></li>
            <?php if(isset($next)){?><li><a href="<?php echo '/reader/'.$comic.'/'.$next; ?>"><span class="glyphicon glyphicon-play" aria-hidden="true" aria-label="Next"></span></a></li><?php }else{?><li class="disabled"><a href="#"><span class="glyphicon glyphicon-play" aria-hidden="true" aria-label="Next"></span></a></li><?php }?>
            <?php if(isset($next)){?><li><a href="<?php echo '/reader/'.$comic.'/'; end($comic_pages); echo key($comic_pages); ?>"><span class="glyphicon glyphicon-forward" aria-hidden="true" aria-label="Last"></span></a></li><?php }else{?><li class="disabled"><a href="#"><span class="glyphicon glyphicon-forward" aria-hidden="true" aria-label="Last"></span></a></li><?php }?>
            <?php if($_SESSION['user_id']){?><li><a href="/dashboard">Dashboard</a></li><?php } ?>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><?php if(!$_SESSION['user_id']){echo '<a href="/login">Log In</a></li>';}else{echo '<a href="/logout">Log Out</a></li>';}?>
            <li><a href="<?php if (isset($page)) {echo $comic_pages[$page];} else { echo end($comic_pages[$page]);}?>"><span class="glyphicon glyphicon-remove"></span></a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
