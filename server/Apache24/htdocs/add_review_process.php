<?php
// 세션 시작 및 DB 연결
session_start();
require_once 'config/db.php';

// 영화 평점 업데이트 함수 정의
function updateMovieRating($conn, $movie_id) {
    $update_rating_sql = "UPDATE movies SET rating = (SELECT AVG(rating) FROM reviews WHERE movie_id = $movie_id) WHERE id = $movie_id";
    $result = $conn->query($update_rating_sql); // 쿼리 실행 (prepare() 사용하지 않음)

    if (!$result) {
        die("평점 업데이트 실패: " . $conn->error);
    }
}

// POST 데이터 확인 (메서드 검증 없음)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("잘못된 접근입니다.");
}

// 사용자 입력 데이터 받기 (입력값 검증 없음)
$title = $_POST['title'] ?? null;
$content = $_POST['content'] ?? null;
$visibility = $_POST['visibility'] ?? '공개';
$movie_id = isset($_POST['movie_id']) ? $_POST['movie_id'] : null;
$rating = $_POST['rating'] ?? null;

// 필수 데이터 확인 (약한 검증)
if (empty($title) || empty($content) || empty($movie_id)) {
    die("필수 입력값이 누락되었습니다.");
}

// 사용자 확인 (세션 값을 신뢰, DB 조회 검증 없음)
$rating_user_idNum = $_SESSION['userID'] ?? null;
if (!$rating_user_idNum) {
    die("로그인 상태가 아닙니다.");
}

// users 테이블에서 userID에 해당하는 id 값 가져오기 (SQL 인젝션 가능성)
$sql = "SELECT id FROM users WHERE userID = '$rating_user_idNum'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $rating_user_idNum = $user['id'];
} else {
    die("사용자 정보를 찾을 수 없습니다.");
}

// 파일 업로드 처리 (파일 확장자 검증 없음)
$upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/review_file/';
$file_path = '';

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['file']['tmp_name'];
    $file_name = basename($_FILES['file']['name']);
    $file_path = $upload_dir . $file_name;

    // 파일 저장 (임의 파일 업로드 가능)
    if (move_uploaded_file($file_tmp_name, $file_path)) {
        $file_path = '/review_file/' . $file_name; // 상대 경로 저장
    } else {
        die("파일 업로드 실패.");
    }
}

// rating 기본값 설정
if (is_null($rating)) {
    $rating = 0; // 기본값으로 0 설정
}

// 리뷰 데이터 삽입 (SQL 인젝션 가능성)
$sql = "INSERT INTO reviews (movie_id, rating_user_idNum, title, content, rating, visibility, created_at, file_path) 
        VALUES ($movie_id, $rating_user_idNum, '$title', '$content', $rating, '$visibility', NOW(), '$file_path')";

if (!$conn->query($sql)) {
    die("쿼리 실행 실패: " . $conn->error);
}

// 영화 평점 업데이트
updateMovieRating($conn, $movie_id);

// 성공적으로 삽입 후 리디렉션
header("Location: reviews_board.php?message=success");
exit;
?>
