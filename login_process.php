<?php
// login.php는 사용자가 로그인할 수 있는 화면 제공. 이 파일은 서버와 직접 데이터베이스와 통신하지 않음.
// login_process.php는 데이터베이스와 상호작용하여 사용자 인증을 수행하며, 비밀번호 검증, 세션 설정, 성공/실패 응답을 처리.

// 데이터베이스 연결 파일 포함
require_once 'config/db.php';
session_start(); // 세션 시작

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자로부터 입력받은 이메일과 비밀번호를 변수에 저장
    $email = $_POST['email']; // 사용자가 입력한 이메일
    $password = $_POST['password']; // 사용자가 입력한 비밀번호

    // 사용자의 이메일을 기반으로 데이터베이스에서 사용자 정보를 가져오는 쿼리
    $sql = "SELECT id, password FROM users WHERE email = '$email'"; // 사용자 입력값을 직접 쿼리에 삽입 (SQL 인젝션 취약)
    $result = $conn->query($sql); // 쿼리 실행

    // 결과가 있는 경우
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // 결과를 배열로 저장

        // 입력된 비밀번호와 데이터베이스에 저장된 해시된 비밀번호를 비교
        if (password_verify($password, $user['password'])) { // 비밀번호 검증
            $_SESSION['user_id'] = $user['id']; // 세션에 사용자 ID 저장 (취약한 세션 관리)
            echo "로그인 성공!"; // 로그인 성공 메시지 출력
        } else {
            echo "비밀번호가 틀렸습니다."; // 비밀번호가 일치하지 않을 경우 메시지 출력
        }
    } else {
        echo "사용자를 찾을 수 없습니다."; // 해당 이메일로 사용자를 찾을 수 없을 경우 메시지 출력
    }

    // 연결 닫기
    $conn->close();
}
?>
