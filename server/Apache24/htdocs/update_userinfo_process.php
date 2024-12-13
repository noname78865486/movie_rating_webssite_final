<?php
require_once 'config/db.php'; // DB 연결

session_start();

// 로그인 여부 확인
if (!isset($_SESSION['userID'])) {
    echo '<script>alert("로그인 후 이용해주세요."); window.location.href = "login.php";</script>';
    exit;
}

$userID = $_SESSION['userID']; // 세션에서 로그인한 사용자 ID 가져오기

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $identifNum = $_POST['identifNum1'] . '-' . $_POST['identifNum2'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber1'] . '-' . $_POST['phoneNumber2'] . '-' . $_POST['phoneNumber3'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // 중복 확인 (Email, 주민등록번호, 연락처)
    $sql = "SELECT * FROM users WHERE (email = ? OR identifNum = ? OR phoneNumber = ?) AND userID != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $email, $identifNum, $phoneNumber, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['email'] === $email) {
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

    // 업데이트 SQL
    if ($password) {
        $sql = "UPDATE users SET name = ?, email = ?, identifNum = ?, address = ?, phoneNumber = ?, password = ? WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $name, $email, $identifNum, $address, $phoneNumber, $password, $userID);
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, identifNum = ?, address = ?, phoneNumber = ? WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $identifNum, $address, $phoneNumber, $userID);
    }

    // 업데이트 실행
    if ($stmt->execute()) {
        echo '<script>alert("정보가 성공적으로 수정되었습니다."); window.location.href = "my_page.php";</script>';
    } else {
        echo '<script>alert("정보 수정에 실패했습니다. 다시 시도해주세요."); window.history.back();</script>';
    }

    $stmt->close();
    $conn->close();
} else {
    echo '<script>alert("잘못된 접근입니다."); window.history.back();</script>';
    exit;
}
?>
