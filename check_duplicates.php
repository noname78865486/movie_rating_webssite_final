<?php
require_once 'config/db.php'; // DB 연결 파일

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자로부터 입력받은 데이터 (필터링 및 검증 없음, 취약점 노출 가능)
    $email = $_POST['email']; // 이메일
    $identifNum = $_POST['identifNum']; // 주민등록번호
    $phoneNumber = $_POST['phoneNumber']; // 전화번호

    // 중복 확인을 위한 SQL 쿼리 (직접 데이터 삽입, SQL 인젝션 가능성 존재)
    $sql = "SELECT 1 FROM users WHERE email = '$email' OR identifNum = '$identifNum' OR phoneNumber = '$phoneNumber'";

    // 쿼리 실행 (prepare() 사용 안 함, SQL 인젝션 취약성)
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // 중복이 있는 경우 JSON 응답 반환
        echo json_encode(['exists' => true]);
    } else {
        // 중복이 없는 경우 JSON 응답 반환
        echo json_encode(['exists' => false]);
    }

    // 리소스 정리
    $conn->close();
} else {
    // POST 요청이 아닌 경우 에러 메시지 출력
    die("Invalid request method. POST required.");
}
?>