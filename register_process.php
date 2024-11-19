<?php
require_once 'config/db.php'; // DB 연결

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $userID = $_POST['userID'];
    $email = $_POST['email'];
    $identifNum = $_POST['identifNum1'] . '-' . $_POST['identifNum2'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber1'] . '-' . $_POST['phoneNumber2'] . '-' . $_POST['phoneNumber3'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 중복 확인 (ID, Email, 주민등록번호, 연락처)
    $sql = "SELECT 1 FROM users WHERE userID = ? OR email = ? OR identifNum = ? OR phoneNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $userID, $email, $identifNum, $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    // 중복 항목에 맞는 메시지 출력
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['userID'] == $userID) {
            echo '<script>alert("이미 사용 중인 ID입니다."); window.history.back();</script>';
            exit;
        } elseif ($row['email'] == $email) {
            echo '<script>alert("이미 사용 중인 Email입니다."); window.history.back();</script>';
            exit;
        } elseif ($row['identifNum'] == $identifNum) {
            echo '<script>alert("이미 회원가입한 회원의 주민등록번호입니다."); window.history.back();</script>';
            exit;
        } elseif ($row['phoneNumber'] == $phoneNumber) {
            echo '<script>alert("이미 사용 중인 연락처입니다."); window.history.back();</script>';
            exit;
        }
    }

    // 데이터 저장
    $sql = "INSERT INTO users (name, userID, email, identifNum, address, phoneNumber, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $userID, $email, $identifNum, $address, $phoneNumber, $password);

    if ($stmt->execute()) {
        echo '<script>alert("회원가입이 성공적으로 완료되었습니다! 로그인해주세요"); window.location.href = "login.php";</script>';
    } else {
        echo '<script>alert("회원가입 실패. 다시 시도해주세요."); window.history.back();</script>';
    }

    $stmt->close();
    $conn->close();
}
?>
