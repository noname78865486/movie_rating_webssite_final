<?php
// DB 연결
require_once 'config/db.php';  // DB 연결 설정 파일

// 세션 시작
session_start();

// 로그인 여부 확인
$isLoggedIn = isset($_SESSION['user_id']);

// 검색어 처리
$searchKeyword = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';

// 페이지 번호 처리
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지
$moviesPerPage = 10; // 한 페이지에 표시할 영화 수
$offset = ($currentPage - 1) * $moviesPerPage; // 시작 위치 계산. offset은 현재 페이지에서 데이터를 시작하는 인덱스.

// SQL 쿼리 시작
$sql = "SELECT id, title, director, release_date, genre, IFNULL(rating, 0) AS rating FROM movies";

// 검색어가 있을 경우 WHERE 절 추가
if ($searchKeyword) {
    $sql .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?";
}

// 정렬 조건 및 페이징 조건 추가
$sql .= " ORDER BY id ASC LIMIT ? OFFSET ?";

// 쿼리 준비
$stmt = $conn->prepare($sql);

// 검색어와 페이징 파라미터 바인딩
if ($searchKeyword) {
    $stmt->bind_param('sssii', $searchKeyword, $searchKeyword, $searchKeyword, $moviesPerPage, $offset);
} else {
    $stmt->bind_param('ii', $moviesPerPage, $offset);
}

// 쿼리 실행
$stmt->execute();
$result = $stmt->get_result();

// 전체 영화 개수 가져오기 (페이지네이션 계산용)
$totalMoviesQuery = "SELECT COUNT(*) AS total FROM movies";
if ($searchKeyword) {
    $totalMoviesQuery .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?";
    $countStmt = $conn->prepare($totalMoviesQuery);
    $countStmt->bind_param('sss', $searchKeyword, $searchKeyword, $searchKeyword);
} else {
    $countStmt = $conn->prepare($totalMoviesQuery);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalMovies = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalMovies / $moviesPerPage); // 전체 페이지 수 계산
?>


<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 목록</title>
    <script>
        // 리뷰 추가 클릭 시 로그인 여부를 판단하여 로그인하지 않은 유저 차단
        function handleAddmovie(isLoggedIn) {
            if (isLoggedIn) {
                // 로그인된 사용자일 경우 add_review.php로 이동
                window.location.href = 'add_movie.php';
            } else {
                // 로그인되지 않은 경우 경고 메시지 표시
                alert('로그인한 회원만 가능한 기능입니다.');
            }
        }
        // 홈 페이지 클릭 시 로그인 여부에 따라 페이지 이동
        function handleHomeClick(isLoggedIn) {
            if (isLoggedIn) {
                window.location.href = 'dashboard.php';  // 로그인한 사용자 대시보드로 이동
            } else {
                window.location.href = 'index.php';  // 로그인하지 않은 경우 홈 페이지로 이동
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

    <main>
        <!-- 검색 폼 -->
        <form class="d-flex" role="search" method="get" action="movie_list.php">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button class="btn btn-outline-success" type="submit">검색</button>
        </form>

        <!-- 영화 목록 테이블 -->
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
                <?php if ($result->num_rows > 0) : ?>
                    <?php $no = $offset + 1; // offset+1을 사용하면 현재 페이지의 첫 번째 영화번호를 얻을 수 있음 ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $no++ ?></td> <!-- 번호 출력 후 1 증가 -->
                            <td><?= $row['title'] ?></td>
                            <td><?= $row['director'] ?></td>
                            <td><?= $row['release_date'] ?></td>
                            <td><?= $row['genre'] ?></td>
                            <td><?php if ($row['rating'] == 0): ?>
                                    (후기 없음)
                                <?php else: ?>
                                    <?= number_format($row['rating'], 1) ?>/10
                                <?php endif; ?></td>
                            <td><a style="color: blue;" href="movie_detail.php?id=<?= $row['id'] ?>">보기</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="no-data">등록된 영화가 없습니다.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    <nav style="text-align: center; margin: 20px 0;">
    <ul style="list-style: none; padding: 0; display: inline-flex;">
        <?php if ($currentPage > 1): ?>
            <li style="margin: 0 5px;">
                <a href="movie_list.php?page=<?= $currentPage - 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>">이전</a>
            </li>
        <?php endif; ?>
        
        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
            <li style="margin: 0 5px; <?= $page == $currentPage ? 'font-weight: bold;' : '' ?>">
                <a href="movie_list.php?page=<?= $page ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>"><?= $page ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <li style="margin: 0 5px;">
                <a href="movie_list.php?page=<?= $currentPage + 1 ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>">다음</a>
            </li>
        <?php endif; ?>
    </ul>
    <footer style="height: 100px;">
        <p style="margin-bottom: 40px; margin-top: 20px;">© 2024 My Movie List</p>
    </footer>
</nav>
</body>
</html>