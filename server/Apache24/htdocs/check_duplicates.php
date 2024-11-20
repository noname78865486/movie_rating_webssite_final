<?php
require_once 'config/db.php'; // DB 연결 파일

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $identifNum = $_POST['identifNum'];
    $phoneNumber = $_POST['phoneNumber'];

    // 중복 확인 쿼리
    $sql = "SELECT 1 FROM users WHERE email = ? OR identifNum = ? OR phoneNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $identifNum, $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['exists' => true]); // 중복 있음
    } else {
        echo json_encode(['exists' => false]); // 중복 없음
    }

    $stmt->close();
    $conn->close();
}