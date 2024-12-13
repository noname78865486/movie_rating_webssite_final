<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

if (!$conn) {
    die("DB 연결 실패: " . mysqli_connect_error());
}

// 로그인 여부와 관리자 권한 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 이동
    exit;
}

$userID = $_SESSION['userID'];

// 관리자 권한 확인 (취약점 포함: SQL 인젝션 가능)
$sql = "SELECT role FROM users WHERE userID = '$userID'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    $isAdmin = ($userData['role'] === 'admin');
} else {
    $isAdmin = false;
}

if (!$isAdmin) {
    echo "<script>alert('관리자만 접근할 수 있습니다.'); window.location.href = 'dashboard.php';</script>";
    exit;
}

// 검색 조건 처리
$searchCategory = $_GET['search_category'] ?? '';
$searchKeyword = $_GET['search_keyword'] ?? '';
$whereClause = "";

if ($searchKeyword) {
    $searchKeyword = '%' . $searchKeyword . '%';
    // 취약점 포함: WHERE 절에 직접 변수를 삽입
    switch ($searchCategory) {
        case 'user_id':
            $whereClause = " WHERE u.userID LIKE '$searchKeyword'";
            break;
        case 'name':
            $whereClause = " WHERE u.name LIKE '$searchKeyword'";
            break;
        case 'email':
            $whereClause = " WHERE u.email LIKE '$searchKeyword'";
            break;
        case 'id_name':
            $whereClause = " WHERE u.userID LIKE '$searchKeyword' OR u.name LIKE '$searchKeyword'";
            break;
        case 'id_email':
            $whereClause = " WHERE u.userID LIKE '$searchKeyword' OR u.email LIKE '$searchKeyword'";
            break;
        case 'name_email':
            $whereClause = " WHERE u.name LIKE '$searchKeyword' OR u.email LIKE '$searchKeyword'";
            break;
        case 'all':
            $whereClause = " WHERE u.userID LIKE '$searchKeyword' OR u.name LIKE '$searchKeyword' OR u.email LIKE '$searchKeyword'";
            break;
    }
}

// SQL 쿼리 구성 (취약점 포함)
$sql = "SELECT u.id, u.userID, u.name, u.email, u.role, 
        (SELECT COUNT(*) FROM reviews r WHERE r.rating_user_idNum = u.id) AS review_count
        FROM users u" . $whereClause;

// SQL 실행 (취약점 포함: SQL 인젝션 가능)
$result = $conn->query($sql);

if (!$result) {
    die("쿼리 실행 실패: " . $conn->error . " SQL: " . $sql);
}

// 계정 삭제 처리 (취약점 포함: CSRF 가능)
if (isset($_POST['delete_user'])) {
    $deleteUserID = $_POST['delete_user_id'];
    $deleteSql = "DELETE FROM users WHERE userID = '$deleteUserID'"; // 취약점: SQL 인젝션 가능
    if ($conn->query($deleteSql)) {
        echo "<script>alert('유저가 삭제되었습니다.'); window.location.href = 'admin_users.php';</script>";
    } else {
        die("유저 삭제 실패: " . $conn->error);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
            display: block;
            width: 100%;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: #ecf0f1;
            min-height: 100vh;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>관리자 페이지</h2>
        <a href="admin_users.php" class="active">회원 관리</a>
        <a href="admin_notice.php">영화 데이터 관리</a>
        <a href="admin_notice.php">후기 관리</a>
        <a href="admin_notice.php">통계 및 분석</a>
        <a href="admin_notice.php">공지사항 관리</a>
        <a href="dashboard.php">메인으로</a>
        <a href="logout.php">로그아웃</a>
    </div>
    <div class="main-content">
        <h1>회원 관리</h1>
        <form method="GET" action="admin_users.php">
            <select name="search_category">
                <option value="all">전체</option>
                <option value="user_id">유저 ID</option>
                <option value="name">이름</option>
                <option value="email">이메일</option>
            </select>
            <input type="text" name="search_keyword" placeholder="검색어 입력">
            <button type="submit">검색</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>번호</th>
                    <th>유저 ID</th>
                    <th>이름</th>
                    <th>이메일</th>
                    <th>후기 개수</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['userID'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['review_count'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                <input type="hidden" name="delete_user_id" value="<?= $row['userID'] ?>">
                                <button type="submit" name="delete_user">삭제</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
