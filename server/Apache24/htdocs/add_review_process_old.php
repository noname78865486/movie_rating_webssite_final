<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
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
    } else {
        echo '<pre>';
        print_r($_FILES);
        echo '</pre>';
        die("파일을 업로드하세요.");
    }
}

// 폼에서 전달된 값 받기
$title = $_POST['title'];
$content = $_POST['content'];
$visibility = $_POST['visibility'];
$rating_user_id = $_SESSION['userID']; // 변수명을 변경
$movie_id = $_POST['movie_id']; // reviews 테이블에서 사용하는 변수명은 유지
$rating = isset($_POST['rating']) ? $_POST['rating'] : null;  // rating 값이 없으면 NULL

// 파일 업로드 처리 (필요한 경우)
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    $file_name = basename($_FILES['image']['name']);
    $file_path = $upload_dir . $file_name;

    // 파일 이동
    if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
        // 파일 업로드 성공 시 파일 경로 저장
        echo "파일 업로드 성공.";
    } else {
        echo "파일 업로드 실패.";
        exit;
    }
} else {
    $file_path = ''; // 파일이 없을 경우 빈 문자열로 설정
}
// rating 값이 존재하지 않으면 0으로 입력
if (is_null($rating)) {
    $rating = 0; // 또는 적절한 기본값 설정
}

var_dump($movie_id, $rating_user_id, $title, $content, $rating, $visibility, $file_path);

// 리뷰 데이터를 DB에 삽입
$sql = "INSERT INTO reviews (movie_id, rating_user_id, title, content, rating, visibility, created_at, file_path) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("SQL prepare() 실패: " . $conn->error);
}

/*/ bind_param에서 데이터 타입을 맞춰줍니다.
// 'i' = integer, 's' = string
$stmt->bind_param("iisssss", $movie_id, $rating_user_id, $title, $content, $rating, $visibility, $file_path);
$stmt->execute();*/

echo "데이터 타입 통일 성공.";


// 영화 평점 업데이트를 위한 함수 호출
$update_rating_sql = "UPDATE movies SET rating = (SELECT AVG(rating) FROM reviews WHERE movie_id = ?) WHERE id = ?";
$update_stmt = $conn->prepare($update_rating_sql);
$update_stmt->bind_param("ii", $movie_id, $movie_id); // movies 테이블의 id로 변경
$update_stmt->execute();

// 성공적으로 삽입 후 reviews_board.php로 리디렉션
header("Location: reviews_board.php?message=success");
exit;
?>
