<!-- register.php -->
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function checkUserID() {
            const userID = document.querySelector('input[name="userID"]').value;
            const message = document.getElementById('check-message');

            // AJAX 요청
            fetch('check_userID.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'userID=' + encodeURIComponent(userID)
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    message.style.color = 'red';
                    message.textContent = '이미 존재하는 ID입니다.';
                } else {
                    message.style.color = 'green';
                    message.textContent = '사용 가능한 ID입니다.';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                message.style.color = 'red';
                message.textContent = '오류 발생. 다시 시도해주세요.';
            });
        }
    </script>
</head>
<body>
    <h2>회원가입</h2>
    <form action="register_process.php" method="POST">
        <label>성명:</label>
        <input type="text" name="username" required><br>

        <label>ID:</label>
        <input type="text" name="userID" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>주민등록번호:</label>
        <input type="tel" name="identifNum" required><br>

        <label>주소:</label>
        <input type="text" name="address" required><br>

        <label>휴대폰번호:</label>
        <input type="tel" name="phoneNumber" required><br>

        <label>비밀번호:</label>
        <input type="password" name="password" required><br>

        <label>비밀번호 확인:</label>
        <input type="password" name="passwordCheck" required><br>

        <button type="submit">회원가입</button>
    </form>
</body>
</html>
