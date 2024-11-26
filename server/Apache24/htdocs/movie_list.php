<!-- 기능설명
- 영화 목록 보여주는 기능
- 누구든 영화 목록 및 점수 조회 가능
- 영화 추가는 로그인한 사람만 가능
- 댓글 형식으로 점수 추가 및 코멘트 가능 -->

<?php
//DB 연결
require_once 'config/db.php';

// 영화 목록과 평균 평점을 가져오는 SQL 쿼리
$sql = "
    SELECT m.id, 
           COALESCE(m.title, '') AS title, 
           COALESCE(m.director, '') AS director, 
           COALESCE(m.release_date, '') AS release_date, 
           COALESCE(m.genre, '') AS genre,
           COALESCE(AVG(r.rating), 0) AS avg_rating
    FROM movies m
    LEFT JOIN reviews r ON m.id = r.movie_id
    GROUP BY m.id
    ORDER BY m.id ASC;
";
$result = $conn->query($sql);
?>

<?php
// 세션 시작
session_start();

// 로그인 여부를 판단 (로그인한 경우 $_SESSION['user_id']가 존재한다고 가정)
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>영화 목록</title>
    <script>
        // 영화 추가 클릭 시 로그인 여부를 판단하여 로그인하지 않은 유저 차단
        function handleAddMovie(isLoggedIn) {
            if (isLoggedIn) {
                // 로그인된 사용자일 경우 add_movie.php로 이동
                window.location.href = 'add_movie.php';
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
                // 로그인되지 않은 경우 index.php로 이동
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
        table th:nth-child(2), table td:nth-child(2) { width: 45%; } /* 제목 */
        table th:nth-child(3), table td:nth-child(3) { width: 12%; } /* 감독 */
        table th:nth-child(4), table td:nth-child(4) { width: 13%; } /* 개봉일 */
        table th:nth-child(5), table td:nth-child(5) { width: 10%; } /* 장르 */
        table th:nth-child(6), table td:nth-child(6) { width: 10%; } /* 평점 */
        table th:nth-child(7), table td:nth-child(7) { width: 10%; } /* 상세보기 */
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
        <h1>영화 목록</h1>
        <nav>
            <!-- 로그인 여부를 JavaScript로 전달 -->
            <a href="#" onclick="handleHomeClick(<?= $isLoggedIn ? 'true' : 'false' ?>)">🏠Home</a>
            <a href="#" onclick="handleAddMovie(<?= $isLoggedIn ? 'true' : 'false' ?>)">➕영화 추가</a>
        </nav>
    </header>

    <main>
        <table border="1">
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
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['director']) ?></td>
                        <td><?= htmlspecialchars($row['release_date']) ?></td>
                        <td><?= htmlspecialchars($row['genre']) ?></td>
                        <td><?= number_format($row['avg_rating'], 1) ?>/10</td>
                        <td><a style="color: blue;" href="movie_detail.php?id=<?= htmlspecialchars($row['id']) ?>">보기</a></td>
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

    <footer>
        <p>© 2024 My Movie List</p>
    </footer>
</body>
</html>