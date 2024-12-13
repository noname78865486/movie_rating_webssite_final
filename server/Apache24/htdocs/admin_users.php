<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

if (!$conn) {
    die("DB 연결 실패: " . mysqli_connect_error());
}

// 로그인 여부와 관리자 권한 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인 페이지로 리다이렉트
    exit;
}

$userID = $_SESSION['userID'];
var_dump($userID);

// 로그인한 사용자가 관리자 권한인지 확인
$sql = "SELECT role FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $userID);
$stmt->execute();
$userResult = $stmt->get_result();
if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    $isAdmin = $userData['role'] === 'admin';
} else {
    $isAdmin = false;
}

if (!$isAdmin) {
    echo "<script>alert('관리자만 접근할 수 있습니다.'); window.location.href = 'dashboard.php';</script>";
    exit;
}

// 검색 필터 처리
$searchCategory = $_GET['search_category'] ?? '';
$searchKeyword = $_GET['search_keyword'] ?? '';
$whereClause = ""; // 검색 필터가 없을 때 기본값 설정
$params = [];
$paramTypes = "";

// SQL 기본 쿼리
$sql = "SELECT u.id, u.userID, u.name, u.email, u.role, 
        (SELECT COUNT(*) FROM reviews r WHERE r.rating_user_idNum = u.id) AS review_count
        FROM users u";

// 검색 조건 추가
$whereClause = '';
if ($searchKeyword) {
    $searchKeyword = '%' . $searchKeyword . '%';
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
$sql .= $whereClause;

// SQL 쿼리 실행
$result = $conn->query($sql);

// 오류 발생 시 디버깅용 메시지 출력
if (!$result) {
    die("쿼리 실행 실패: " . $conn->error . " SQL: " . $sql);
}

// 계정 비활성화
// if (isset($_POST['disable_user'])) {
//     $disableUserID = $_POST['disable_user_id'];

//     if (empty($disableUserID)) {
//         echo "<script>alert('비활성화할 유저 ID가 전달되지 않았습니다.');</script>";
//         exit;
//     }

//     $updateSql = "UPDATE users SET role = 'admin' WHERE userID = ?";
//     $stmt = $conn->prepare($updateSql);

//     if (!$stmt) {
//         die("쿼리 준비 실패: " . $conn->error);
//     }

//     $stmt->bind_param('s', $disableUserID);

//     if ($stmt->execute()) {
//         echo "<script>alert('유저가 비활성화되었습니다.'); window.location.reload();</script>";
//     } else {
//         echo "<script>alert('유저 비활성화 실패. " . $stmt->error . "');</script>";
//     }
// }

// // 계정 활성화
// if (isset($_POST['able_user'])) {
//     $ableUserID = $_POST['able_user_id'];
//     if (empty($_POST['able_user_id'])) {
//         echo "<script>alert('활성화할 유저 ID가 전달되지 않았습니다.');</script>";
//         exit;
//     }
//     $updateSql = "UPDATE users SET role = 'activated' WHERE userID = ?";
//     $stmt = $conn->prepare($updateSql);
//     $stmt->bind_param('s', $ableUserID);
//     if ($stmt->execute()) {
//         echo "<script>alert('유저가 활성화되었습니다.'); window.location.reload();</script>";
//     } else {
//         echo "<script>alert('유저 활성화 실패. 다시 시도해주세요.');</script>";
//     }
//     if (!$stmt) {
//         die("쿼리 준비 실패: " . $conn->error);
//     }
// }

// 계정 삭제
if (isset($_POST['delete_user'])) {
    $deleteUserID = $_POST['delete_user_id'];
    $deleteSql = "DELETE FROM users WHERE userID = ?";
    $deletestmt = $conn->prepare($deleteSql);

    if (!$deletestmt) {
        die("쿼리 준비 실패: " . $conn->error);
    }

    $deletestmt->bind_param('s', $deleteUserID);
    if ($deletestmt->execute()) {
        if ($deletestmt->affected_rows > 0) {
            echo "<script>alert('유저가 삭제되었습니다.'); window.location.href = 'admin_users.php';</script>";
            exit;
        } else {
            echo "<script>alert('유저 삭제 실패. 다시 시도해주세요.'); window.location.href = 'admin_users.php';</script>";
            exit;
        }
    } else {
        die("쿼리 실행 실패: " . $deletestmt->error);
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <!-- <script>
        function confirmDisable(userID) {
            if (confirm('정말 비활성화하시겠습니까?')) {
                document.getElementById('disable-user-form-' + userID).submit();
            }
        }

        function confirmable(userID) {
            if (confirm('정말 활성화하시겠습니까?')) {
                document.getElementById('able-user-form-' + userID).submit();
            }
        }

        function confirmDelete(userID) {
            if (confirm('정말 삭제하시겠습니까?')) {
                document.getElementById('delete-user-form-' + userID).submit();
            }
        }
    </script> -->
    <style>
        body {
            display: flex;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* 왼쪽 네비게이션 바 */
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            margin: 0;
            padding: 20px 0;
            font-size: 22px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
            width: 100%;
            text-align: left;
            display: block;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .sidebar a.active {
            background-color: #2980b9;
            font-weight: bold;
        }

        /* 메인 콘텐츠 */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #ecf0f1;
            min-height: 100vh;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        table th {
            background-color: #3498db;
            color: white;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* .btn-enable {
            background-color: #27ae60;
        }

        .btn-disable {
            background-color: #c0392b;
        } */

        .btn-delete {
            background-color: #e74c3c;
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
        <a href="admin_notice.php">관리자 계정 관리</a>
        <a href="dashboard.php">back to main</a>
        <a href="logout.php">🔓Logout</a>
    </div>

    <div class="main-content">
        <h1>회원 관리</h1>
        <div class="table-container">
            <div class="search-bar">
                <form method="GET" action="admin_users.php">
                    <select name="search_category">
                        <option value="all" <?= $searchCategory === 'all' ? 'selected' : ''; ?>>전체</option>
                        <option value="user_id" <?= $searchCategory === 'user_id' ? 'selected' : ''; ?>>유저 ID</option>
                        <option value="name" <?= $searchCategory === 'name' ? 'selected' : ''; ?>>이름</option>
                        <option value="email" <?= $searchCategory === 'email' ? 'selected' : ''; ?>>이메일</option>
                        <option value="id_name" <?= $searchCategory === 'id_name' ? 'selected' : ''; ?>>ID + 이름</option>
                        <option value="id_email" <?= $searchCategory === 'id_email' ? 'selected' : ''; ?>>ID + 이메일</option>
                        <option value="name_email" <?= $searchCategory === 'name_email' ? 'selected' : ''; ?>>이름 + 이메일</option>
                    </select>
                    <input type="text" name="search_keyword" value="<?= str_replace('%', '', $searchKeyword); ?>" placeholder="검색어 입력">
                    <button type="submit">검색</button>
                </form>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>번호</th>
                        <th>유저 ID</th>
                        <th>이름</th>
                        <th>이메일</th>
                        <th>후기 작성 개수</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['userID']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= $row['review_count'] ?></td>
                                <td>
                                    <form method="POST" style="background-color: transparent;" action="" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                    <input type="hidden" name="delete_user_id" value="<?= htmlspecialchars($row['userID']) ?>">
                                        <button type="submit" name="delete_user">삭제</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">검색 결과가 없습니다.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>