<?php
// 세션 연결
session_start();

// DB 연결
require_once 'config/db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인한 회원만 가능합니다.'); location.href='login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 사용자 입력 값 받기
    $title = $_POST['title'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $genre = $_POST['genre'];

    // 포스터 업로드 처리
    $upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/img/';
    $poster_path = '';

    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $poster_tmp_name = $_FILES['poster']['tmp_name'];
        $poster_name = basename($_FILES['poster']['name']);
        $poster_path = $upload_dir . $poster_name;

          // 파일 저장
            if (move_uploaded_file($poster_tmp_name, $poster_path)) {
                $poster_path = '/img/' . $poster_name; // 상대 경로로 저장
        } else {
            die("포스터 업로드 실패.");
    }
} else {
    die("포스터 파일을 업로드하세요.");
}
}

// 데이터베이스에 영화 데이터 삽입
$sql = "INSERT INTO movies (title, director, release_date, genre, poster_path)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssss', $title, $director, $release_date, $genre, $poster_path);

if ($stmt->execute()) {
    echo "영화가 성공적으로 등록되었습니다.";
    header('Location: movie_list.php');
    exit;
} else {
    die("영화 등록 실패: " . $conn->error);
}

$conn->close();
?>