<?php
// login.php는 사용자 UI를 제공하여 사용자가 로그인할 수 있는 HTML 폼을 제공하고, 서버와 직접 통신하지 않고 사용자가 입력한 데이터를 전송하기 위한 화면을 구성
// login_process.php는 서버와 직접 통신하며 데이터베이스와 상호작용하여 인증을 수행. 비밀번호 검증, 세션 설정, 리다이렉션, 실패 시 에러 반환 등의 서버 로직을 수행.
require_once 'config/db.php'; // DB 연결 파일
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id; // 세션에 사용자 ID 저장
            echo "로그인 성공!";
        } else {
            echo "비밀번호가 틀렸습니다.";
        }
    } else {
        echo "사용자를 찾을 수 없습니다.";
    }

    $stmt->close();
    $conn->close();
}
?>