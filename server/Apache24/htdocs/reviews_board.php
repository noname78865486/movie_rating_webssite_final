<?php
require_once 'config/db.php';

// Public 후기만 가져오기
$sql = "SELECT p.title, p.content, u.username, p.created_at 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.visibility = 'public' 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($post = $result->fetch_assoc()) {
        echo "<h3>" . htmlspecialchars($post['title']) . "</h3>";
        echo "<p>" . htmlspecialchars($post['content']) . "</p>";
        echo "<p>Posted by: " . htmlspecialchars($post['username']) . " on " . $post['created_at'] . "</p>";
        echo "<hr>";
    }
} else {
    echo "No reviews found.";
}
?>
