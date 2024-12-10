<?php
require_once 'config/db.php';

// 영화 ID 가져오기
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

// 영화 수정 처리
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $genre = $_POST['genre'];
    $rating = $_POST['rating'];
    $poster_path = $_POST['poster_path'];

    // 데이터 유효성 검사 (예: 제목, 감독 필수)
    if (empty($title) || empty($director) || empty($release_date) || empty($genre)) {
        echo "<script>alert('필수 항목을 모두 입력해 주세요.');</script>";
    } else {
        // 영화 정보 업데이트
        $updateSql = "UPDATE movies SET title = ?, director = ?, release_date = ?, genre = ?, rating = ?, poster_path = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('ssssisi', $title, $director, $release_date, $genre, $rating, $poster_path, $id);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            echo "<script>alert('영화 정보가 수정되었습니다.'); window.location.href = 'movie_detail.php?id=$id';</script>";
        } else {
            echo "<script>alert('수정 실패.');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 수정</title>
    <style>
        body {
            line-height: 1.6;
            margin: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 400px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>영화 수정</h2>
        <form method="POST" action="">
            <label for="title">영화 제목</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required>

            <label for="director">감독</label>
            <input type="text" id="director" name="director" value="<?= htmlspecialchars($movie['director']) ?>" required>

            <label for="release_date">개봉연도</label>
            <input type="text" id="release_date" name="release_date" value="<?= htmlspecialchars($movie['release_date']) ?>" required>

            <label for="genre">장르</label>
            <input type="text" id="genre" name="genre" value="<?= htmlspecialchars($movie['genre']) ?>" required>

            <label for="rating">평균 평점 (0-10)</label>
            <input type="number" id="rating" name="rating" value="<?= htmlspecialchars($movie['rating']) ?>" min="0" max="10" step="0.1" re
