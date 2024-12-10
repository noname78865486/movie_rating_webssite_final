<?php
require_once 'config/db.php'; // DB 연결
session_start(); // 세션 시작

// 로그인 여부를 판단 (로그인한 경우 $_SESSION['userID']가 존재한다고 가정)
$isLoggedIn = isset($_SESSION['userID']);
$userID = $_SESSION['userID'] ?? ''; // 로그인한 사용자 ID

// 검색 카테고리와 키워드 처리
$searchCategory = $_GET['category'] ?? ''; // 카테고리 (제목, 작성자, 영화제목)
$searchKeyword = $_GET['search'] ?? ''; // 검색어

// 페이지네이션 설정
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지
$moviesPerPage = 10; // 한 페이지에 표시할 영화 수
$offset = ($currentPage - 1) * $moviesPerPage; // 시작 위치 계산

// 기본 SQL 쿼리
$sql = "SELECT r.id, r.movie_id, m.title AS movie_title, m.poster_path,
                r.rating_user_idNum, r.title AS review_title, r.content,
                r.rating, r.visibility, r.created_at, r.file_path, u.userID  
        FROM reviews r
        JOIN users u ON r.rating_user_idNum = u.id
        JOIN movies m ON r.movie_id = m.id
        WHERE (r.visibility = '공개' OR r.rating_user_idNum = ?)";

// 매개변수 배열 초기화
$params = [$userID];
$paramTypes = 's'; // 기본 타입은 's' (userID)

// 검색 조건 추가
if (!empty($searchCategory) && !empty($searchKeyword)) {
    if ($searchCategory == '작성자') {
        // 작성자 검색 (users.userID로 검색)
        $sql .= " AND u.userID LIKE ?";
        $params[] = '%' . $searchKeyword . '%';
        $paramTypes .= 's';
    } elseif ($searchCategory == '제목') {
        // 후기 제목 검색
        $sql .= " AND r.title LIKE ?";
        $params[] = '%' . $searchKeyword . '%';
        $paramTypes .= 's';
    } elseif ($searchCategory == '영화제목') {
        // 영화 제목 검색
        $sql .= " AND m.title LIKE ?";
        $params[] = '%' . $searchKeyword . '%';
        $paramTypes .= 's';
    } elseif ($searchCategory == '전체') {
        // 전체 컬럼 검색 (제목, 작성자, 영화제목)
        $sql .= " AND (r.title LIKE ? OR u.userID LIKE ? OR m.title LIKE ?)";
        $params[] = '%' . $searchKeyword . '%';
        $params[] = '%' . $searchKeyword . '%';
        $params[] = '%' . $searchKeyword . '%';
        $paramTypes .= 'sss';
    }
}

// 페이지네이션을 위해 LIMIT과 OFFSET 추가
$sql .= " ORDER BY r.created_at ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $moviesPerPage;
$paramTypes .= 'ii';

// 쿼리 준비
$stmt = $conn->prepare($sql);

// 파라미터 바인딩
$stmt->bind_param($paramTypes, ...$params);

// 쿼리 실행
$stmt->execute();
$result = $stmt->get_result();

// 총 게시물 수 계산 (페이지네이션을 위한)
$totalSql = "SELECT COUNT(*) AS total FROM reviews r
             JOIN users u ON r.rating_user_idNum = u.id
             JOIN movies m ON r.movie_id = m.id
             WHERE (r.visibility = '공개' OR r.rating_user_idNum = ?)";
$totalStmt = $conn->prepare($totalSql);
$totalStmt->bind_param('s', $userID);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalReviews = $totalRow['total']; // 총 리뷰 수
$totalPages = ceil($totalReviews / $moviesPerPage); // 총 페이지 수
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews✨</title>
    <script>
        function handleAddreview(isLoggedIn) {
            if (isLoggedIn) {
                window.location.href = 'add_review.php';
            } else {
                alert('로그인한 회원만 가능한 기능입니다.');
            }
        }

        function handleHomeClick(isLoggedIn) {
            if (isLoggedIn) {
                window.location.href = 'dashboard.php';
            } else {
                window.location.href = 'index.php';
            }
        }
    </script>
    <style>
        table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
            table-layout: fixed;
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
            word-wrap: break-word;
        }
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
        }
        .secret-post {
            color: #999;
            font-style: italic;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            text-decoration: none;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Reviews✨</h1>
        <nav>
            <a href="#" onclick="handleHomeClick(<?= $isLoggedIn ? 'true' : 'false' ?>)">🏠Home</a>
            <a href="#" onclick="handleAddreview(<?= $isLoggedIn ? 'true' : 'false' ?>)">➕후기 추가</a>
        </nav>
    </header>

    <main>
        <!-- 검색 폼 -->
        <form method="get" action="" style="text-align: center; margin-bottom: 20px;">
            <select name="category">
                <option value="전체">전체</option>
                <option value="제목" <?= $searchCategory == '제목' ? 'selected' : '' ?>>제목</option>
                <option value="작성자" <?= $searchCategory == '작성자' ? 'selected' : '' ?>>작성자</option>
                <option value="영화제목" <?= $searchCategory == '영화제목' ? 'selected' : '' ?>>영화제목</option>
            </select>
            <input type="text" name="search" placeholder="검색어 입력" value="<?= htmlspecialchars($searchKeyword) ?>">
            <button type="submit">검색</button>
        </form>

        <table>
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
                                if ($row['visibility'] == '비공개' && $row['rating_user_idNum'] != $userID) {
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
                        <td colspan="7" class="no-data">검색 결과가 없습니다.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 페이지네이션 -->
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>&category=<?= htmlspecialchars($searchCategory) ?>&search=<?= htmlspecialchars($searchKeyword) ?>">◀ 이전</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&category=<?= htmlspecialchars($searchCategory) ?>&search=<?= htmlspecialchars($searchKeyword) ?>" 
                   style="<?= $i == $currentPage ? 'font-weight: bold;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&category=<?= htmlspecialchars($searchCategory) ?>&search=<?= htmlspecialchars($searchKeyword) ?>">다음 ▶</a>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2024 My Movie List</p>
    </footer>
</body>
</html>
