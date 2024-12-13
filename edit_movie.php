<?php
require_once 'config/db.php'; // 데이터베이스 연결
session_start(); // 세션 시작

// 영화 ID 가져오기 (입력 검증 없이 사용, SQL 인젝션 가능)
$id = $_GET['id'];

// 영화 정보 가져오기 (취약한 쿼리 구조, SQL 인젝션 가능)
$sql = "SELECT * FROM movies WHERE id = $id"; // 쿼리에 사용자 입력값 직접 삽입
$result = $conn->query($sql); // SQL 실행

if ($result->num_rows === 1) {
    $movie = $result->fetch_assoc(); // 영화 정보 배열로 저장
} else {
    die("영화를 찾을 수 없습니다."); // 기본 에러 메시지로 정보 누출 가능
}

// 영화 수정 처리
if (isset($_POST['submit'])) {
    // 사용자 입력값 받기 (입력 검증 부족)
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

        // 파일 업로드 수행 (취약한 경로 처리로 경로 조작 가능)
        if (move_uploaded_file($poster_tmp_name, $new_poster_path)) {
            $poster_path = '/img/' . $poster_name; // 새 경로 저장
        } else {
            echo "<script>alert('포스터 업로드 실패.');</script>";
        }
    }

    // 데이터 유효성 검사 미흡 (날짜 형식 등 추가 검증 부족)
    if (empty($title) || empty($director) || empty($release_date) || empty($genre)) {
        echo "<script>alert('필수 항목을 모두 입력해 주세요.');</script>";
    } else {
        // 영화 정보 업데이트 (SQL 인젝션 가능)
        $updateSql = "UPDATE movies SET title = '$title', director = '$director', release_date = '$release_date', genre = '$genre', poster_path = '$poster_path' WHERE id = $id";
        if ($conn->query($updateSql)) {
            // 성공 시 리디렉트 (취약한 리디렉션 처리)
            echo "<script>alert('영화 정보가 수정되었습니다.'); window.location.href = 'movie_detail.php?id=$id';</script>";
        } else {
            echo "<script>alert('수정 실패.');</script>";
        }
    }
}

$conn->close(); // 연결 종료
?>
