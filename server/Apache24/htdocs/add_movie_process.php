<?php
// add_movie_process.php
require_once 'config/db.php'; // DB ���� ����

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $rating = $_POST['rating'];

    // �����ͺ��̽��� ����
    $sql = "INSERT INTO movies (title, rating) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $title, $rating);

    if ($stmt->execute()) {
        echo "��ȭ�� ��ϵǾ����ϴ�.";
    } else {
        echo "����: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>