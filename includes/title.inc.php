<?php
$title = basename($_SERVER['SCRIPT_FILENAME'],'.php');
$title = str_replace('_', ' ', $title);
if ($title == 'index'){$title = 'home';}
if($comic_name){$title = $comic_name;}
if ($title == 'faq'){$title = 'FAQ';}
echo "Archive Binge - ".ucfirst($title);?>
