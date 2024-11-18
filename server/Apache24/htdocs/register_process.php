<?php
require_once 'config/db.php'; // DB ���� ����

// DB�� ȸ������ ���� ���
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ����� �Է� �� ��������
    $name = $_POST['username'];
    $userID = $_POST['userID'];
    $email = $_POST['email'];
    $identifNum = $_POST['identifNum'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber'];
    $password = $_POST['password'];
    $passwordCheck = $_POST['passwordCheck'];

    // ��й�ȣ�� ��й�ȣ Ȯ�� �� ��
    if ($password !== $passwordCheck) {
        die("��й�ȣ�� ��й�ȣ Ȯ�� ���� ��ġ���� �ʽ��ϴ�.");
    }

    // ��й�ȣ ��ȣȭ
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // �����ͺ��̽� �� users ���̺� ������ SQL ����
    $sql = "INSERT INTO users (name, userID, email, identifNum, address, phoneNumber, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // �÷��̽�Ȧ���� ������ ���ε�
    $stmt->bind_param("sssssss", $name, $userID, $email, $identifNum, $address, $phoneNumber, $hashedPassword);

    // SQL ���� �� ���� ���� Ȯ��
    if ($stmt->execute()) {
        echo "ȯ���մϴ�. <b><i>$userID</i></b> ��. ȸ�������� ���������� �Ϸ�Ǿ����ϴ�!";
    } else {
        echo "DB������ �߻��Ͽ����ϴ�. �����ڿ��� �����ϼ���." . $stmt->error;
    }
    
    // �غ�� ���� �ݱ�
    $stmt->close();

    //�����ͺ��̽� ���� �ݱ�
    $conn->close();
} else {
    echo "�߸��� ��û�Դϴ�.";
}
?>
