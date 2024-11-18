<?php
// login_process.php
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
