<?php
require_once 'config/db.php';
session_start();

// URL 매개변수로 전달된 id 값 가져오기
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("잘못된 접근입니다."); // id가 없거나 숫자가 아니면 오류 처리
}

$userId = $_SESSION['userID'] ?? null; // 로그인한 사용자 ID

// 로그인한 사용자 정보에서 rating_user_idNum 가져오기
$sql = "SELECT id FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userId); // userID를 바인딩
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $rating_user_idNum = $userData['id']; // 로그인한 사용자의 id (rating_user_idNum)
} else {
    die("사용자 정보가 없습니다.");
}

// 리뷰 정보 가져오기
$sql = "SELECT r.id AS review_id, r.movie_id, m.title AS movie_title, m.poster_path, 
               r.rating_user_idNum, r.title AS review_title, r.content, 
               r.rating, r.visibility, r.created_at, r.file_path, 
               u.userID 
        FROM reviews r
        JOIN users u ON r.rating_user_idNum = u.id
        JOIN movies m ON r.movie_id = m.id
        WHERE r.id = ?"; // 특정 리뷰만 조회
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id); // id를 바인딩
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $reviews = $result->fetch_assoc();
} else {
    die("후기를 찾을 수 없습니다.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>후기 상세 정보</title>
    <style>
        body {
            margin: 100px 0 0 0; /* 상단 여백 50px */
        }
        .float_box {
            display: flex; justify-content: space-between; align-items: center; margin-top: 16px; margin-bottom: 8px; background-color: #fff; width: 300px; border: 1px solid #ccc; padding: 10px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        /* 영화 포스터 스타일 */
        .movie-poster {
            flex: 1;
            max-width: 400px;
            margin-right: 20px; /* 포스터와 텍스트 사이의 여백 */
        }
        /* 영화 정보 박스 스타일 */
        .movie-info {
            flex: 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
        }
    </style>
    <script>
        // 비밀글이 아닌 경우만 내용을 표시하고, 다른 사용자는 알림 후 돌아가게 처리
        function checkAccess(userId, reviewUserId) {
            if (userId != reviewUserId) {
                alert('비밀글입니다.');
                window.history.back(); // 이전 페이지로 돌아가기
            }
        }
    </script>
</head>
<body>
    <div style="width: 600px; margin: 20px auto; padding: 20px;">
        <?php if ($reviews['visibility'] == '비공개' && $reviews['rating_user_idNum'] != $rating_user_idNum): ?>
            <script>
                checkAccess(<?= json_encode($rating_user_idNum) ?>, <?= json_encode($reviews['rating_user_idNum']) ?>);
            </script>
        <?php else: ?>
            <!-- 영화 포스터와 정보 박스 -->
            <div style="display: flex; justify-content: flex-start; align-items: flex-start; padding: 10px; width: 600px;">
                <div class="movie-poster">
                    <?php if (!empty($reviews['poster_path'])): ?>
                        <img src="<?= htmlspecialchars($reviews['poster_path']) ?>" alt="영화 포스터" style="max-width: 100%;">
                    <?php else: ?>
                        <p style="color: #888;">포스터가 없습니다.</p>
                    <?php endif; ?>
                </div>
                <div class="movie-info">
                    <div class="float_box">
                        <p>공개여부: <?= htmlspecialchars($reviews['visibility']) ?></p>
                    </div>
                    <div class="float_box">
                        <p>글쓴이: <?= htmlspecialchars($reviews['userID']) ?></p>
                    </div>
                    <div class="float_box">
                        <p>평점: <?= htmlspecialchars($reviews['rating']) ?>/10</p>
                    </div>
                    <div class="float_box">
                        <p>작성일자: <?= htmlspecialchars($reviews['created_at']) ?></p>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; margin-bottom: 8px; background-color: #fff; width: 600px; border: 1px solid #ccc; padding: 10px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
                <p>제목 : <?= $reviews['review_title'] ?></p>
            </div>

            <div style="background-color: #fff; width: 600px; border: 1px solid #ccc; padding: 10px; margin-top: 16px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); text-align: left;">
                <p>리뷰 내용 : <br><?= nl2br(htmlspecialchars($reviews['content'])) ?></p>
            </div>

            <div style="background-color: #fff; width: 600px; border: 1px solid #ccc; padding: 10px; margin-top: 16px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
                <?php if ($reviews['file_path']): ?>
                    <img src="<?= $reviews['file_path']?>" alt="포스터" style="max-width: 300px;">
                <?php else: ?>
                    <p>첨부된 파일이 없습니다.</p>
                <?php endif; ?>
            </div>

            <button type="button" style="margin-top: 16px; margin-bottom: 40px;" onclick="history.back();">뒤로가기</button>
        <?php endif; ?>
    </div>
</body>
</html>
