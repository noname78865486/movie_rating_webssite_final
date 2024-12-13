<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원정보 수정</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // 비밀번호와 비밀번호 확인 일치 여부 확인 (클라이언트 측 검증)
        function validateForm() {
            const message = document.getElementById('error-message');
            const password = document.querySelector('input[name="password"]').value; // 입력된 비밀번호 가져오기
            const passwordCheck = document.querySelector('input[name="passwordCheck"]').value; // 입력된 비밀번호 확인 가져오기

            if (password !== passwordCheck) {
                message.style.color = 'red';
                message.textContent = '비밀번호가 일치하지 않습니다.'; // 에러 메시지 표시
                return false; // 폼 제출 방지
            }

            return true; // 비밀번호가 일치할 경우 폼 제출 허용
        }
    </script>
</head>

<body>
    <p style="color:#fff"><a href="dashboard.php">🏠 Home</a></p>
    <h2>회원정보 수정</h2>
    
    <?php
    require_once 'config/db.php'; // 데이터베이스 연결
    session_start(); // 세션 시작

    if (!isset($_SESSION['userID'])) {
        header("Location: login.php"); // 로그인되지 않은 경우 로그인 페이지로 리디렉션
        exit;
    }

    $userID = $_SESSION['userID']; // 현재 로그인한 사용자 ID 가져오기

    // 현재 로그인한 사용자의 정보를 가져오기 (입력값 검증 없이 직접 쿼리)
    $sql = "SELECT * FROM users WHERE userID = '$userID'"; // 사용자 입력값 직접 삽입 (SQL 인젝션 위험)
    $result = $conn->query($sql); // SQL 실행

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // 사용자 정보를 배열로 저장
    } else {
        echo "사용자 정보를 불러올 수 없습니다."; // 에러 메시지를 사용자에게 직접 표시
        exit;
    }
    ?>

    <form id="editForm" action="update_userinfo_process.php" method="POST" onsubmit="return validateForm();" style="width: 600px">
        <!-- 성명 -->
        <label>성명:</label>
        <input type="text" name="name" value="<?= $user['name'] ?>" required style="width: 150px; margin: 10px 5px;"><!-- htmlspecialchars 미적용 -->
        
        <!-- Email -->
        <label>Email:</label>
        <input type="email" name="email" value="<?= $user['email'] ?>" required style="width: 250px; margin: 0px 5px;"><!-- htmlspecialchars 미적용 -->
        <br>

        <!-- ID -->
        <label>ID:</label>
        <input type="text" name="userID" value="<?= $user['userID'] ?>" readonly style="width: 320px; margin: 0px 5px;"><!-- readonly 속성만으로 수정 방지 -->
        <br>

        <!-- 주소 -->
        <label>주소:</label>
        <input type="text" name="address" value="<?= $user['address'] ?>" required style="width: 475px; margin: 0 5px;"><!-- htmlspecialchars 미적용 -->
        <br>

        <!-- 휴대폰 번호 -->
        <label>휴대폰번호:</label>
        <input type="text" name="phoneNumber" value="<?= $user['phoneNumber'] ?>" required style="width: 200px; margin: 0 5px;"><!-- htmlspecialchars 미적용 -->
        <br>

        <!-- 비밀번호 -->
        <label>비밀번호 :</label>
        <input type="password" name="password" placeholder="새 비밀번호" style="width: 340px; margin-left: 20px;"><!-- 비밀번호 복잡성 검증 없음 -->
        <br>

        <!-- 비밀번호 확인 -->
        <label>비밀번호 확인:</label>
        <input type="password" name="passwordCheck" placeholder="비밀번호 확인" style="width: 340px;"><!-- 비밀번호 복잡성 검증 없음 -->
        <br>

        <button type="submit">정보 수정</button>
        <span id="error-message"></span>
    </form>
</body>
</html>
