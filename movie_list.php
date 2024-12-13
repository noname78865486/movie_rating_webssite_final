<?php
// DB 연결
require_once 'config/db.php'; // DB 연결 설정 파일

// 세션 시작
session_start();

// 로그인 여부 확인
$isLoggedIn = isset($_SESSION['user_id']); // 세션에 user_id가 설정되어 있는지 확인

// 검색 카테고리와 키워드 가져오기
$searchCategory = $_GET['search_category'] ?? ''; // GET 파라미터에서 검색 카테고리 가져오기 (기본값은 빈 문자열)
$searchKeyword = $_GET['search_keyword'] ?? ''; // GET 파라미터에서 검색 키워드 가져오기 (기본값은 빈 문자열)

// 페이지네이션 변수 설정
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // GET 파라미터에서 현재 페이지 번호 가져오기 (기본값은 1)
$moviesPerPage = 10; // 한 페이지에 표시할 영화 수
$offset = ($currentPage - 1) * $moviesPerPage; // 시작 위치 계산

// SQL 쿼리 시작
$sql = "SELECT id, title, director, release_date, genre, IFNULL(rating, 0) AS rating FROM movies"; // 기본 영화 목록 조회 쿼리

// 매개변수 배열 초기화
$params = [];
$types = '';

// 검색어가 있을 경우 WHERE 조건 추가
if ($searchKeyword) {
    $searchKeyword = '%' . $searchKeyword . '%'; // LIKE 조건에 사용할 검색어 패턴 생성
    if ($searchCategory == 'title') { // 제목으로 검색
        $sql .= " WHERE title LIKE ?";
        $params = [$searchKeyword];
        $types = 's';
    } elseif ($searchCategory == 'director') { // 감독으로 검색
        $sql .= " WHERE director LIKE ?";
        $params = [$searchKeyword];
        $types = 's';
    } elseif ($searchCategory == 'genre') { // 장르로 검색
        $sql .= " WHERE genre LIKE ?";
        $params = [$searchKeyword];
        $types = 's';
    } elseif ($searchCategory == 'title_director') { // 제목 또는 감독으로 검색
        $sql .= " WHERE title LIKE ? OR director LIKE ?";
        $params = [$searchKeyword, $searchKeyword];
        $types = 'ss';
    } elseif ($searchCategory == 'title_genre') { // 제목 또는 장르로 검색
        $sql .= " WHERE title LIKE ? OR genre LIKE ?";
        $params = [$searchKeyword, $searchKeyword];
        $types = 'ss';
    } elseif ($searchCategory == 'genre_director') { // 장르 또는 감독으로 검색
        $sql .= " WHERE genre LIKE ? OR director LIKE ?";
        $params = [$searchKeyword, $searchKeyword];
        $types = 'ss';
    } elseif ($searchCategory == 'total') { // 제목, 감독 또는 장르로 검색
        $sql .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?";
        $params = [$searchKeyword, $searchKeyword, $searchKeyword];
        $types = 'sss';
    }
}

// 정렬 조건 추가
$sql .= " ORDER BY id ASC"; // ID를 기준으로 오름차순 정렬

// 페이지네이션 추가
$sql .= " LIMIT ? OFFSET ?"; // 지정된 개수만큼 출력하고 시작 위치를 설정
$params[] = $moviesPerPage;
$params[] = $offset;
$types .= 'ii'; // LIMIT와 OFFSET에 대한 매개변수 타입

// 쿼리 준비
$stmt = $conn->prepare($sql);

// 매개변수 바인딩
if ($params) {
    $stmt->bind_param($types, ...$params); // 매개변수 바인딩
}

// 쿼리 실행
$stmt->execute();
if (!$stmt) {
    die("SQL Error: " . $conn->error); // SQL 실행 실패 시 에러 출력
}
$result = $stmt->get_result();
if (!$result) {
    die("Query returned no results or failed."); // 결과 반환 실패 시 에러 출력
}

// 전체 영화 수 가져오기
$totalCountSql = "SELECT COUNT(*) AS total_count FROM movies"; // 총 영화 개수 조회 쿼리
if ($searchKeyword) {
    $totalCountSql .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?"; // 검색 조건이 있는 경우 WHERE 조건 추가
    $totalCountStmt = $conn->prepare($totalCountSql);
    $totalCountStmt->bind_param('sss', $searchKeyword, $searchKeyword, $searchKeyword); // 검색어 바인딩
} else {
    $totalCountStmt = $conn->prepare($totalCountSql);
}
$totalCountStmt->execute();
$totalCountResult = $totalCountStmt->get_result();
$totalCountRow = $totalCountResult->fetch_assoc();
$totalCount = $totalCountRow['total_count']; // 총 영화 개수
$totalPages = ceil($totalCount / $moviesPerPage); // 전체 페이지 수 계산
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 목록</title>
    <script>
        function handleAddmovie(isLoggedIn) {
            if (isLoggedIn) {
                window.location.href = 'add_movie.php';
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
        table th:nth-child(1), table td:nth-child(1) { width: 5%; } /* No.(ID) */
        table th:nth-child(2), table td:nth-child(2) { width: 30%; } /* 제목 */
        table th:nth-child(3), table td:nth-child(3) { width: 10%; } /* 감독 */
        table th:nth-child(4), table td:nth-child(4) { width: 10%; } /* 개봉일 */
        table th:nth-child(5), table td:nth-child(5) { width: 10%; } /* 장르 */
        table th:nth-child(6), table td:nth-child(6) { width: 10%; } /* 평점 */
        table th:nth-child(7), table td:nth-child(6) { width: 7%; } /* 상세보기 */
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
        .no-data {
            text-align: center;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body style="height: 100%;">
    <header>
        <h1 style="margin-top: 60px;">영화 목록</h1>
        <nav>
            <a href="#" onclick="handleHomeClick(<?= $isLoggedIn ? 'true' : 'false' ?>)">🏠Home</a>
            <a href="#" onclick="handleAddmovie(<?= $isLoggedIn ? 'true' : 'false' ?>)">➕영화 추가</a>
        </nav>
    </header>
    <!-- 로그인한 사용자 정보 표시 -->
    <div class="user-info">
    <p style="text-align: center;"><로그인정보></p>
    <?php if ($isLoggedIn): ?>
        <p><strong>ID:</strong> <?= $_SESSION['userID'] ?></p>
        <p><strong>login at:</strong> <?= $_SESSION['login_time'] ?></p>
        <a href="logout.php" style="text-align: center;">🔓 Logout</a>
    <?php else: ?>
        <p>로그인해주세요</p>
        <?php endif; ?>
    </div>

    <main>
        <form action="movie_list.php" method="get">
            <div>
                <label for="search_category">검색 카테고리:</label>
                <select name="search_category" id="search_category">
                    <option value="total">전체</option>
                    <option value="title" <?= $searchCategory === 'title' ? 'selected' : ''; ?>>제목</option>
                    <option value="director" <?= $searchCategory === 'director' ? 'selected' : ''; ?>>감독</option>
                    <option value="genre" <?= $searchCategory === 'genre' ? 'selected' : ''; ?>>장르</option>
                    <option value="title_director" <?= $searchCategory === 'title_director' ? 'selected' : ''; ?>>제목 + 감독</option>
                    <option value="title_genre" <?= $searchCategory === 'title_genre' ? 'selected' : ''; ?>>제목 + 장르</option>
                    <option value="genre_director" <?= $searchCategory === 'genre_director' ? 'selected' : ''; ?>>장르 + 감독</option>
                </select>
            </div>
            <div>
                <label for="search_keyword">검색어:</label>
                <input type="text" id="search_keyword" name="search_keyword" 
                    value="<?= isset($_GET['search_keyword']) ? str_replace('%', '', $_GET['search_keyword']) : '' ?>">
            </div>
            <button type="submit">검색</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>제목</th>
                    <th>감독</th>
                    <th>개봉일</th>
                    <th>장르</th>
                    <th>평점</th>
                    <th>상세보기</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = ($currentPage - 1) * $moviesPerPage + 1; // 현재 페이지에 따른 시작 번호 계산 ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['title'] ?></td>
                            <td><?= $row['director'] ?></td>
                            <td><?= $row['release_date'] ?></td>
                            <td><?= $row['genre'] ?></td>
                            <td><?= $row['rating'] == 0 ? '(후기 없음)' : number_format($row['rating'], 1) . '/10' ?></td>
                            <td><a style="color: blue;" href="movie_detail.php?id=<?= $row['id'] ?>">보기</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">검색 결과가 없습니다.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <nav>
            <ul style="list-style: none; display: flex; justify-content: center; padding: 0;">
                <?php if ($currentPage > 1): ?>
                    <li><a href="?page=<?= $currentPage - 1 ?>&search_category=<?= $searchCategory ?>&search_keyword=<?= str_replace('%', '', $searchKeyword) ?>">이전</a></li>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                    <li style="margin: 0 5px;">
                        <a href="?page=<?= $page ?>&search_category=<?= $searchCategory ?>&search_keyword=<?= str_replace('%', '', $searchKeyword) ?>"
                        style="<?= $page == $currentPage ? 'font-weight: bold; text-decoration: underline;' : '' ?>">
                        <?= $page ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li><a href="?page=<?= $currentPage + 1 ?>&search_category=<?= $searchCategory ?>&search_keyword=<?= str_replace('%', '', $searchKeyword) ?>">다음</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </main>
    <footer>
        <p>© 2024 My Movie List</p>
    </footer>
</body>
</html>