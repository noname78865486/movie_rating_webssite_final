<?php
// 세션 연결
session_start();

// DB 연결
require_once 'config/db.php';

// 로그인 여부 확인
// 로그인하지 않은 사용자는 로그인 페이지로 리디렉션
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('로그인한 회원만 가능합니다.'); location.href='login.php';</script>";
    exit;
}

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 사용자 입력 값 받기
    $title = $_POST['title']; // 영화 제목
    $director = $_POST['director']; // 감독명
    $release_date = $_POST['release_date']; // 개봉일
    $genre = $_POST['genre']; // 장르

    // 포스터 업로드 처리
    $upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/img/'; // 서버의 업로드 디렉터리
    $poster_path = ''; // 포스터 파일 경로 초기화

    // 포스터 파일이 업로드되었는지 확인
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $poster_tmp_name = $_FILES['poster']['tmp_name']; // 임시 저장 경로
        $poster_name = basename($_FILES['poster']['name']); // 사용자가 업로드한 파일명
        $poster_path = $upload_dir . $poster_name; // 최종 저장 경로

        // 파일을 지정한 경로에 저장
        if (move_uploaded_file($poster_tmp_name, $poster_path)) {
            $poster_path = '/img/' . $poster_name; // DB에 저장될 상대 경로 생성
        } else {
            die("포스터 업로드 실패.");
        }
    } else {
        die("포스터 파일을 업로드하세요.");
    }

    // SQL 쿼리를 생성하여 사용자 입력 값을 그대로 포함
    $sql = "INSERT INTO movies (title, director, release_date, genre, poster_path) 
            VALUES ('$title', '$director', '$release_date', '$genre', '$poster_path')";

    // SQL 쿼리 실행 - prepare()를 사용하지 않고 raw query 실행
    if ($conn->query($sql)) {
        echo "영화가 성공적으로 등록되었습니다.";
        header('Location: movie_list.php'); // 영화 목록 페이지로 리디렉션
        exit;
    } else {
        die("영화 등록 실패: " . $conn->error); // 오류 발생 시 상세 메시지 출력
    }
}

// DB 연결 종료
$conn->close();
?>
