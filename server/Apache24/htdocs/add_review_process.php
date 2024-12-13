<?php
// 세션 시작 및 DB 연결
session_start();
require_once 'config/db.php';

// 영화 평점 업데이트 함수 정의
function updateMovieRating($conn, $movie_id) {
    $update_rating_sql = "UPDATE movies SET rating = (SELECT AVG(rating) FROM reviews WHERE movie_id = ?) WHERE id = ?";
    $update_stmt = $conn->prepare($update_rating_sql);
    $update_stmt->bind_param("ii", $movie_id, $movie_id);

    if (!$update_stmt->execute()) {
        error_log("평점 업데이트 실패: " . $update_stmt->error);
        die("평점 업데이트 실패.");
    }
}

// POST 데이터 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("잘못된 접근입니다.");
}

$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;
$visibility = $_POST['visibility'] ?? '공개';
$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : null;
$rating = $_POST['rating'] ?? null;

// 필수 데이터 확인
if (empty($title) || empty($content) || empty($movie_id)) {
    die("필수 입력값이 누락되었습니다.");
}

// 사용자 확인
$rating_user_idNum = $_SESSION['userID'] ?? null;
if (!$rating_user_idNum) {
    die("로그인 상태가 아닙니다.");
}

// users 테이블에서 userID에 해당하는 id 값 가져오기
$sql = "SELECT id FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rating_user_idNum);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $rating_user_idNum = (int)$user_id;
} else {
    die("사용자 정보를 찾을 수 없습니다.");
}
$stmt->close();

// 파일 업로드 처리
$upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/review_file/';
$file_path = '';

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['file']['tmp_name'];
    $file_name = basename($_FILES['file']['name']);
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($file_tmp_name, $file_path)) {
        $file_path = '/review_file/' . $file_name;
    } else {
        die("파일 업로드 실패.");
    }
}

// rating 기본값 설정
if (is_null($rating)) {
    $rating = 0;
}

// 리뷰 데이터 삽입
$sql = "INSERT INTO reviews (movie_id, rating_user_idNum, title, content, rating, visibility, created_at, file_path) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("SQL prepare() 실패: " . $conn->error);
}

$stmt->bind_param("iisssss", $movie_id, $rating_user_idNum, $title, $content, $rating, $visibility, $file_path);

if (!$stmt->execute()) {
    die("쿼리 실행 실패: " . $stmt->error);
}

// 영화 평점 업데이트
updateMovieRating($conn, $movie_id);

// 성공적으로 삽입 후 리디렉션
header("Location: reviews_board.php?message=success");
exit;
?>