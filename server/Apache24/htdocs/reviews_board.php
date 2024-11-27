<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

// 로그인 여부를 판단 (로그인한 경우 $_SESSION['userID']가 존재한다고 가정)
$isLoggedIn = isset($_SESSION['userID']);

// 로그인한 유저의 ID 확인
$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : null;

if ($isLoggedIn && $userID) {
    // MySQL DB에서 조건에 맞는 후기만 가져오기
    $sql = "SELECT r.id, r.title, r.content, u.userID, r.created_at, r.userID, r.rating, r.movie_id
            FROM reviews r
            JOIN users u ON r.userID = u.userID
            WHERE r.visibility = 'public' OR r.userID = ?
            ORDER BY r.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userID); // 로그인한 사용자의 ID를 바인딩
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 결과가 있다면 출력
        while ($post = $result->fetch_assoc()) {
            echo "<h3>" . htmlspecialchars($post['title']) . "</h3>";
            echo "<p>" . htmlspecialchars($post['content']) . "</p>";
            echo "<p>Posted by: " . htmlspecialchars($post['userID']) . " on " . $post['created_at'] . "</p>";
            echo "<hr>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews✨</title>
    <script>
        // 영화 추가 클릭 시 로그인 여부를 판단하여 로그인하지 않은 유저 차단
        function handleAddreview(isLoggedIn) {
            if (isLoggedIn) {
                // 로그인된 사용자일 경우 add_review.php로 이동
                window.location.href = 'add_review.php';
            } else {
                // 로그인되지 않은 경우 경고 메시지 표시
                alert('로그인한 회원만 가능한 기능입니다.');
            }
        }

        // home 클릭 시 로그인 여부에 따라 연결되는 index 페이지를 분리
        function handleHomeClick(isLoggedIn) {
            if (isLoggedIn) {
                // 로그인된 사용자일 경우 dashboard.php로 이동
                window.location.href = 'dashboard.php';
            } else {
                // 로그인된 사용자일 경우 index.php로 이동
                window.location.href = 'index.php';
            }
        }
    </script>
    <style>
        table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
            table-layout: fixed; /* 열 간격을 고정 */
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        table th {
            background-color: #f4f4f4;
        }
        table td {
            word-wrap: break-word; /* 긴 텍스트 줄바꿈 */
        }
        /* 각 열의 비율을 지정 (전체 열을 균일하게 설정) */
        table th:nth-child(1), table td:nth-child(1) { width: 5%; } /* No.(ID) */
        table th:nth-child(2), table td:nth-child(2) { width: 20%; } /* 영화제목 */
        table th:nth-child(3), table td:nth-child(2) { width: 30%; } /* 후기제목 */
        table th:nth-child(4), table td:nth-child(3) { width: 10%; } /* 평점 */
        table th:nth-child(5), table td:nth-child(4) { width: 10%; } /* 작성자ID */
        table th:nth-child(6), table td:nth-child(5) { width: 10%; } /* 작성일 */
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- 스타일시트 경로 -->
</head>
<body>
    <header>
        <h1>Reviews</h1>
        <nav>
            <!-- 로그인 여부를 JavaScript로 전달 -->
            <a href="#" onclick="handleHomeClick(<?= $isLoggedIn ? 'true' : 'false' ?>)">🏠Home</a>
            <a href="#" onclick="handleAddreview(<?= $isLoggedIn ? 'true' : 'false' ?>)">➕후기 추가</a>
        </nav>
    </header>

    <main>
        <table border="1">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>영화제목</th>
                    <th>후기제목</th>
                    <th>평점</th>
                    <th>작성자</th>
                    <th>작성시간</th>
                </tr>
            </thead>   
            <tbody>
                <?php if ($result->num_rows > 0) : ?>
                    <?php while ($post = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($post['id']) ?></td>
                            <td><?= htmlspecialchars($post['movie_id']) ?></td>
                            <td><?= htmlspecialchars($post['title']) ?></td>
                            <td><?= htmlspecialchars($post['rating']) ?></td>
                            <td><?= htmlspecialchars($post['userID']) ?></td>
                            <td><?= htmlspecialchars($post['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">등록된 리뷰가 없습니다.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>© 2024 My Movie List</p>
    </footer>
</body>
</html>
