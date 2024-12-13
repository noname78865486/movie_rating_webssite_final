<?php
require_once 'config/db.php'; // 데이터베이스 연결 파일 포함
session_start(); // 세션 시작

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 리다이렉트
    exit;
}

$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴

// 사용자 정보 조회
$sql_user = "SELECT name, email, address, phoneNumber, identifNum FROM users WHERE userID = '$userID'"; // 사용자 ID로 사용자 정보 조회
$result_user = $conn->query($sql_user); // 쿼리 실행

if ($result_user->num_rows > 0) {
    $userInfo = $result_user->fetch_assoc(); // 사용자 정보 가져오기
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
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- 스타일시트 포함 -->
</head>
<body class="content">
    <h1 style="color:#fff;"><?php echo $userID; ?>님의 My Page</h1> <!-- 사용자 ID 표시 -->
    <a style="color: white; display: block;" href="dashboard.php">🏠Home</a>
    <a style="color: white; display: block;" href="logout.php">🔓Logout</a>

    <h2 style="color:#fff;">My Info</h2>
    <table border="1" style="color: black; width: 50%;">
        <!-- 사용자 정보 출력 -->
        <tr>
            <th>성명</th>
            <td><?php echo $userInfo['name']; ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo $userInfo['email']; ?></td>
        </tr>
        <tr>
            <th>주소</th>
            <td><?php echo $userInfo['address']; ?></td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td><?php echo $userInfo['phoneNumber']; ?></td>
        </tr>
        <tr>
            <th>주민등록번호</th>
            <td><?php echo $userInfo['identifNum']; ?></td>
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
                    WHERE r.rating_user_idNum = (SELECT id FROM users WHERE userID = '$userID') 
                    ORDER BY r.created_at DESC"; // 로그인한 사용자의 리뷰 조회
    $result_reviews = $conn->query($sql_reviews); // 쿼리 실행
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
            <!-- 리뷰가 있는 경우 -->
            <?php while ($post = $result_reviews->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $post['id']; ?></td>
                    <td><?php echo $post['review_title']; ?></td>
                    <td><?php echo $post['movie_title']; ?></td>
                    <td><?php echo $post['created_at']; ?></td>
                    <td><?php echo $post['rating']; ?></td>
                    <td><a style="color: blue;" href="review_detail.php?id=<?php echo $post['id']; ?>">상세보기</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- 리뷰가 없는 경우 -->
            <tr>
                <td colspan="6" class="no-data">등록된 리뷰가 없습니다.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
