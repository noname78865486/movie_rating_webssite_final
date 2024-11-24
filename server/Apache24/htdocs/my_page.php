<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

echo "<h1>Welcome, " . htmlspecialchars($_SESSION['username']) . "</h1>";
echo "<p>This is your personal My Page!</p>";
?>
