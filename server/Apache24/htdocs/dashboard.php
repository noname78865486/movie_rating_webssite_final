<!--로그인한 유저만 볼 수 있는 dashboard-->
<?php
session_start(); // 세션 시작
require_once 'config/db.php'; //DB연결

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 리다이렉트
    exit;
}
$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴

// 로그인한 사용자 정보에서 role 가져오기
$sql = "SELECT role FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userID);
$stmt->execute();
$userResult = $stmt->get_result();
if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $isAdmin = $userData['role'] === 'admin'; // admin 여부
} else {
    $isAdmin = false;
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
        <h1 style="color:#fff">안녕하세요,<br></h1>
        <h1 style="color:#fff"><?php echo htmlspecialchars($userID); ?>님!<br></h1>
        <hr style="width: 50%; margin: 10px auto; border: 1px solid #fff;">
        <p style="font-size: 15px;">
            <?php if ($isAdmin): ?>
                    <a style="color: white; margin-bottom: 10px; display: block;" href="admin_board.php">⚠️Admin Board</a>
            <?php endif; ?>
            <a style="color: white; margin-bottom: 10px; display: block;" href="reviews_board.php">⭐Rate Movies</a>
            <a style="color: white; margin-bottom: 10px; display: block;" href="movie_list.php">🎞️Show Movies</a>
            <a style="color: white; margin-bottom: 10px; display: block;" href="add_movie.php">🆕Add a New Movie</a>
            <a style="color: white; margin-bottom: 10px; display: block;" href="my_page.php">🔒my page</a>
            <a style="color: white; display: block;" href="logout.php">🔓Logout</a>
        </p>
        <hr style="width: 50%; margin: 10px auto; border: 1px solid #fff;">
</body>
</html>
