<?php
require_once 'config/db.php';

// 영화의 ID를 받아서 해당 영화의 평점을 업데이트합니다.
if (isset($_POST['movie_id'])) {
    $movie_id = $_POST['movie_id'];

    // 해당 영화에 대한 모든 리뷰의 평균 평점을 구합니다.
    $sql = "SELECT AVG(rating) AS average_rating FROM reviews WHERE movie_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $average_rating = $row['average_rating'];

    // 평균 평점이 null이면 0으로 설정
    if ($average_rating === null) {
        $average_rating = 0;
    }

    // 영화 테이블에 평균 평점 업데이트
    $update_sql = "UPDATE movies SET rating = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("di", $average_rating, $movie_id);
    $update_stmt->execute();

    echo "평점이 성공적으로 업데이트되었습니다.";
}
?>
