<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 리다이렉트
    exit;
}

$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴

// 사용자 정보 조회
$sql_user = "SELECT name, email, address, phoneNumber, identifNum FROM users WHERE userID = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('s', $userID);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $userInfo = $result_user->fetch_assoc();
} else {
    // 유저를 찾을 수 없으면 에러 처리
    echo "User not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Page</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="content">
    <h1 style="color:#fff;"><?= htmlspecialchars($userID) ?>님의 My Page</h1>
    <a style="color: white; display: block;" href="dashboard.php">🏠Home</a>
    <a style="color: white; display: block;" href="logout.php">🔓Logout</a>

    <h2 style="color:#fff;">My Info</h2>
    <table border="1" style="color: black; width: 50%;">
        <tr>
            <th>성명</th>
            <td><?= htmlspecialchars($userInfo['name']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($userInfo['email']) ?></td>
        </tr>
        <tr>
            <th>주소</th>
            <td><?= htmlspecialchars($userInfo['address']) ?></td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td><?= htmlspecialchars($userInfo['phoneNumber']) ?></td>
        </tr>
        <tr>
            <th>주민등록번호</th>
            <td><?= htmlspecialchars($userInfo['identifNum']) ?></td>
        </tr>
    </table>
    <br>
    <a href="edit_userinfo.php" style="color: white; text-decoration: none; padding: 10px 20px; background-color: blue; border-radius: 5px;">정보 수정</a>

    <h2 style="color:#fff;">My Reviews</h2>
    <?php
    // 리뷰 조회
    $sql_reviews = "SELECT r.id, r.movie_id, m.title AS movie_title, r.title AS review_title, r.rating, r.created_at 
                    FROM reviews r
                    JOIN movies m ON r.movie_id = m.id
                    WHERE r.rating_user_idNum = (SELECT id FROM users WHERE userID = ?) 
                    ORDER BY r.created_at DESC";
    $stmt_reviews = $conn->prepare($sql_reviews);
    $stmt_reviews->bind_param('s', $userID);
    $stmt_reviews->execute();
    $result_reviews = $stmt_reviews->get_result();
    ?>

    <table border="1">
        <thead>
            <tr>
                <th>No.</th>
                <th>제목</th>
                <th>영화 제목</th>
                <th>작성일자</th>
                <th>평점</th>
                <th>상세보기</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result_reviews->num_rows > 0): ?>
            <?php while ($post = $result_reviews->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($post['id']) ?></td>
                    <td><?= htmlspecialchars($post['review_title']) ?></td>
                    <td><?= htmlspecialchars($post['movie_title']) ?></td>
                    <td><?= htmlspecialchars($post['created_at']) ?></td>
                    <td><?= htmlspecialchars($post['rating']) ?></td>
                    <td><a style="color: blue;" href="review_detail.php?id=<?= htmlspecialchars($post['id']) ?>">상세보기</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="no-data">등록된 리뷰가 없습니다.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>