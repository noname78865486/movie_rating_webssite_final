<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

$userId = $_SESSION['userID'] ?? null; // 로그인한 사용자 ID

// URL 매개변수로 전달된 id 값 가져오기
$id = $_GET['id'] ?? null;

// id가 없거나 숫자가 아닌 경우 오류 처리
if (!$id || !is_numeric($id)) {
    die("잘못된 접근입니다."); 
}

// 로그인한 사용자 정보에서 id 가져오기
$sql = "SELECT id FROM users WHERE userID = '$userId'"; // prepare() 제거하고 직접 SQL 실행
$userResult = $conn->query($sql);

if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $rating_user_idNum = $userData['id']; // 로그인한 사용자의 id
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
        LEFT JOIN movies m ON r.movie_id = m.id 
        WHERE r.id = $id"; // id를 직접 SQL에 포함
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $reviews = $result->fetch_assoc();
} else {
    die("후기를 찾을 수 없습니다.");
}

// 영화 제목이 조회되지 않을 경우 "삭제된 영화입니다." 설정
$movieTitle = $reviews['movie_title'] ?? "삭제된 영화입니다.";

// 작성자인지 확인
$isAuthor = $reviews['rating_user_idNum'] === $rating_user_idNum;

// admin인지 확인
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// 댓글 저장 로직
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $commentContent = trim($_POST['comment']);
    $visibility = $_POST['visibility'] ?? '공개'; // 기본값은 '공개'

    if (!empty($commentContent) && $userId) {
        $sql = "INSERT INTO comments (review_id, user_id, content, visibility) VALUES ($id, $rating_user_idNum, '$commentContent', '$visibility')"; // prepare() 제거
        if ($conn->query($sql)) {
            header("Location: review_detail.php?id=" . $id); // 댓글 작성 후 페이지 새로고침
            exit;
        } else {
            echo "댓글을 저장하는 중 오류가 발생했습니다.";
        }
    } else {
        echo "댓글 내용을 입력하세요.";
    }
}

// 댓글 불러오기 로직
$sql = "SELECT c.content, c.created_at, c.visibility, u.userID, c.user_id, c.id AS comment_id
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.review_id = $id
        ORDER BY c.created_at DESC";
$comments = $conn->query($sql);

// 댓글 삭제 로직
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $commentId = intval($_POST['delete_comment_id']);

    // 댓글 작성자인지 확인
    $sql = "SELECT user_id FROM comments WHERE id = $commentId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $comment = $result->fetch_assoc();
        if ($comment['user_id'] === $rating_user_idNum) { // 작성자가 맞는 경우
            $deleteSql = "DELETE FROM comments WHERE id = $commentId";
            if ($conn->query($deleteSql)) {
                header("Location: review_detail.php?id=" . $id); // 삭제 후 페이지 새로고침
                exit;
            } else {
                echo "댓글 삭제 중 오류가 발생했습니다.";
            }
        } else {
            echo "권한이 없습니다."; // 작성자가 아닌 경우
        }
    } else {
        echo "댓글을 찾을 수 없습니다.";
    }
}

// 리뷰 삭제 처리
if (($isAdmin || $isAuthor) && isset($_POST['delete'])) {
    $deleteSql = "DELETE FROM reviews WHERE id = $id";
    if ($conn->query($deleteSql)) {
        echo "<script>alert('리뷰가 삭제되었습니다.'); window.location.href = 'reviews_board.php';</script>";
    } else {
        echo "<script>alert('삭제 실패.');</script>";
    }
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
    <script>
        // 리뷰 추가 클릭 시 로그인 여부를 판단하여 로그인하지 않은 유저 차단
        function handleAddreview(isLoggedIn) {
            if (isLoggedIn) {
                // 로그인된 사용자일 경우 add_review.php로 이동
                window.location.href = 'add_review.php';
            } else {
                // 로그인되지 않은 경우 경고 메시지 표시
                alert('로그인한 회원만 가능한 기능입니다.');
            }
        }
    </script>
    <style>
        a {
            margin: 20px auto;
            padding: 20px;
            width: 150px;
        }
        body {
            height: 100%;
            width: 100%;
        }
        .float_box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            margin-bottom: 8px;
            background-color: #fff;
            width: 300px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .movie-poster {
            flex: 1;
            max-width: 400px;
            margin-right: 20px;
        }
        .movie-info {
            flex: 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
        }
        table th:nth-child(1), table td:nth-child(1) { width: 10%; }
        table th:nth-child(2), table td:nth-child(2) { width: 8%; }
        table th:nth-child(3), table td:nth-child(3) { width: 30%; }
        table th:nth-child(4), table td:nth-child(4) { width: 10%; }
        table th:nth-child(5), table td:nth-child(5) { width: 3%; }
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <div style="width: 600px; margin: 20px auto; padding: 20px;">
        <?php if ($reviews['visibility'] == '비공개' && $reviews['rating_user_idNum'] != $rating_user_idNum && !$isAdmin): ?>
            <script>
                alert('비밀글입니다.');
                window.history.back();
            </script>
        <?php else: ?>
        <?php endif; ?>
        
        <h1 style="color: white;"><?= $reviews['review_title'] ?></h1>
        <div style="display: flex; justify-content: flex-start; align-items: flex-start; padding: 10px; width: 600px;">
            <div class="movie-poster">
                <?php if (!empty($reviews['poster_path'])): ?>
                    <img src="<?= $reviews['poster_path'] ?>" alt="영화 포스터" style="max-width: 100%;">
                <?php else: ?>
                    <p style="color: #888;">포스터가 없습니다.</p>
                <?php endif; ?>
            </div>
            <div class="movie-info">
                <div class="float_box">
                    <p>공개여부: <?= $reviews['visibility'] ?></p>
                </div>
                <div class="float_box">
                    <p>글쓴이: <?= $reviews['userID'] ?></p>
                </div>
                <div class="float_box">
                    <p>평점: <?= $reviews['rating'] ?>/10</p>
                </div>
                <div class="float_box">
                    <p>작성일자: <?= $reviews['created_at'] ?></p>
                </div>
            </div>
        </div>

        <div style="background-color: #fff; width: 600px; border: 1px solid #ccc; padding: 10px; margin-top: 16px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); text-align: left;">
            <p>리뷰 내용 : <br><?= nl2br($reviews['content']) ?></p>
        </div>

        <div style="background-color: #fff; width: 600px; border: 1px solid #ccc; padding: 10px; margin-top: 16px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
            <p>첨부 파일 :</p>
            <?php if ($reviews['file_path']): ?>
                <img src="<?= $reviews['file_path'] ?>" alt="첨부파일" style="max-width: 100%; margin-top: 16px;">
            <?php else: ?>
                <p style="color: #888;">첨부된 파일이 없습니다.</p>
            <?php endif; ?>
        </div>

        <?php if ($isAdmin || $isAuthor): ?>
            <div style="display: flex; gap: 10px; align-items: center; margin-top: 16px;">
                <a href="edit_review.php?id=<?= $reviews['review_id'] ?>">
                    <button>리뷰 수정</button>
                </a>
                <form method="POST" action="" style="background-color: transparent; margin:0 0;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                    <button type="submit" name="delete">리뷰 삭제</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <h1>댓글</h1>
    <div style="width: 1200px; margin: 0 auto; margin-top: 40px;">
        <form method="POST" action="" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 30px; width: 1000px;">
            <textarea name="comment" rows="3" style="width: 70%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: none;" placeholder="댓글을 작성하세요..."></textarea>
            <div style="width: 25%; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                <select name="visibility" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="공개">공개</option>
                    <option value="비공개">비공개</option>
                </select>
                <button type="submit" style="width: 100%; padding: 10px; background-color: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">댓글 작성</button>
            </div>
        </form>
        <br>
        <div>
            <table style="width: 1000px; border-collapse: collapse; border-radius: 0px; box-shadow: 0 0 0;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="border: 1px solid #ddd; padding: 8px;">댓글 작성자</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">공개/비공개</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">내용</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">작성 시간</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">삭제</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($comments->num_rows > 0): ?>
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?= $comment['userID'] ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?= $comment['visibility'] ?></td>
                                <td>
                                    <?php if ($comment['visibility'] === '공개' || $isAdmin || $isAuthor || $comment['user_id'] === $rating_user_idNum): ?>
                                        <?= $comment['content'] ?>
                                    <?php else: ?>
                                        <em>비밀 댓글입니다.</em>
                                    <?php endif; ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;"><?= $comment['created_at'] ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle;">
                                    <?php if ($isAdmin || $comment['user_id'] == $rating_user_idNum): ?>
                                        <form id="delete-comment-form-<?= $comment['comment_id'] ?>" method="POST" action="" style="margin: 0; padding: 0; width: 60px; background: #fff;">
                                            <input type="hidden" name="delete_comment_id" value="<?= $comment['comment_id'] ?>">
                                            <button type="button" onclick="confirmDelete(<?= $comment['comment_id'] ?>)" style="background-color: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">삭제</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">등록된 댓글이 없습니다.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function confirmDelete(commentId) {
            const confirmation = confirm("이 댓글을 삭제하시겠습니까?");
            if (confirmation) {
                const form = document.getElementById('delete-comment-form-' + commentId);
                form.submit();
            }
        }
    </script>
    <a href="reviews_board.php">
        <button type="button" style="margin-top: 16px; margin-bottom: 40px;">뒤로가기</button>
    </a>
</body>
</html>