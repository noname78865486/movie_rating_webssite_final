<?php
require_once 'config/db.php'; // 데이터베이스 연결 파일 포함

// 검색어 처리
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : ''; // GET 요청에서 검색어를 가져와 공백 제거

// 검색 조건에 따라 영화 목록 가져오기
$sql = "SELECT id, title FROM movies"; // 기본 SQL 쿼리
if (!empty($searchKeyword)) { // 검색어가 입력된 경우
    $sql .= " WHERE title LIKE ?"; // 제목에 검색어가 포함된 영화를 검색하는 조건 추가
    $stmt = $conn->prepare($sql); // SQL 문 준비
    $likeKeyword = '%' . $searchKeyword . '%'; // 부분 검색을 위한 키워드
    $stmt->bind_param("s", $likeKeyword); // 키워드를 바인딩
    $stmt->execute(); // SQL 문 실행
    $result = $stmt->get_result(); // 결과 가져오기
} else { // 검색어가 없는 경우
    $result = $conn->query($sql . " ORDER BY title ASC"); // 영화 제목을 기준으로 오름차순 정렬하여 전체 목록 가져오기
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8"> <!-- 한글 인코딩 설정 -->
    <title>영화 선택</title> <!-- 페이지 제목 -->
    <style>
        /* 기본 스타일 정의 */
        body { font-family: Arial, sans-serif; margin: 20px; } /* 폰트 및 기본 여백 설정 */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; } /* 테이블 너비 및 스타일 설정 */
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; } /* 테이블 셀 스타일 */
        th { background-color: #f4f4f4; } /* 테이블 헤더 스타일 */
        input[type="text"] { width: 70%; padding: 5px; } /* 텍스트 입력 필드 스타일 */
        button { padding: 5px 10px; cursor: pointer; } /* 버튼 스타일 */
    </style>
</head>
<body>
    <h2>영화 선택</h2> <!-- 페이지 제목 -->

    <!-- 검색 폼 -->
    <form method="GET" action="movie_list_popup.php"> <!-- 검색어를 전달하는 GET 요청 -->
        <input type="text" name="search" placeholder="영화 제목 검색" value="<?= htmlspecialchars($searchKeyword) ?>"> <!-- 검색어 입력 -->
        <button type="submit">검색</button> <!-- 검색 버튼 -->
    </form>

    <table>
        <thead>
            <tr>
                <th>영화 제목</th> <!-- 영화 제목 열 -->
                <th>선택</th> <!-- 선택 버튼 열 -->
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?> <!-- 검색 결과가 있는 경우 -->
            <?php while ($row = $result->fetch_assoc()): ?> <!-- 결과를 반복하여 테이블에 표시 -->
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td> <!-- 영화 제목 출력 -->
                    <td>
                        <!-- 선택 버튼에 실제 제목과 movie_id 값을 전달 -->
                        <button onclick="selectMovie('<?= htmlspecialchars($row['title']) ?>', <?= $row['id'] ?>)">선택</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?> <!-- 검색 결과가 없는 경우 -->
            <tr>
                <td colspan="2">검색 결과가 없습니다.</td> <!-- 검색 결과 없음 메시지 -->
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <script>
        function selectMovie(title, id) {
            // 부모 창의 함수 호출
            const parentWindow = window.opener; // 부모 창 참조
            if (parentWindow && parentWindow.setSelectedMovie) { // 부모 창에 setSelectedMovie 함수가 있는 경우
                parentWindow.setSelectedMovie(title, id); // 부모 창 함수 호출하여 영화 제목과 ID 전달
            }
            window.close(); // 팝업 창 닫기
        }
    </script>

</body>
</html>
