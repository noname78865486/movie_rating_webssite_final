<?php
session_start();

// 로그인 여부 확인
$isLoggedIn = isset($_SESSION['username']); // 세션에 username이 있으면 로그인 상태
$username = $isLoggedIn ? $_SESSION['username'] : null;
?>

<!--로그인하지 않은 유저만 볼 수 있는 index-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Rating Website</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* 텍스트 및 링크 스타일 */
        h1 {
            font-size: 50px;
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
        }

        a {
            font-size: 20px;
            color: #fff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        p {
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body class="center">
    <div>
        <h1>Welcome to<br>
            🎞️ Movie Rating! 🍿<br>
        </h1>
        <hr style="width: 50%; margin: 10px auto; border: 1px solid #fff;">
        <p><a href="movie_list.php">Show Movie List📽️</a></p>
        <p><a href="reviews_board.php">Show reviews✨</a></p>
        <p><a href="login.php">Login🔒</a></p>
        <p><a href="register.php">Sign in✒️</a></p>
        <hr style="width: 50%; margin: 10px auto; border: 1px solid #fff;">
    </div>
</body>
</html>