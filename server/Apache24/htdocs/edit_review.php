<?php
require_once 'config/db.php';

// 리뷰 ID 가져오기
$id = $_GET['id'];

// 리뷰 정보 가져오기
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $movie = $result->fetch_assoc(); // 리뷰 정보 배열로 저장
} else {
    die("리뷰를 찾을 수 없습니다.");
}

// 리뷰 수정 처리
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $genre = $_POST['genre'];
    $poster_path = $movie['poster_path']; // 기존 경로를 기본값으로 설정

    // 포스터 업로드 처리
    $upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/img/';
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $poster_tmp_name = $_FILES['poster']['tmp_name'];
        $poster_name = basename($_FILES['poster']['name']);
        $new_poster_path = $upload_dir . $poster_name;

        if (move_uploaded_file($poster_tmp_name, $new_poster_path)) {
            $poster_path = '/img/' . $poster_name; // 새 경로 저장
        } else {
            echo "<script>alert('포스터 업로드 실패.');</script>";
        }
    }

    // 데이터 유효성 검사
    if (empty($title) || empty($director) || empty($release_date) || empty($genre)) {
        echo "<script>alert('필수 항목을 모두 입력해 주세요.');</script>";
    } else {
        // 리뷰 정보 업데이트
        $updateSql = "UPDATE movies SET title = ?, director = ?, release_date = ?, genre = ?, poster_path = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sssssi', $title, $director, $release_date, $genre, $poster_path, $id);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            echo "<script>alert('리뷰 정보가 수정되었습니다.'); window.location.href = 'movie_detail.php?id=$id';</script>";
        } else {
            echo "<script>alert('수정 실패.');</script>";
        }
    }
}

$conn->close();
?>