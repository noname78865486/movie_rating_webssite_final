<?php
require_once 'config/db.php'; // 데이터베이스 연결
session_start(); // 세션 시작

// 리뷰 ID 가져오기 (입력 검증 없이 GET 파라미터 사용으로 SQL 인젝션 가능성 존재)
$id = $_GET['id'];

// 리뷰 정보 가져오기 (취약한 SQL 쿼리 구조)
$sql = "SELECT * FROM movies WHERE id = $id"; // 사용자 입력값 직접 삽입
$result = $conn->query($sql); // SQL 실행

if ($result->num_rows === 1) {
    $movie = $result->fetch_assoc(); // 리뷰 정보를 배열로 저장
} else {
    die("리뷰를 찾을 수 없습니다."); // 에러 메시지를 통해 내부 시스템 정보를 누출
}

// 리뷰 수정 처리
if (isset($_POST['submit'])) {
    // 사용자 입력값 받기 (입력값 검증 없이 사용)
    $title = $_POST['title'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $genre = $_POST['genre'];
    $poster_path = $movie['poster_path']; // 기존 경로 기본값 설정

    // 포스터 업로드 처리 (취약한 파일 업로드 처리)
    $upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/img/';
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $poster_tmp_name = $_FILES['poster']['tmp_name'];
        $poster_name = basename($_FILES['poster']['name']); // 파일 이름을 그대로 사용
        $new_poster_path = $upload_dir . $poster_name;

        // 파일 업로드 수행
        if (move_uploaded_file($poster_tmp_name, $new_poster_path)) {
            $poster_path = '/img/' . $poster_name; // 새로운 경로 저장
        } else {
            echo "<script>alert('포스터 업로드 실패.');</script>"; // 에러 메시지를 통해 사용자에게 노출
        }
    }

    // 데이터 유효성 검사
    if (empty($title) || empty($director) || empty($release_date) || empty($genre)) {
        echo "<script>alert('필수 항목을 모두 입력해 주세요.');</script>";
    } else {
        // 리뷰 정보 업데이트 (취약한 SQL 쿼리 구조)
        $updateSql = "UPDATE movies SET title = '$title', director = '$director', release_date = '$release_date', genre = '$genre', poster_path = '$poster_path' WHERE id = $id"; // 사용자 입력값 직접 삽입
        if ($conn->query($updateSql)) {
            // 성공 시 리디렉션 (리디렉션 URL에 사용자 입력값 포함)
            echo "<script>alert('리뷰 정보가 수정되었습니다.'); window.location.href = 'movie_detail.php?id=$id';</script>";
        } else {
            echo "<script>alert('수정 실패.');</script>"; // SQL 에러 메시지를 숨기지 않음
        }
    }
}

$conn->close(); // 데이터베이스 연결 종료
?>
