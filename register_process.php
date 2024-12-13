<?php
require_once 'config/db.php'; // DB 연결

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name']; // 사용자 이름
    $userID = $_POST['userID']; // 사용자 ID
    $email = $_POST['email']; // 사용자 이메일
    $identifNum = $_POST['identifNum1'] . '-' . $_POST['identifNum2']; // 주민등록번호 앞뒤 합치기
    $address = $_POST['address']; // 사용자 주소
    $phoneNumber = $_POST['phoneNumber1'] . '-' . $_POST['phoneNumber2'] . '-' . $_POST['phoneNumber3']; // 전화번호 조합
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 비밀번호 해시 처리

    // 중복 확인 (ID, Email, 주민등록번호, 연락처)
    $sql = "SELECT * FROM users WHERE userID = '$userID' OR email = '$email' OR identifNum = '$identifNum' OR phoneNumber = '$phoneNumber'";
    $result = $conn->query($sql); // 쿼리 실행

    // 중복 항목에 맞는 메시지 출력
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) { // 중복 결과를 순회하며 확인
            if ($row['userID'] === $userID) {
                echo '<script>alert("이미 사용 중인 ID입니다."); window.history.back();</script>';
                exit;
            } elseif ($row['email'] === $email) {
                echo '<script>alert("이미 사용 중인 Email입니다."); window.history.back();</script>';
                exit;
            } elseif ($row['identifNum'] === $identifNum) {
                echo '<script>alert("이미 회원가입한 회원의 주민등록번호입니다."); window.history.back();</script>';
                exit;
            } elseif ($row['phoneNumber'] === $phoneNumber) {
                echo '<script>alert("이미 사용 중인 연락처입니다."); window.history.back();</script>';
                exit;
            }
        }
    }

    // 데이터 저장
    $sql = "INSERT INTO users (name, userID, email, identifNum, address, phoneNumber, password) 
            VALUES ('$name', '$userID', '$email', '$identifNum', '$address', '$phoneNumber', '$password')";
    if ($conn->query($sql) === TRUE) {
        // 성공 메시지와 로그인 페이지로 리다이렉트
        echo '<script>alert("회원가입이 성공적으로 완료되었습니다! 로그인해주세요"); window.location.href = "login.php";</script>';
    } else {
        // 실패 메시지 출력
        echo '<script>alert("회원가입 실패. 다시 시도해주세요."); window.history.back();</script>';
    }

    $conn->close(); // DB 연결 종료
}
?>
