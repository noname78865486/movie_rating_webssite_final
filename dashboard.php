<!--로그인한 유저만 볼 수 있는 dashboard-->
<?php
session_start(); // 세션 시작
require_once 'config/db.php'; // DB 연결

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    // 세션에 userID가 없으면 로그인 페이지로 리다이렉트
    header("Location: login.php"); 
    exit; // 스크립트 종료
}

// 세션에서 로그인한 유저의 ID 가져오기
$userID = $_SESSION['userID'];

// 로그인한 사용자 정보에서 role 가져오기
$sql = "SELECT role FROM users WHERE userID = '$userID'"; // 사용자 ID를 기반으로 역할(role)을 가져오는 SQL
$result = $conn->query($sql); // 직접 쿼리를 실행 (SQL 인젝션에 취약)

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc(); // 결과를 배열로 가져옴
    $isAdmin = $userData['role'] === 'admin'; // 역할이 admin인지 확인
} else {
    $isAdmin = false; // role 정보를 찾을 수 없으면 admin이 아님
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Rating Website</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="content">
        <!-- 사용자 인사말 표시 -->
        <h1 style="color:#fff">안녕하세요,<br></h1>
        <!-- XSS 공격에 취약: htmlspecialchars()를 제거 -->
        <h1 style="color:#fff"><?php echo $userID; ?>님!<br></h1>
        <hr style="width: 50%; margin: 10px auto; border: 1px solid #fff;">
        <p style="font-size: 15px;">
            <!-- Admin 사용자만 Admin Board 링크를 볼 수 있음 -->
            <?php if ($isAdmin): ?>
                    <a style="color: white; margin-bottom: 10px; display: block;" href="admin_board.php">⚠️Admin Board</a>
            <?php endif; ?>
            <!-- 모든 사용자가 접근 가능한 메뉴 -->
            <a style="color: white; margin-bottom: 10px; display: block;" href="reviews_board.php">⭐Rate Movies</a>
            <a style="color: white; margin-bottom: 10px; display: block;" href="movie_list.php">🎞️Show Movies</a>
            <a style="color: white; margin-bottom: 10px; display: block;" href="add_movie.php">🆕Add a New Movie</a>
            <a style="color: white; margin-bottom: 10px; display: block;" href="my_page.php">🔒my page</a>
            <a style="color: white; display: block;" href="logout.php">🔓Logout</a>
        </p>
        <hr style="width: 50%; margin: 10px auto; border: 1px solid #fff;">
</body>
</html>
