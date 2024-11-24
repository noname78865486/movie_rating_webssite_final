<?php
session_start();
require_once 'config/db.php';

$post_id = $_GET['id'];

$sql = "SELECT p.title, p.content, p.visibility, p.user_id, u.username 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Post not found.";
    exit;
}

$post = $result->fetch_assoc();

// 접근 제한 처리
if ($post['visibility'] === 'private' && $post['user_id'] !== $_SESSION['user_id']) {
    echo "You do not have permission to view this post.";
    exit;
}

echo "<h1>" . htmlspecialchars($post['title']) . "</h1>";
echo "<p>" . htmlspecialchars($post['content']) . "</p>";
echo "<p>Posted by: " . htmlspecialchars($post['username']) . "</p>";
?>
