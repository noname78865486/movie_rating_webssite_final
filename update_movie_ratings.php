<?php
require_once 'config/db.php';

// 영화의 ID를 받아서 해당 영화의 평점을 업데이트.
if (isset($_POST['movie_id'])) {
    $movie_id = $_POST['movie_id'];

    // SQL 인젝션에 취약한 방식으로 평균 평점을 계산.사용자 입력값이 그대로 쿼리에 삽입됨.
    $sql = "SELECT AVG(rating) AS average_rating FROM reviews WHERE movie_id = $movie_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $average_rating = $row['average_rating'];

    // 평균 평점이 null이면 0으로 설정
    if ($average_rating === null) {
        $average_rating = 0;
    }

    // 영화 테이블에 평균 평점을 업데이트.
    $update_sql = "UPDATE movies SET rating = $average_rating WHERE id = $movie_id";
    $conn->query($update_sql);

    echo "평점이 성공적으로 업데이트되었습니다.";
}
?>
