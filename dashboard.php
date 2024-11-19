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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Rating Website</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
        <h1 style="color:#fff">안녕하세요</h1>
        <h1 style="color:#fff"><?php echo htmlspecialchars($userID); ?>님!</h1>
        <a style="font-size:10px; color:white">______________________________</a>
        <p style="font-size: 15px;">
            <a style="color: white; margin-bottom: 10px; display: block;" href="movie_list.php">⭐Rate Movies</a>
            <a style="color: white; margin-bottom: 10px; display: block;" href="add_movie.php">🆕Add a New Movie</a>
            <a style="color: white; display: block;" href="logout.php">🔓Logout</a>
        </p>
        <a style="font-size: 10px; color: white;">______________________________</a>
</body>
</html>
