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

if ($result->num_rows > 0) { $reviews = $result->fetch_assoc(); }
    else {die("후기를 찾을 수 없습니다.");}

// 댓글 저장 로직
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $commentContent = trim($_POST['comment']);
    $visibility = $_POST['visibility'] ?? '공개'; // 기본값은 '공개'

    if (!empty($commentContent) && $userId) {
        $sql = "INSERT INTO comments (review_id, user_id, content, visibility) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiss', $id, $rating_user_idNum, $commentContent, $visibility);

        if ($stmt->execute()) {
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
        WHERE c.review_id = ?
        AND (c.visibility = '공개' OR c.user_id = ? OR ? = ?)
        ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iiii', $id, $rating_user_idNum, $rating_user_idNum, $reviews['rating_user_idNum']);
$stmt->execute();
$comments = $stmt->get_result();

// 댓글 삭제 로직
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $commentId = intval($_POST['delete_comment_id']);

    // 댓글 작성자인지 확인
    $sql = "SELECT user_id FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $commentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $comment = $result->fetch_assoc();
        if ($comment['user_id'] === $rating_user_idNum) { // 작성자가 맞는 경우
            $deleteSql = "DELETE FROM comments WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param('i', $commentId);

            if ($deleteStmt->execute()) {
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

// 후기 삭제 처리
if (isset($_POST['delete'])) {
    // 삭제할 리뷰 ID
    $reviewId = intval($_POST['delete_review_id']); // HTML 폼에서 전달받은 리뷰 ID
    $rating_user_idNum = $_SESSION['userID']; // 세션에서 사용자 ID 가져오기

    if (!$reviewId || !$rating_user_idNum) {
        die("잘못된 요청입니다. 리뷰 ID나 사용자 정보를 확인해주세요.");
    }

    // 게시글 작성자인지 확인
    $sql = "SELECT rating_user_idNum FROM reviews WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL 준비 실패: " . $conn->error);
    }

    $stmt->bind_param('i', $reviewId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $comment = $result->fetch_assoc();
        if ($comment['rating_user_idNum'] === $rating_user_idNum) { // 작성자가 맞는 경우
            // 리뷰 삭제 쿼리 실행
            $deleteSql = "DELETE FROM reviews WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteSql);

            if (!$deleteStmt) {
                die("SQL 준비 실패: " . $conn->error);
            }

            $deleteStmt->bind_param('i', $reviewId);
            if ($deleteStmt->execute()) {
                // 성공적으로 삭제된 경우
                $deleteStmt->close();
                $stmt->close();
                $conn->close();

                // 리디렉션
                header("Location: reviews_board.php?message=deleted");
                exit;
            } else {
                die("게시글 삭제 중 오류 발생: " . $deleteStmt->error);
            }
        } else {
            die("삭제 권한이 없습니다.");
        }
    } else {
        die("게시글을 찾을 수 없습니다.");
    }

    // 리소스 해제
    $stmt->close();
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
        body {
            height: 100%;
            width: 100%;
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
        form {
            margin: 20px auto;
            padding: 20px;
            width: 300px;
            background: #fff;
        }
        /* 각 열의 비율을 지정 (전체 열을 균일하게 설정) */
        table th:nth-child(1), table td:nth-child(1) { width: 10%; } /* 댓글 작성자 */
        table th:nth-child(2), table td:nth-child(2) { width: 8%; } /* 공개/비공개 */
        table th:nth-child(3), table td:nth-child(3) { width: 30%; } /* 내용 */
        table th:nth-child(4), table td:nth-child(4) { width: 10%; } /* 작성시간 */
        table th:nth-child(5), table td:nth-child(5) { width: 3%; } /* 삭제 */
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <div style="width: 600px; margin: 20px auto; padding: 20px;">
        <!--비밀글 사용자 검증 : 비공개일 경우 작성자의 ID와 현재 접속한 유저의 ID를 비교하여 일치하는 경우에만 게시글 표출-->
        <?php if ($reviews['visibility'] == '비공개' && $reviews['rating_user_idNum'] != $rating_user_idNum): ?>
            <script>
                alert('비밀글입니다.');
                window.history.back(); // 이전 페이지로 돌아가기
            </script>
        <?php else: ?>
        
        <!--후기 제목-->
        <h1 style="color: white;"><?= $reviews['review_title'] ?></h1>
        <!-- 영화 포스터와 정보 박스 -->
        <div style="display: flex; justify-content: flex-start; align-items: flex-start; padding: 10px; width: 600px;">
            <!--후기 작성 시 태그한 영화의 포스터를 가져오는 코드-->
            <div class="movie-poster">
                <?php if (!empty($reviews['poster_path'])): ?>
                    <img src="<?= htmlspecialchars($reviews['poster_path']) ?>" alt="영화 포스터" style="max-width: 100%;">
                <?php else: ?>
                    <p style="color: #888;">포스터가 없습니다.</p>
                <?php endif; ?>
            </div>
            <!--영화 및 후기 정보 : 공개여부, 글쓴이, 평점, 작성일자-->
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

        <!--리뷰 내용 표시-->
        <div style="background-color: #fff; width: 600px; border: 1px solid #ccc; padding: 10px; margin-top: 16px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1); text-align: left;">
            <p>리뷰 내용 : <br><?= nl2br(htmlspecialchars($reviews['content'])) ?></p>
        </div>

        <!--후기 첨부파일 표시-->
        <div style="background-color: #fff; width: 600px; border: 1px solid #ccc; padding: 10px; margin-top: 16px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
            <p>첨부 파일 :</p>
            <?php if ($reviews['file_path']): ?>
                <img src="<?= $reviews['file_path']?>" alt="포스터" style="max-width: 100%; margin-top: 16px;">
            <?php else: ?>
                <p style="color: #888;">첨부된 파일이 없습니다.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 리뷰 수정 버튼 -->
    <a href="edit_review.php?id=<?= $movie['id'] ?>">
        <button type="button" style="margin-top: 16px;">리뷰 수정</button>
    </a>

    <!-- 리뷰 삭제 버튼 -->
    <form method="POST" action="" onsubmit="return confirm('정말 삭제하시겠습니까?');">
        <button type="submit" name="delete_review_id" style="margin-top: 16px; background-color: red; color: white;">리뷰 삭제</button>
    </form>

        <!-- 댓글 섹션 -->
        <h1>댓글</h1>
        <div style="width: 1200px; margin: 0 auto; margin-top: 40px;">
            <!-- 댓글 작성 폼 -->
            <form method="POST" action="" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 30px; width: 1000px;">
                <!-- 댓글 내용 작성 -->
                <textarea name="comment" rows="3" style="width: 70%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: none;" placeholder="댓글을 작성하세요..."></textarea>
                <!-- 공개 여부 및 작성 버튼 -->
                <div style="width: 25%; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                    <select name="visibility" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="공개">공개</option>
                        <option value="비공개">비공개</option>
                    </select>
                    <button type="submit" style="width: 100%; padding: 10px; background-color: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">댓글 작성</button>
                </div>
            </form><br>

            <!-- 댓글 표 -->
            <div>
                <!--리뷰 표 스타일-->
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
                    <!--리뷰 표 내용-->
                    <tbody>
                        <?php if ($comments->num_rows > 0): ?> <!-- 댓글이 있을 경우 -->
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                    <?= htmlspecialchars($comment['userID']) ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                    <?= htmlspecialchars($comment['visibility']) ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px;">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </td>
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                    <?= $comment['created_at'] ?>
                                </td>
                                <!-- 댓글 삭제 버튼 -->
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; vertical-align: middle;">
                                    <?php if ($comment['user_id'] == $rating_user_idNum): ?>
                                        <form id="delete-comment-form-<?= $comment['comment_id'] ?>" method="POST" action="" style="margin: 0; padding: 0; width: 60px; background: #fff;">
                                            <input type="hidden" name="delete_comment_id" value="<?= $comment['comment_id'] ?>">
                                            <button type="button" onclick="confirmDelete(<?= $comment['comment_id'] ?>)" style="background-color: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">삭제</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?> <!-- 댓글이 없을 경우 -->
                            <tr>
                                <td colspan="5" class="no-data">등록된 댓글이 없습니다.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <script>
            // 삭제 확인 및 삭제 요청
            function confirmDelete(commentId) {
                const confirmation = confirm("이 댓글을 삭제하시겠습니까?");
                if (confirmation) {
                    const form = document.getElementById('delete-comment-form-' + commentId);
                    form.submit(); // 삭제 폼 제출
                }
            }
        </script>

        </div>

        <?php endif; ?>
        <a href="reviews_board.php">
            <button type="button" style="margin-top: 16px; margin-bottom: 40px;">뒤로가기</button>
        </a>
        </div>
</body>
</html>
