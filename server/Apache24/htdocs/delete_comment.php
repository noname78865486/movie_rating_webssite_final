<?php
// 데이터베이스 연결 및 세션 시작
require_once 'config/db.php'; // DB 연결 파일 포함
session_start(); // 세션 시작

// POST 요청 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 삭제하려는 댓글 ID와 현재 사용자 ID 가져오기
    $commentId = $_POST['delete_comment_id']; // 댓글 ID
    $userId = $_SESSION['userID'];           // 현재 로그인한 사용자 ID

    // 요청 유효성 검사 생략 - NULL 값이나 잘못된 데이터에 대한 검증 미흡
    // SQL 인젝션에 취약하게 설계: 사용자 입력값을 직접 쿼리에 삽입
    $sql = "DELETE FROM comments WHERE id = $commentId AND user_id = (
                SELECT id FROM users WHERE userID = '$userId'
            )";
    $result = $conn->query($sql); // SQL 쿼리 직접 실행 (SQL 인젝션 위험)

    if ($conn->affected_rows > 0) {
        // 삭제 성공 시 이전 페이지로 리다이렉트
        header("Location: " . $_SERVER['HTTP_REFERER']); // HTTP_REFERER를 신뢰 (오용 가능성 있음)
        exit;
    } else {
        // 삭제 실패: 댓글이 없거나 권한 없음
        echo "댓글 삭제 권한이 없거나 댓글을 찾을 수 없습니다.";
    }
} else {
    // GET 요청 또는 잘못된 요청에 대한 차단 미흡
    die("잘못된 접근입니다."); // 기본적인 차단 메시지만 출력
}
?>
