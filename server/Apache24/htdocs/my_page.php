<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 리다이렉트
    exit;
}

$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴

// 1. userID를 기반으로 users 테이블에서 id(A)를 조회
$sql_user = "SELECT id FROM users WHERE userID = '$userID'"; 
$result_user = $conn->query($sql_user);

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $user_id = $row_user['id']; // 유저의 id(A)를 가져옵니다.

    // 2. A와 동일한 rating_user_idNum을 reviews 테이블에서 조회
    $sql_reviews = "SELECT r.id, r.movie_id, m.title AS movie_title, r.rating_user_idNum, r.title AS review_title, r.content, r.rating, r.visibility, r.created_at, r.file_path, u.userID 
                    FROM reviews r
                    JOIN users u ON r.rating_user_idNum = u.id
                    JOIN movies m ON r.movie_id = m.id
                    WHERE r.rating_user_idNum = '$user_id'
                    ORDER BY r.created_at DESC";

    $result_reviews = $conn->query($sql_reviews);
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
    <title>Movie Rating Website</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="content">
        <h1 style="color:#fff"><?php echo $userID ?>님의 My Page<br></h1>
        <a style="color: white; display: block;" href="dashboard.php">🏠Home</a>
        <a style="color: white; display: block;" href="logout.php">🔓Logout</a>
        <h1 style="color:#fff, content-align: left">My Info<br></h1>

        <h1 style="color:#fff, content-align: left">My Reviews<br></h1>
            <table border="1">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>제목</th>
                        <th>작성자</th>
                        <th>작성일자</th>
                        <th>평점</th>
                        <th>상세보기</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result_reviews->num_rows > 0) : ?>
                    <?php while ($post = $result_reviews->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $post['id'] ?></td>
                            <td><?= $post['review_title'] ?></td>
                            <td><?= $post['userID'] ?></td>
                            <td><?= $post['created_at'] ?></td>
                            <td><?= $post['rating'] ?></td>
                            <td><a style="color: blue;" href="review_detail.php?id=' . $reviews['id'] . '">상세보기</a></td>
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
