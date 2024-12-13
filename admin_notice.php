<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Board</title>
    <style>
        /* 기본 레이아웃 */
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

        /* 테이블 스타일 */
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

        .btn-enable {
            background-color: #27ae60;
        }

        .btn-disable {
            background-color: #c0392b;
        }

        .btn-delete {
            background-color: #e74c3c;
        }
    </style>
</head>
<body>
    <!-- 사이드바 -->
    <div class="sidebar">
        <h2>관리자 페이지</h2>
        <a href="admin_users.php">회원 관리</a>
        <a href="admin_notice.php">영화 데이터 관리</a>
        <a href="admin_notice.php">후기 관리</a>
        <a href="admin_notice.php">통계 및 분석</a>
        <a href="admin_notice.php">공지사항 관리</a>
        <a href="admin_notice.php">관리자 계정 관리</a>
        <a href="dashboard.php">back to main</a>
        <a href="logout.php">🔓Logout</a>
    </div>

    <!-- 메인 콘텐츠 -->
    <div class="main-content">
        <h3>'후기 관리'부터 '관리자 계정 관리'까지 기능은 개발 중인 기능입니다.</h3>
    </div>
</body>
</html>
