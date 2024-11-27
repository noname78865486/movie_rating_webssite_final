<?php
require_once 'config/db.php'; // DB 연결

// 검색어 처리
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';

// 검색 조건에 따라 영화 목록 가져오기
$sql = "SELECT id, title FROM movies";
if (!empty($searchKeyword)) {
    $sql .= " WHERE title LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeKeyword = '%' . $searchKeyword . '%';
    $stmt->bind_param("s", $likeKeyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql . " ORDER BY title ASC");
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>영화 선택</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f4f4f4; }
        input[type="text"] { width: 70%; padding: 5px; }
        button { padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>영화 선택</h2>

    <!-- 검색 폼 -->
    <form method="GET" action="movie_list_popup.php">
        <input type="text" name="search" placeholder="영화 제목 검색" value="<?= htmlspecialchars($searchKeyword) ?>">
        <button type="submit">검색</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>영화 제목</th>
                <th>선택</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <!-- 선택 버튼에 실제 제목과 movie_id 값을 전달 -->
                        <button onclick="selectMovie('<?= htmlspecialchars($row['title']) ?>', <?= $row['id'] ?>)">선택</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="2">검색 결과가 없습니다.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <script>
        function selectMovie(title, id) {
            // 부모 창의 함수 호출
            const parentWindow = window.opener;
            if (parentWindow && parentWindow.setSelectedMovie) {
                parentWindow.setSelectedMovie(title, id);
            }
            window.close(); // 팝업창 닫기
        }
    </script>

</body>
</html>
