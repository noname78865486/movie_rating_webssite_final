<!--로그인한 유저만 볼 수 있는 index-->
<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 리다이렉트
    exit;
}
$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴

// 내가 작성한 후기만 가져오는 SQL 쿼리
$sql = "SELECT r.id, r.title, r.content, u.userID, r.created_at, r.user_id, r.rating
        FROM reviews r
        JOIN users u ON r.user_id = u.userID
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userID); // 로그인한 사용자의 ID를 바인딩

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($post = $result->fetch_assoc()) {
        echo "<h3>" . htmlspecialchars($post['title']) . "</h3>";
        echo "<p>" . htmlspecialchars($post['content']) . "</p>";
        echo "<p>Posted by: " . htmlspecialchars($post['userID']) . " on " . $post['created_at'] . "</p>";
        echo "<hr>";
    }
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
        <h1 style="color:#fff"><?php echo htmlspecialchars($userID); ?>님의 My Page<br></h1>
        <a style="color: white; display: block;" href="dashboard.php">🏠Home</a>
        <a style="color: white; display: block;" href="logout.php">🔓Logout</a>
        <h1 style="color:#fff, content-align: left">My Info<br></h1>

        <h1 style="color:#fff, content-align: left">My Reviews<br></h1>
            <table border="1">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>제목</th>
                        <th>감독</th>
                        <th>개봉일</th>
                        <th>장르</th>
                        <th>평점</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0) : ?>
                    <?php while ($post = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($post['id']) ?></td>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['userID']) ?></td>
                            <td><?= htmlspecialchars($post['created_at']) ?></td>
                            <td><?= htmlspecialchars($post['rating']) ?></td>
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
