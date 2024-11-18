<?php
// add_movie_process.php
require_once 'config/db.php'; // DB 연결 파일

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $rating = $_POST['rating'];

    // 데이터베이스에 저장
    $sql = "INSERT INTO movies (title, rating) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $title, $rating);

    if ($stmt->execute()) {
        echo "영화가 등록되었습니다.";
    } else {
        echo "오류: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>