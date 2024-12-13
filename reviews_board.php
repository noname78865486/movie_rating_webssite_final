```php
<?php
require_once 'config/db.php'; // 데이터베이스 연결 설정 파일
session_start(); // 세션 시작

// 로그인 여부를 판단 (로그인한 경우 $_SESSION['userID']가 존재한다고 가정)
$isLoggedIn = isset($_SESSION['userID']);
$userID = $_SESSION['userID'] ?? ''; // 로그인한 사용자 ID를 세션에서 가져옴

// 로그인한 사용자 정보에서 id와 role 가져오기
$sql = "SELECT id, role FROM users WHERE userID = '$userID'";
$userResult = $conn->query($sql); // 사용자 정보 쿼리 실행

if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc(); // 사용자 데이터를 가져옴
    $currentUserId = $userData['id']; // 로그인한 사용자의 id
    $isAdmin = $userData['role'] === 'admin'; // admin 여부 확인
} else {
    $currentUserId = null;
    $isAdmin = false;
}

// 검색 카테고리와 키워드 처리
$searchCategory = $_GET['category'] ?? ''; // 검색 카테고리 (제목, 작성자, 영화제목)
$searchKeyword = $_GET['search'] ?? ''; // 검색 키워드

// 페이지네이션 설정
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지 번호
$moviesPerPage = 10; // 한 페이지에 표시할 리뷰 수
$offset = ($currentPage - 1) * $moviesPerPage; // 시작 위치 계산

// 기본 SQL 쿼리 작성
$sql = "SELECT r.id, r.movie_id, m.title AS movie_title, m.poster_path,
                r.rating_user_idNum, r.title AS review_title, r.content,
                r.rating, r.visibility, r.created_at, r.file_path, u.userID  
        FROM reviews r
        JOIN users u ON r.rating_user_idNum = u.id
        JOIN movies m ON r.movie_id = m.id";

// 검색 조건 추가
if (!empty($searchCategory) && !empty($searchKeyword)) {
    if ($searchCategory == '작성자') {
        $sql .= " WHERE u.userID LIKE '%$searchKeyword%'"; // 작성자 검색
    } elseif ($searchCategory == '제목') {
        $sql .= " WHERE r.title LIKE '%$searchKeyword%'"; // 리뷰 제목 검색
    } elseif ($searchCategory == '영화제목') {
        $sql .= " WHERE m.title LIKE '%$searchKeyword%'"; // 영화 제목 검색
    } elseif ($searchCategory == '전체') {
        $sql .= " WHERE (r.title LIKE '%$searchKeyword%' OR u.userID LIKE '%$searchKeyword%' OR m.title LIKE '%$searchKeyword%')"; // 모든 컬럼 검색
    }
}

// 페이지네이션 적용
$sql .= " ORDER BY r.created_at ASC LIMIT $offset, $moviesPerPage";

// 쿼리 실행
$result = $conn->query($sql);

// 총 리뷰 수 계산 (페이지네이션을 위한)
$totalSql = "SELECT COUNT(*) AS total FROM reviews r
             JOIN users u ON r.rating_user_idNum = u.id
             JOIN movies m ON r.movie_id = m.id";
$totalResult = $conn->query($totalSql);
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
        // 리뷰 추가 버튼 클릭 시 로그인 여부 확인
        function handleAddreview(isLoggedIn) {
            if (isLoggedIn) {
                window.location.href = 'add_review.php'; // 로그인한 사용자만 접근 가능
            } else {
                alert('로그인한 회원만 가능한 기능입니다.'); // 경고 메시지 표시
            }
        }

        // 홈 버튼 클릭 시 로그인 여부 확인
        function handleHomeClick(isLoggedIn) {
            if (isLoggedIn) {
                window.location.href = 'dashboard.php'; // 로그인한 사용자의 대시보드로 이동
            } else {
                window.location.href = 'index.php'; // 비로그인 사용자는 홈 페이지로 이동
            }
        }
    </script>
    <style>
        /* 테이블 스타일 */
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
        /* 페이지네이션 스타일 */
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

    <!-- 로그인한 사용자 정보 표시 -->
    <div class="user-info">
        <p style="text-align: center;"><로그인정보></p>
        <?php if ($isLoggedIn): ?>
            <p><strong>ID:</strong> <?= $userID ?></p>
            <p><strong>login at:</strong> <?= date('Y-m-d H:i:s') ?></p>
            <a href="logout.php" style="text-align: center;">🔓 Logout</a>
        <?php else: ?>
            <p>로그인해주세요</p>
        <?php endif; ?>
    </div>

    <main>
        <!-- 검색 폼 -->
        <form method="get" action="" style="text-align: center; margin-bottom: 20px;">
            <select name="category">
                <option value="전체">전체</option>
                <option value="제목" <?= $searchCategory == '제목' ? 'selected' : '' ?>>제목</option>
                <option value="작성자" <?= $searchCategory == '작성자' ? 'selected' : '' ?>>작성자</option>
                <option value="영화제목" <?= $searchCategory == '영화제목' ? 'selected' : '' ?>>영화제목</option>
            </select>
            <input type="text" name="search" placeholder="검색어 입력" value="<?= $searchKeyword ?>">
            <button type="submit">검색</button>
        </form>

        <!-- 리뷰 테이블 -->
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
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['movie_title'] ?></td>
                            <td>
                                <?php if ($row['visibility'] === '비공개' && !$isAdmin && $row['rating_user_idNum'] !== $currentUserId): ?>
                                    <span class="secret-post">비밀글입니다.</span>
                                <?php else: ?>
                                    <?= $row['review_title'] ?>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['rating'] ?></td>
                            <td><?= $row['userID'] ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td><a href="review_detail.php?id=<?= $row['id'] ?>">상세보기</a></td>
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
                <a href="?page=<?= $currentPage - 1 ?>&category=<?= $searchCategory ?>&search=<?= $searchKeyword ?>">◀ 이전</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&category=<?= $searchCategory ?>&search=<?= $searchKeyword ?>" 
                   style="<?= $i == $currentPage ? 'font-weight: bold;' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&category=<?= $searchCategory ?>&search=<?= $searchKeyword ?>">다음 ▶</a>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>© 2024 My Movie List</p>
    </footer>
</body>
</html>