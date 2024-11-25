<?php
require_once 'config/db.php';

$id = $_GET['id'];

// 영화 정보 가져오기
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $movie = $result->fetch_assoc();
} else {
    die("영화를 찾을 수 없습니다.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>영화 상세 정보</title>
</head>
<body>
    <h1><?= htmlspecialchars($movie['title']) ?></h1>
    <p>감독: <?= htmlspecialchars($movie['director']) ?></p>
    <p>개봉연도: <?= htmlspecialchars($movie['release_date']) ?></p>
    <p>장르: <?= htmlspecialchars($movie['genre']) ?></p>
    <p>평점: <?= htmlspecialchars($movie['rating']) ?>/10</p>
    <?php if ($movie['poster_path']): ?>
        <img src="<?= $movie['poster_path'] ?>" alt="포스터" style="max-width: 300px;">
    <?php else: ?>
        <p>포스터 이미지가 없습니다.</p>
    <?php endif; ?>
</body>
</html>
