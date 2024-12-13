<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원정보 수정</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // 비밀번호와 비밀번호 확인 일치 여부 확인
        function validateForm() {
            const message = document.getElementById('error-message');
            const password = document.querySelector('input[name="password"]').value;
            const passwordCheck = document.querySelector('input[name="passwordCheck"]').value;

            if (password !== passwordCheck) {
                message.style.color = 'red';
                message.textContent = '비밀번호가 일치하지 않습니다.';
                return false; // 폼 제출 방지
            }

            return true; // 폼 제출 허용
        }
    </script>
</head>

<body>
    <p style="color:#fff"><a href="dashboard.php">🏠 Home</a></p>
    <h2>회원정보 수정</h2>
    
    <?php
    require_once 'config/db.php';
    session_start();

    if (!isset($_SESSION['userID'])) {
        header("Location: login.php"); // 로그인되지 않은 경우 로그인 페이지로 리디렉션
        exit;
    }

    $userID = $_SESSION['userID'];

    // 현재 로그인한 사용자의 정보를 가져오기
    $sql = "SELECT * FROM users WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "사용자 정보를 불러올 수 없습니다.";
        exit;
    }
    ?>

    <form id="editForm" action="update_userinfo_process.php" method="POST" onsubmit="return validateForm();" style="width: 600px">
        <!-- 성명 -->
        <label>성명:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required style="width: 150px; margin: 10px 5px;">
        
        <!-- Email -->
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required style="width: 250px; margin: 0px 5px;">
        <br>

        <!-- ID -->
        <label>ID:</label>
        <input type="text" name="userID" value="<?= htmlspecialchars($user['userID']) ?>" readonly style="width: 320px; margin: 0px 5px;">
        <br>

        <!-- 주소 -->
        <label>주소:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required style="width: 475px; margin: 0 5px;">
        <br>

        <!-- 휴대폰 번호 -->
        <label>휴대폰번호:</label>
        <input type="text" name="phoneNumber" value="<?= htmlspecialchars($user['phoneNumber']) ?>" required style="width: 200px; margin: 0 5px;">
        <br>

        <!-- 비밀번호 -->
        <label>비밀번호 :</label>
        <input type="password" name="password" placeholder="새 비밀번호" style="width: 340px; margin-left: 20px;">
        <br>

        <!-- 비밀번호 확인 -->
        <label>비밀번호 확인:</label>
        <input type="password" name="passwordCheck" placeholder="비밀번호 확인" style="width: 340px;">
        <br>

        <button type="submit">정보 수정</button>
        <span id="error-message"></span>
    </form>
</body>
</html>
