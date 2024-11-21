<?php
// <기능 설명>
// - 영화 목록 보여주는 기능
// - 누구든 영화 목록 및 점수 조회 가능
// - 영화 추가는 로그인한 사람만 가능
// - 댓글 형식으로 점수 추가 및 코멘트 가능

//DB 연결
require_once 'config/db.php';
// 영화 데이터를 데이터베이스에서 가져오는 SQL 쿼리
$sql = "SELECT id, title, director, release_date, genre, rating FROM movies ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>영화 목록</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- 스타일시트 경로 -->
</head>
<body>
    <header>
        <h1>영화 목록</h1>
        <nav>
            <a href="dashboard.php">🏠Home</a>
            <a href="add_movie.php">➕영화 추가</a>
        </nav>
    </header>

    <main>
        <table border="1">
            <thead>
                <tr>
                    <th>번호</th>
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
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['director']) ?></td>
                            <td><?= htmlspecialchars($row['release_date']) ?></td>
                            <td><?= htmlspecialchars($row['genre']) ?></td>
                            <td><?= htmlspecialchars($row['rating']) ?></td>
                            <td><a href="movie_detail.php?id=<?= $row['id'] ?>">보기</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">등록된 영화가 없습니다.</td>
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