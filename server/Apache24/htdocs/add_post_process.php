<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$title = $_POST['title'];
$content = $_POST['content'];
$visibility = $_POST['visibility'];
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO posts (user_id, title, content, visibility, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $user_id, $title, $content, $visibility);
$stmt->execute();

header("Location: my_posts.php");
exit;
?>
