<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_POST['userID'];

    // 중복 체크 쿼리
    $sql_check = "SELECT COUNT(*) AS count FROM users WHERE userID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $userID);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    // 결과 반환
    $response = ['exists' => $row['count'] > 0];
    echo json_encode($response);

    $stmt_check->close();
    $conn->close();
}
