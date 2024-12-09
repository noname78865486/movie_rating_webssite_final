<?php
// DB 연결
require_once 'config/db.php';  // DB 연결 설정 파일

// 세션 시작
session_start();

// 로그인 여부 확인
$isLoggedIn = isset($_SESSION['user_id']);

// 검색 카테고리와 키워드 가져오기
$searchCategory = $_GET['search_category'] ?? '';
$searchKeyword = $_GET['search_keyword'] ?? '';

// 페이지 번호 처리
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // 현재 페이지
$moviesPerPage = 10; // 한 페이지에 표시할 영화 수
$offset = ($currentPage - 1) * $moviesPerPage; // 시작 위치 계산. offset은 현재 페이지에서 데이터를 시작하는 인덱스.

// SQL 쿼리 시작
$sql = "SELECT id, title, director, release_date, genre, IFNULL(rating, 0) AS rating FROM movies";
$totalMoviesQuery = "SELECT COUNT(*) AS total FROM movies";

// 매개변수 배열 초기화
$params = [];
$types = '';

// 검색어가 있을 경우 WHERE 조건 추가
if ($searchKeyword) {
    // 검색어에 와일드카드 추가
    $searchKeyword = '%' . $searchKeyword . '%';  // LIKE 검색을 위해 앞뒤에 %를 추가합니다.

    if ($searchCategory == 'title') {
        $sql .= " WHERE title LIKE ?";
        $totalMoviesQuery .= " WHERE title LIKE ?";
        $params = [$searchKeyword]; 
        $types = 's';
    } elseif ($searchCategory == 'director') {
        $sql .= " WHERE director LIKE ?";
        $totalMoviesQuery .= " WHERE director LIKE ?";
        $params = [$searchKeyword];
        $types = 's';
    } elseif ($searchCategory == 'genre') {
        $sql .= " WHERE genre LIKE ?";
        $totalMoviesQuery .= " WHERE genre LIKE ?";
        $params = [$searchKeyword];
        $types = 's';
    } elseif ($searchCategory == 'title_director') {
        $sql .= " WHERE title LIKE ? OR director LIKE ?";
        $totalMoviesQuery .= " WHERE title LIKE ? OR director LIKE ?";
        $params = [$searchKeyword, $searchKeyword];
        $types = 'ss';
    } elseif ($searchCategory == 'title_genre') {
        $sql .= " WHERE title LIKE ? OR genre LIKE ?";
        $totalMoviesQuery .= " WHERE title LIKE ? OR genre LIKE ?";
        $params = [$searchKeyword, $searchKeyword];
        $types = 'ss';
    } elseif ($searchCategory == 'genre_director') {
        $sql .= " WHERE genre LIKE ? OR director LIKE ?";
        $totalMoviesQuery .= " WHERE genre LIKE ? OR director LIKE ?";
        $params = [$searchKeyword, $searchKeyword];
        $types = 'ss';
    } else {
        // 전체 필드에서 검색될 경우
        $sql .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?";
        $totalMoviesQuery .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?";
        $params = [$searchKeyword, $searchKeyword, $searchKeyword];
        $types = 'sss';
    }
}

// 정렬 조건 및 페이징 조건 추가
$sql .= " ORDER BY id ASC LIMIT ? OFFSET ?";
$params[] = $moviesPerPage;
$params[] = $offset;
$types .= 'ii';

// 쿼리 준비
$stmt = $conn->prepare($sql);

// 검색어와 페이징 파라미터 바인딩
if ($searchKeyword) {
    // 검색 카테고리가 제목
    if ($searchCategory === 'title') {
        $stmt->bind_param('ssi', $searchKeyword, $moviesPerPage, $offset);
    }
    // 검색 카테고리가 감독
    elseif ($searchCategory === 'director') {
        $stmt->bind_param('ssi', $searchKeyword, $moviesPerPage, $offset);
    }
    // 검색 카테고리가 장르
    elseif ($searchCategory === 'genre') {
        $stmt->bind_param('ssi', $searchKeyword, $moviesPerPage, $offset);
    }
    // 제목 + 감독
    elseif ($searchCategory === 'title_director') {
        $stmt->bind_param('sssi', $searchKeyword, $searchKeyword, $moviesPerPage, $offset);
    }
    // 제목 + 장르
    elseif ($searchCategory === 'title_genre') {
        $stmt->bind_param('sssi', $searchKeyword, $searchKeyword, $moviesPerPage, $offset);
    }
    // 장르 + 감독
    elseif ($searchCategory === 'genre_director') {
        $stmt->bind_param('sssi', $searchKeyword, $searchKeyword, $moviesPerPage, $offset);
    }
} else {
    // 검색어가 없을 경우 페이징 매개변수만 바인딩
    $stmt->bind_param('ii', $moviesPerPage, $offset); 
}

// 쿼리 실행
$stmt->execute();
$result = $stmt->get_result();

// 전체 영화 개수 가져오기 (페이지네이션 계산용)
$totalMoviesQuery = "SELECT COUNT(*) AS total FROM movies";

// 검색어가 있을 경우 제목(title), 감독(director), 장르(genre) 컬럼에서 해당 검색어를 찾는 조건을 추가
if ($searchKeyword) {
    $searchKeyword = '%' . $searchKeyword . '%'; // 와일드카드 추가
    $sql .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?";
    $totalMoviesQuery .= " WHERE title LIKE ? OR director LIKE ? OR genre LIKE ?";
    // 검색 조건을 매개변수로 준비. 여기서는 검색어가 세 군데에서 사용되므로, 세 번 동일한 검색어를 사용
    $countParams = [$searchKeyword, $searchKeyword, $searchKeyword];
    // bind_param에서 사용할 타입을 설정. 'sss'는 세 개의 문자열 매개변수 의미
    $countTypes = 'sss'; // 세 개의 문자열 매개변수
} else {
    $countParams = [];  // 검색어가 없으면 매개변수는 비워두기
    $countTypes = '';    // 타입도 비워두기
}

// 준비된 쿼리 실행을 위한 준비
// 쿼리 문자열을 기반으로 준비된 문장(prepared statement)을 생성합니다.
$countStmt = $conn->prepare($totalMoviesQuery);

// 검색 조건이 있을 경우에만 매개변수를 바인딩합니다.
if ($countParams) {
    // bind_param은 쿼리의 물음표(?) 부분에 실제 값을 바인딩하는 함수입니다.
    // 첫 번째 인자는 데이터 타입, 두 번째 인자부터는 실제 값입니다.
    $countStmt->bind_param($countTypes, ...$countParams);
}

// 쿼리 실행
$countStmt->execute();

// 쿼리 실행 결과를 가져오는 코드. 전체 영화 개수는 'total'이라는 필드명으로 반환되므로, 그 값을 얻어옴.
$countResult = $countStmt->get_result();
// 총 영화 수를 가져옵니다. 쿼리의 결과에서 'total' 컬럼 값을 추출하여 전체 영화 수를 계산합니다.
$totalMovies = $countResult->fetch_assoc()['total'];

// 전체 페이지 수 계산
// 전체 영화 수(totalMovies)를 한 페이지에 표시할 영화 수($moviesPerPage)로 나누어 페이지 수를 계산합니다.
// ceil() 함수를 사용하여 나누어떨어지지 않으면 올림 처리하여 전체 페이지 수를 구합니다.
$totalPages = ceil($totalMovies / $moviesPerPage);
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
        <form action="movie_list.php" method="get">
            <div>
                <label for="search_category">검색 카테고리:</label>
                <select name="search_category" id="search_category">
                    <option value="">전체</option>
                    <option value="title" <?php echo $searchCategory === 'title' ? 'selected' : ''; ?>>제목</option>
                    <option value="director" <?php echo $searchCategory === 'director' ? 'selected' : ''; ?>>감독</option>
                    <option value="genre" <?php echo $searchCategory === 'genre' ? 'selected' : ''; ?>>장르</option>
                    <option value="title_director" <?php echo $searchCategory === 'title_director' ? 'selected' : ''; ?>>제목 + 감독</option>
                    <option value="title_genre" <?php echo $searchCategory === 'title_genre' ? 'selected' : ''; ?>>제목 + 장르</option>
                    <option value="genre_director" <?php echo $searchCategory === 'genre_director' ? 'selected' : ''; ?>>장르 + 감독</option>
                </select>
            </div>
            <div>
                <label for="search_keyword">검색어:</label>
                <input type="text" id="search_keyword" name="search_keyword" value="<?= isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '' ?>">
            </div>
            <button type="submit">검색</button>
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
                <?php else: ?> <!-- 결과가 없을 때 -->
                    <tr>
                      <td colspan="7">검색 결과가 없습니다.</td>
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