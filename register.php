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

        // 사용자 ID 중복 여부 확인 함수
        function checkUserID() {
            const userID = document.querySelector('input[name="userID"]').value.trim(); // 사용자 입력값에서 공백 제거
            const message = document.getElementById('id-check-message'); // 중복 확인 메시지를 출력할 엘리먼트

            // 입력된 ID가 비어 있는지 확인
            if (!userID) {
                message.style.color = 'red'; // 메시지 색상 설정
                message.textContent = 'ID를 입력해주세요.'; // 경고 메시지 출력
                isUserIDAvailable = false; // 상태값 업데이트
                return; // 중단
            }

            // AJAX 요청으로 ID 중복 여부 확인
            fetch('check_userID.php', {
                method: 'POST', // HTTP POST 요청
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, // 요청 헤더 설정
                body: 'userID=' + encodeURIComponent(userID) // 요청 본문 데이터
            })
                .then(response => response.json()) // 서버 응답을 JSON으로 변환
                .then(data => {
                    if (data.exists) { // 중복 ID 확인
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
                    console.error('Error:', error); // 오류 로그 출력
                    message.style.color = 'red';
                    message.textContent = '오류 발생. 다시 시도해주세요.';
                });
        }

        // 폼 제출 시 유효성 검사
        function validateForm() {
            const message = document.getElementById('error-message'); // 오류 메시지를 출력할 엘리먼트

            // ID 중복 여부 확인
            if (!isUserIDAvailable) {
                message.style.color = 'red';
                message.textContent = 'ID 중복 확인을 먼저 해주세요.';
                return false; // 폼 제출 방지
            }

            // 비밀번호와 비밀번호 확인 일치 여부 확인
            const password = document.querySelector('input[name="password"]').value; // 비밀번호 입력값
            const passwordCheck = document.querySelector('input[name="passwordCheck"]').value; // 비밀번호 확인 입력값

            if (password !== passwordCheck) { // 비밀번호 불일치 시
                message.style.color = 'red';
                message.textContent = '비밀번호가 일치하지 않습니다.';
                return false; // 폼 제출 방지
            }

            return true; // 모든 조건을 만족한 경우 폼 제출 허용
        }
    </script>
</head>

<body>
    <p style="color:#fff"><a href="index.php">🏠home</a><br><br>
    <h2>회원가입✒️<br></h2>
    <form id="registerForm" action="register_process.php" method="POST" onsubmit="return validateForm();" style="width: 600px">
        <!-- 성명 입력 -->
        <label>성명:</label>
        <input type="text" name="name" required style="width: 150px; margin: 10px 5px;">
        
        <!-- 이메일 입력 -->
        <label>Email:</label>
        <input type="email" name="email" required placeholder="abc123@example.com" style="width: 250px; margin: 0px 5px;">
        <br>

        <!-- ID 입력 -->
        <label>ID:</label>
        <input type="text" name="userID" required style="width: 320px; margin: 0px 5px;">
        <button type="button" onclick="checkUserID()" style="width: 140px;">ID 중복 여부 확인</button><br>
        <span id="id-check-message"></span>
        <br>

        <!-- 주민등록번호 입력 -->
        <label>주민등록번호:</label>
        <input type="text" name="identifNum1" maxlength="6" required placeholder="123456" style="width: 200px; margin: 0px 5px;">
        <span>-</span>
        <input type="password" name="identifNum2" maxlength="7" required placeholder="●●●●●●●" style="width: 200px;">
        <br>

        <!-- 주소 입력 -->
        <label>주소:</label>
        <input type="text" name="address" required style="width: 475px; margin: 0 5px;">
        <br>

        <!-- 휴대폰 번호 입력 -->
        <label>휴대폰번호:</label>
        <input type="text" name="phoneNumber1" maxlength="3" required placeholder="010" style="width: 100px; margin: 0 5px;">
        <span>-</span>
        <input type="text" name="phoneNumber2" maxlength="4" required placeholder="1234" style="width: 100px;">
        <span>-</span>
        <input type="text" name="phoneNumber3" maxlength="4" required placeholder="5678" style="width: 100px;">
        <br>

        <!-- 비밀번호 입력 -->
        <label>비밀번호:</label>
        <input type="password" name="password" required placeholder="영어, 특수문자, 숫자 포함 8자리 이상" style="width: 340px; margin-left: 20px;">
        <br>

        <!-- 비밀번호 확인 -->
        <label>비밀번호 확인:</label>
        <input type="password" name="passwordCheck" required placeholder="비밀번호 확인" style="width: 340px;">
        <br>

        <!-- 제출 버튼 -->
        <button type="submit">회원가입</button>
        <span id="error-message"></span>
    </form>
</body>
</html>
