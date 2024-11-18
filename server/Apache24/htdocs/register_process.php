<?php
require_once 'config/db.php'; // DB 연결 파일

// DB에 회원가입 정보 등록
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 사용자 입력 값 가져오기
    $name = $_POST['username'];
    $userID = $_POST['userID'];
    $email = $_POST['email'];
    $identifNum = $_POST['identifNum'];
    $address = $_POST['address'];
    $phoneNumber = $_POST['phoneNumber'];
    $password = $_POST['password'];
    $passwordCheck = $_POST['passwordCheck'];

    // 비밀번호와 비밀번호 확인 값 비교
    if ($password !== $passwordCheck) {
        die("비밀번호와 비밀번호 확인 값이 일치하지 않습니다.");
    }

    // 비밀번호 암호화
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 데이터베이스 내 users 테이블에 저장할 SQL 쿼리
    $sql = "INSERT INTO users (name, userID, email, identifNum, address, phoneNumber, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // 플레이스홀더에 데이터 바인딩
    $stmt->bind_param("sssssss", $name, $userID, $email, $identifNum, $address, $phoneNumber, $hashedPassword);

    // SQL 실행 및 성공 여부 확인
    if ($stmt->execute()) {
        echo "환영합니다. <b><i>$userID</i></b> 님. 회원가입이 성공적으로 완료되었습니다!";
    } else {
        echo "DB오류가 발생하였습니다. 관리자에게 문의하세요." . $stmt->error;
    }
    
    // 준비된 구문 닫기
    $stmt->close();

    //데이터베이스 연결 닫기
    $conn->close();
} else {
    echo "잘못된 요청입니다.";
}
?>
