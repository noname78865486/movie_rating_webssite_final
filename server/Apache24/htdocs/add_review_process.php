<?php
// 세션 시작 및 DB 연결
session_start();
require_once 'config/db.php';

// 영화 평점 업데이트 함수 정의
function updateMovieRating($conn, $movie_id) {
    // 영화 평점 업데이트 쿼리
    $update_rating_sql = "UPDATE movies SET rating = (SELECT AVG(rating) FROM reviews WHERE movie_id = ?) WHERE id = ?";
    $update_stmt = $conn->prepare($update_rating_sql);
    $update_stmt->bind_param("ii", $movie_id, $movie_id);

    if (!$update_stmt->execute()) {
        die("평점 업데이트 실패: " . $update_stmt->error);
    }
    $update_stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 파일 업로드 처리
    $upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/review_file/';
    $file_path = '';
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['file']['tmp_name'];
        $file_name = basename($_FILES['file']['name']);
        $file_path = $upload_dir . $file_name;

        // 파일 저장
        if (move_uploaded_file($file_tmp_name, $file_path)) {
            $file_path = '/review_file/' . $file_name; // 상대 경로로 저장
        } else {
            die("파일 업로드 실패.");
        }
    }
}

// 폼에서 전달된 값 받기
$title = $_POST['title'];
$content = $_POST['content'];
$visibility = $_POST['visibility'];
$rating_user_idNum = $_SESSION['userID']; // 세션에 있는 userID
$movie_id = (int)$_POST['movie_id']; // reviews 테이블에서 사용하는 변수명은 유지
$rating = $_POST['rating']; // rating

// 사용자의 userID에 해당하는 id 값 가져오기
$sql = "SELECT id FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rating_user_idNum);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id); // users 테이블에서 가져온 id 값
    $stmt->fetch();
    $rating_user_idNum = (int)$user_id; // id 값으로 갱신 (int 타입으로 변환)
} else {
    die("사용자 정보를 찾을 수 없습니다.");
}

$stmt->close();

// 평점 기본값 처리
if (is_null($rating)) {
    $rating = 0; // 기본값 설정
}

// 파일 처리
$file_path = ''; // 파일이 없을 경우 빈 문자열로 설정
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    
    // 업로드 폴더 경로
    $upload_dir = 'review_file/';
    
    // 고유한 파일 이름 생성
    $new_file_name = uniqid() . '_' . basename($file_name);
    $file_path = $upload_dir . $new_file_name;

    // 파일을 서버에 저장
    if (!move_uploaded_file($file_tmp, $file_path)) {
        echo "파일 업로드에 실패했습니다.";
        exit;
    }
}

// rating 값이 존재하지 않으면 0으로 입력
if (is_null($rating)) {
    $rating = 0; // 또는 적절한 기본값 설정
}

// 리뷰 데이터를 DB에 삽입
$sql = "INSERT INTO reviews (movie_id, rating_user_idNum, title, content, rating, visibility, created_at, file_path) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("SQL prepare() 실패: " . $conn->error);
}

// 바인딩된 파라미터와 그 값 확인
$stmt->bind_param("iisssss", $movie_id, $rating_user_idNum, $title, $content, $rating, $visibility, $file_path);

// 실행 여부 확인
if (!$stmt->execute()) {
    die("쿼리 실행 실패: " . $stmt->error);
} else {
    echo "데이터 삽입 성공.";
}

// 영화 평점 업데이트 함수 호출
updateMovieRating($conn, $movie_id);

// 성공적으로 삽입 후 reviews_board.php로 리디렉션
header("Location: reviews_board.php?message=success");
exit;
?>
