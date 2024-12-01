<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

// 로그인 여부를 판단 (로그인한 경우 $_SESSION['userID']가 존재한다고 가정)
$isLoggedIn = isset($_SESSION['userID']);
$userID = $_SESSION['userID'] ?? ''; // 로그인한 사용자 ID

// 검색 키워드 처리
$searchKeyword = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';

// SQL 쿼리 시작
$sql = "SELECT r.id, r.movie_id, m.title AS movie_title, m.poster_path,
                r.rating_user_idNum, r.title AS review_title, r.content,
                r.rating, r.visibility, r.created_at, r.file_path, u.userID  
        FROM reviews r
        JOIN users u ON r.rating_user_idNum = u.id
        JOIN movies m ON r.movie_id = m.id
        WHERE r.visibility = 'public' 
        OR r.rating_user_idNum = ? 
        ORDER BY r.created_at DESC;";

// 검색 조건 추가 (검색어가 있을 때)
if ($searchKeyword) {
    $sql .= " AND (
        m.title LIKE ? OR
        r.title LIKE ? OR
        r.content LIKE ? OR
        u.userID LIKE ?
    )";
}

// 쿼리 준비
$stmt = $conn->prepare($sql);

// 검색어가 있을 경우 4개의 LIKE 파라미터 바인딩
if ($searchKeyword) {
    $stmt->bind_param('sssss', $userID, $searchKeyword, $searchKeyword, $searchKeyword, $searchKeyword);
} else {
    // 검색어가 없을 경우 1개의 파라미터만 바인딩
    $stmt->bind_param('i', $userID);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews✨</title>
    <script>
        // 리뷰 추가 클릭 시 로그인 여부를 판단하여 로그인하지 않은 유저 차단
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
        table th:nth-child(3), table td:nth-child(3) { width: 30%; } /* 후기제목 */
        table th:nth-child(4), table td:nth-child(4) { width: 5%; } /* 평점 */
        table th:nth-child(5), table td:nth-child(5) { width: 10%; } /* 작성자ID */
        table th:nth-child(6), table td:nth-child(6) { width: 10%; } /* 작성시간 */
        table th:nth-child(7), table td:nth-child(6) { width: 7%; } /* 상세보기 */
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
        }
        .no-results {
            text-align: center;
            font-size: 18px;
            color: red;
            margin-top: 20px;
        }
        .secret-post {
            color: #999;
            font-style: italic;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- 스타일시트 경로 -->
</head>
<body>
    <header>
        <h1>Reviews✨</h1>
        <nav>
            <!-- 로그인 여부를 JavaScript로 전달 -->
            <a href="#" onclick="handleHomeClick(<?= $isLoggedIn ? 'true' : 'false' ?>)">🏠Home</a>
            <a href="#" onclick="handleAddreview(<?= $isLoggedIn ? 'true' : 'false' ?>)">➕후기 추가</a>
        </nav>
    </header>

    <main>
        <!-- 검색 폼 -->
        <form method="get" action="" style="text-align: center; margin-bottom: 20px;">
            <input type="text" name="search" placeholder="검색어 입력" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">검색</button>
        </form>
        
        <table border="1">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>영화제목</th>
                    <th>후기제목</th>
                    <th>평점</th>
                    <th>작성자</th>
                    <th>작성시간</th>
                    <th>상세보기</th>
                </tr>
            </thead>   
            <tbody>
                <?php if ($result && $result->num_rows > 0) : ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['movie_title']) ?></td>
                            <td>
                                <?php 
                                // 비밀글 처리
                                if ($row['visibility'] == 'private' && $row['rating_user_idNum'] != $userID) {
                                    echo '<span class="secret-post">비밀글입니다.</span>';
                                } else {
                                    echo htmlspecialchars($row['review_title']);
                                }
                                ?>
                            </td>
                            <td><?= $row['rating'] ?></td>
                            <td><?= htmlspecialchars($row['userID']) ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td><a style="color: blue;" href="review_detail.php?id=<?= $row['id'] ?>">상세보기</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">등록된 리뷰가 없습니다.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 검색 결과가 없을 경우 메시지 출력 -->
        <?php if ($searchKeyword && (!$result || $result->num_rows == 0)): ?>
            <div class="no-results">검색 결과가 없습니다.</div>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2024 My Movie List</p>
    </footer>
</body>
</html>
