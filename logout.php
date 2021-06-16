<?php
ob_start();
if (!isset($_SESSION))
  {
    session_start();
  }

require_once("includes/conf.inc.php");
$conn = dbConnect();
$stmt = $conn->prepare("delete from user_cookies where user_id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->close();
$conn->close();

unset($_SESSION['user_id']);
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-86400, '/');
}
if (isset($_COOKIE['abUser'])) {
    setcookie("abUser", '', time()-86400, '/');
}
session_destroy();

header("Location:/");
?>
