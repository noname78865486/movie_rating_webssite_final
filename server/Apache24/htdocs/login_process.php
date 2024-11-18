<?php
// login_process.php
require_once 'config/db.php'; // DB ���� ����
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id; // ���ǿ� ����� ID ����
            echo "�α��� ����!";
        } else {
            echo "��й�ȣ�� Ʋ�Ƚ��ϴ�.";
        }
    } else {
        echo "����ڸ� ã�� �� �����ϴ�.";
    }

    $stmt->close();
    $conn->close();
}
?>
