<?php
// login.php는 사용자 UI를 제공하여 사용자가 로그인할 수 있는 HTML 폼을 제공하고, 서버와 직접 통신하지 않고 사용자가 입력한 데이터를 전송하기 위한 화면을 구성
// login_process.php는 서버와 직접 통신하며 데이터베이스와 상호작용하여 인증을 수행. 세션 설정, 리다이렉션, 실패 시 에러 반환 등의 기능을 수행.
session_start(); // 세션 시작

// 데이터베이스 연결 파일 포함
require_once 'config/db.php';

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = trim($_POST['userID']);
    $password = trim($_POST['password']);

    // 유효성 검사
    if (empty($userID) || empty($password)) {
        $error = "모든 필드를 채워주세요.";
    } else {
        // DB에서 사용자 조회
        $sql = "SELECT id, password FROM users WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            // 비밀번호 검증
            if (password_verify($password, $row['password'])) {
                // 세션 설정 및 세션 고정 방지(session regenerate_id)
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['userID'] = $userID;
                header("Location: dashboard.php"); // 로그인 성공 후 대시보드로 리다이렉트
                exit;
            }
        }
        $error = "ID 또는 비밀번호가 틀렸습니다.";
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>로그인</title>
</head>
<body>
<div id="login_wrap" class="wrap">
    <div>
        <p><a style="color:red" href="index.html">🏠home</a>
        <h1 style="color:#222222">로그인</h1>
        <?php if (isset($error))echo "<p style='color: red;'>$error</p>";?>
        <form action="login.php" method="post">
            <label for="userID">ID</label><br>
            <input type="text" id="userID" name="userID" required placeholder=ID><br>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder=Password><br>

        <button type="submit">로그인</button>
    </form>
    <p class="regist_btn">Not a member? &nbsp;<a href="register.php">Sign Up✒️</a></p>
</body>
</html>
