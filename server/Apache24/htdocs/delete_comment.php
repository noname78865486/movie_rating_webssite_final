<?php
// 데이터베이스 연결 및 세션 시작
require_once 'config/db.php';
session_start();

// POST 요청 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 삭제하려는 댓글 ID와 현재 사용자 ID 가져오기
    $commentId = $_POST['delete_comment_id'] ?? null; // 댓글 ID
    $userId = $_SESSION['userID'] ?? null;           // 현재 로그인한 사용자 ID

    // 요청 유효성 검사
    if (!$commentId || !$userId) {
        die("잘못된 요청입니다.");
    }

    // 현재 사용자 ID로 댓글 삭제 권한 확인 및 삭제
    $sql = "DELETE FROM comments WHERE id = ? AND user_id = (
                SELECT id FROM users WHERE userID = ?
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $commentId, $userId); // 댓글 ID와 사용자 ID 바인딩
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // 삭제 성공
        header("Location: " . $_SERVER['HTTP_REFERER']); // 이전 페이지로 리다이렉트
        exit;
    } else {
        // 삭제 실패: 댓글이 없거나 권한 없음
        echo "댓글 삭제 권한이 없거나 댓글을 찾을 수 없습니다.";
    }
} else {
    // GET 요청 또는 잘못된 요청 차단
    die("잘못된 접근입니다.");
}