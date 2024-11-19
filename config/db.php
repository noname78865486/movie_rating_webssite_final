<?php
// 데이터베이스 연결 정보 설정
$host = 'localhost';       // MySQL 서버 주소 (일반적으로 로컬 환경에서는 localhost)
$dbname = 'movie_rating_website'; // 사용할 데이터베이스 이름
$username = 'root';   // 데이터베이스 사용자 이름
$password = 'AKDLdptmzbdpf123!'; // 데이터베이스 사용자 비밀번호

// 데이터베이스 연결
$conn = new mysqli($host, $username, $password, $dbname);

// 연결 오류 확인
if ($conn->connect_error) {
    die("데이터베이스 연결 실패: " . $conn->connect_error);
}

// 한글 처리를 위한 설정
$conn->set_charset("utf8");

?>