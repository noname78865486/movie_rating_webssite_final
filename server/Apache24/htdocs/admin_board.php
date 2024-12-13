<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Board</title>
    <style>
        /* 기본 페이지 레이아웃 */
        body {
            display: flex;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* 왼쪽 네비게이션 바 스타일 */
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

        /* 메인 콘텐츠 스타일 */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #ecf0f1;
            min-height: 100vh;
        }

        /* 유저 정보 섹션 */
        .user-info {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, 0.5);
            color: black;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .user-info a {
            color: black;
            text-decoration: none;
            font-weight: bold;
        }
        .user-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- 로그인한 사용자 정보 표시 -->
    <div class="user-info">
        <p style="text-align: center;"><strong>로그인정보</strong></p>
        <?php 
        session_start();
        if (isset($_SESSION['userID'])): ?>
            <p><strong>ID:</strong> <?= htmlspecialchars($_SESSION['userID']) ?></p>
            <p><strong>login at:</strong> <?= htmlspecialchars($_SESSION['login_time'] ?? 'Unknown') ?></p>
            <a href="logout.php" style="text-align: center;">🔓 Logout</a>
        <?php else: ?>
            <p>로그인해주세요</p>
        <?php endif; ?>
    </div>

    <!-- 좌측 네비게이션 바 -->
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
        <h3>좌측의 네비게이션 바에서 기능을 선택해주세요.</h3>
        <h3 style="color: red;">※ '후기 관리'부터 '관리자 계정 관리'까지 기능은 개발 중인 기능입니다.</h3>
    </div>
</body>
</html>
