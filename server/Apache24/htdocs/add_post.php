<?php
session_start(); // 세션 시작

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 리다이렉트
    exit;
}
$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <title>리뷰 등록</title>
    <style> body{height=100%;} </style>
</head>
<body>
    <nav>
        <a href="dashboard.php">🏠 home</a><br>
        <a href="movie_list.php">🎞️ 영화 목록</a>
    </nav>
    <h2>리뷰 등록</h2>
<form action="add_post_process.php" method="POST">
    <!--리뷰 작성 부분-->
    <label for="title">Title:</label>
    <input type="text" name="title" required><br>

    <label for="content">Content:</label>
    <textarea name="content" required></textarea><br>

    <!--공개/비공개 여부 선택 가능-->
    <label for="visibility">Visibility:</label>
    <select name="visibility">
        <option value="public">Public</option>
        <option value="private">Private</option>
    </select><br>

    <button type="submit">Submit</button>
</form>
