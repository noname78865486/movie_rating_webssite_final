<?php
// <기능 설명>
// - 영화 목록 보여주는 기능
// - 누구든 영화 목록 및 점수 조회 가능
// - 영화 추가는 로그인한 사람만 가능
// - 댓글 형식으로 점수 추가 및 코멘트 가능

//DB 연결
require_once 'config/db.php';

// 영화 목록과 평균 평점을 가져오는 SQL 쿼리
$sql = "
    SELECT m.id, m.title, m.director, m.release_date, m.genre,
           COALESCE(AVG(r.rating), 0) AS avg_rating
    FROM movies m
    LEFT JOIN reviews r ON m.id = r.movie_id
    GROUP BY m.id
    ORDER BY m.id DESC;
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>영화 목록</title>
    <style>
        table, tr {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            width: 100%;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        table th {
            width: 100%;
            background-color: #f4f4f4;
        }
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
            <a href="index.HTML">🏠Home</a>
            <a href="#" onclick="alert('로그인한 회원만 가능한 기능입니다.'); return false;">➕영화 추가</a>
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
            <?php if ($result->num_rows > 0) : ?>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['director']) ?></td>
                        <td><?= htmlspecialchars($row['release_date']) ?></td>
                        <td><?= htmlspecialchars($row['genre']) ?></td>
                        <td><?= number_format($row['avg_rating'], 1) ?>/10</td>
                        <td><a href="movie_detail.php?id=<?= htmlspecialchars($row['id']) ?>">보기</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-data">등록된 영화가 없습니다.</td>
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