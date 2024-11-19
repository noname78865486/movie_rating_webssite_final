<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        // ID 중복 여부 상태 저장
        let isUserIDAvailable = false;

        function checkUserID() {
            const userID = document.querySelector('input[name="userID"]').value.trim(); // 공백 제거
            const message = document.getElementById('id-check-message');

            // ID가 공란인지 확인
            if (!userID) {
                message.style.color = 'red';
                message.textContent = 'ID를 입력해주세요.';
                isUserIDAvailable = false;
                return; // 중복 체크 요청 중단
            }

            // AJAX 요청 : 
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
                    isUserIDAvailable = false;
                } else {
                    message.style.color = 'green';
                    message.textContent = '사용 가능한 ID입니다.';
                    isUserIDAvailable = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                message.style.color = 'red';
                message.textContent = '오류 발생. 다시 시도해주세요.';
            });
        }
            // 폼 제출 시 유효성 검사
            function validateForm() {
            const message = document.getElementById('error-message');

            // ID 중복 확인이 완료되지 않았거나 중복 ID인 경우
            if (!isUserIDAvailable) {
                message.style.color = 'red';
                message.textContent = 'ID 중복 확인을 먼저 해주세요.';
                return false;  // 폼 제출을 막음
            }

            // 비밀번호와 비밀번호 확인 일치 여부 확인
            const password = document.querySelector('input[name="password"]').value;
            const passwordCheck = document.querySelector('input[name="passwordCheck"]').value;

            if (password !== passwordCheck) {
                message.style.color = 'red';
                message.textContent = '비밀번호가 일치하지 않습니다.';
                return false;  // 폼 제출을 막음
            }

            // 모든 필드가 제대로 입력되었으면 폼을 제출
            return true;
        }
    </script>


</head>
<body>
    <p style="color:#fff"><a href="index.html">🏠home</a>
    <h2>회원가입✒️</h2>
        <form id="registerForm" action="register_process.php" method="POST" onsubmit="return validateForm();">
        <!-- 성명 -->
        <label>성명:</label>
        <input type="text" name="name" required>
        <br>

        <!-- ID -->
        <label>ID:</label>
        <input type="text" name="userID" required>
        <button type="button" onclick="checkUserID()">ID 중복 여부 확인</button>
        <span id="id-check-message"></span>
        <br>

        <!-- Email -->
        <label>Email:</label>
        <input type="email" name="email" required>
        <br>

        <!-- 주민등록번호 -->
        <label>주민등록번호:</label>
        <input type="text" name="identifNum1" maxlength="6" required>
        <span>-</span>
        <input type="password" name="identifNum2" maxlength="7" required>
        <br>

        <!-- 주소 -->
        <label>주소:</label>
        <input type="text" name="address" required>
        <br>

        <!-- 휴대폰 번호 -->
        <label>휴대폰번호:</label>
        <input type="text" width=35% name="phoneNumber1" maxlength="3" required>
        <span>-</span>
        <input type="text" width=35% name="phoneNumber2" maxlength="4" required>
        <span>-</span>
        <input type="text" width=35% name="phoneNumber3" maxlength="4" required>
        <br>

        <!-- 비밀번호 -->
        <label>비밀번호:</label>
        <input type="password" name="password" required>
        <br>

        <!-- 비밀번호 확인 -->
        <label>비밀번호 확인:</label>
        <input type="password" name="passwordCheck" required>
        <br>

        <button type="submit">회원가입</button>
        <span id="error-message"></span>
    </form>

</body>
</html>
