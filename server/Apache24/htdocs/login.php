<?php
session_start(); // 세션 시작: 사용자의 세션 데이터를 관리하기 위해 필요

// 데이터베이스 연결 파일 포함
require_once 'config/db.php'; // DB 연결 설정 파일을 불러옴

// 로그인 요청이 POST 방식으로 전달되었는지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력 값 가져오기
    $userID = trim($_POST['userID']); // 입력된 사용자 ID를 가져와 공백 제거
    $password = trim($_POST['password']); // 입력된 비밀번호를 가져와 공백 제거

    // 유효성 검사: ID와 비밀번호가 비어있는지 확인
    if (empty($userID) || empty($password)) {
        $error = "모든 필드를 채워주세요."; // 에러 메시지 설정
    } else {
        // 데이터베이스에서 사용자 조회(id, password, role)
        $sql = "SELECT id, password, role FROM users WHERE userID = ?"; // SQL 쿼리 준비
        $stmt = $conn->prepare($sql); // SQL 쿼리 준비
        $stmt->bind_param("s", $userID); // 사용자 ID를 SQL 쿼리에 바인딩
        $stmt->execute(); // SQL 쿼리 실행
        $result = $stmt->get_result(); // 실행 결과 가져오기

        // 결과에서 사용자 데이터 확인
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc(); // 결과를 연관 배열로 가져옴
            
            // 유저 비활성화 여부 확인
            if ($row['role'] === '비활성화') {
                echo "<script>alert('비활성화된 계정입니다. 관리자에게 문의하세요.'); window.location.href = 'login.php';</script>";
                header("Location: login.php");
                exit;
            }

            // 입력된 비밀번호와 저장된 해시 비밀번호 비교
            if (password_verify($password, $row['password'])) {
                // 비밀번호가 일치하면 세션 설정
                session_regenerate_id(true); // 세션 고정 공격 방지를 위해 세션 ID 재생성

                // 세션 변수에 사용자 정보를 저장
                $_SESSION['user_id'] = $row['id']; // 사용자 ID 저장
                $_SESSION['userID'] = $userID; // 사용자 입력 ID 저장
                $_SESSION['role'] = $row['role']; // 사용자 역할(role) 저장
                $_SESSION['login_time'] = date('Y-m-d H:i:s'); // 로그인 시간 저장

                // 로그인 성공 시 대시보드로 리다이렉트
                header("Location: dashboard.php");
                exit; // 추가 코드 실행 방지
            }
        }
        // 사용자 ID 또는 비밀번호가 일치하지 않을 경우 에러 메시지 설정
        $error = "ID 또는 비밀번호가 틀렸습니다.";
        $stmt->close(); // SQL 스테이트먼트 닫기
    }

    $conn->close(); // 데이터베이스 연결 닫기
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- 외부 스타일시트 연결 -->
    <title>로그인</title>
</head>
<body>
    <div>
        <!-- 홈으로 돌아가는 링크 -->
        <p><a style="color:red; margin=10px 0;" href="index.php">🏠home</a></p>
        <h1 style="color:#fff; margin=10px 0;">로그인</h1>

        <!-- 에러 메시지 표시 -->
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>

        <!-- 로그인 폼 -->
        <form action="login.php" method="post">
            <label for="userID">ID</label><br>
            <input type="text" id="userID" name="userID" required placeholder="ID"><br>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Password"><br>

            <button type="submit">로그인</button>
        </form>

        <!-- 회원가입 링크 -->
        <p class="regist_btn">Not a member? &nbsp;<a href="register.php">Sign Up✒️</a></p>
    </div>
</body>
</html>
