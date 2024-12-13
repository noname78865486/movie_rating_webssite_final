<?php
require_once 'config/db.php';

// 영화 ID 가져오기
$id = $_GET['id'];

// 영화 정보 가져오기
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $movie = $result->fetch_assoc(); // 영화 정보 배열로 저장
} else {
    die("영화를 찾을 수 없습니다.");
}

// 영화 수정 처리
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $director = $_POST['director'];
    $release_date = $_POST['release_date'];
    $genre = $_POST['genre'];
    $poster_path = $movie['poster_path']; // 기존 경로를 기본값으로 설정

    // 포스터 업로드 처리
    $upload_dir = 'C:/movie_rating_website/server/Apache24/htdocs/img/';
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $poster_tmp_name = $_FILES['poster']['tmp_name'];
        $poster_name = basename($_FILES['poster']['name']);
        $new_poster_path = $upload_dir . $poster_name;

        if (move_uploaded_file($poster_tmp_name, $new_poster_path)) {
            $poster_path = '/img/' . $poster_name; // 새 경로 저장
        } else {
            echo "<script>alert('포스터 업로드 실패.');</script>";
        }
    }

    // 데이터 유효성 검사
    if (empty($title) || empty($director) || empty($release_date) || empty($genre)) {
        echo "<script>alert('필수 항목을 모두 입력해 주세요.');</script>";
    } else {
        // 영화 정보 업데이트
        $updateSql = "UPDATE movies SET title = ?, director = ?, release_date = ?, genre = ?, poster_path = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sssssi', $title, $director, $release_date, $genre, $poster_path, $id);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            echo "<script>alert('영화 정보가 수정되었습니다.'); window.location.href = 'movie_detail.php?id=$id';</script>";
        } else {
            echo "<script>alert('수정 실패.');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 수정</title>
    <style> body{height:100%;} </style>
</head>
<body>
    <nav>
        <a href="dashboard.php">🏠 home</a><br>
        <a href="movie_list.php">🎞️ 영화 목록</a>
    </nav>
    <h2>영화 수정</h2>
    <form action="edit_movie.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">
        <!--영화 포스터 업로드-->
        <label>영화 포스터 재업로드:</label>
        <input type="file" name="poster" accept="image/*"><br>

        <!--영화 제목 입력-->
        <label>영화 제목:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($movie['title']) ?>" required><br>

        <!--감독 입력-->
        <label>감독:</label>
        <input type="text" name="director" value="<?= htmlspecialchars($movie['director']) ?>" required><br>
        
        <!--개봉 날짜 입력-->
        <label>개봉날짜:</label>
        <input type="text" name="release_date" value="<?= htmlspecialchars($movie['release_date']) ?>" required placeholder="ex. 1900-00-00"><br>

        <!--장르 선택-->
        <label>장르:</label> 
        <select name="genre">
            <option value="none">선택</option>
            <option value="액션" <?= $movie['genre'] == '액션' ? 'selected' : '' ?>>액션</option>
            <option value="코미디" <?= $movie['genre'] == '코미디' ? 'selected' : '' ?>>코미디</option>
            <option value="로맨스" <?= $movie['genre'] == '로맨스' ? 'selected' : '' ?>>로맨스</option>
            <option value="스릴러" <?= $movie['genre'] == '스릴러' ? 'selected' : '' ?>>스릴러</option>
            <option value="애니메이션" <?= $movie['genre'] == '애니메이션' ? 'selected' : '' ?>>애니메이션</option>
            <option value="드라마" <?= $movie['genre'] == '드라마' ? 'selected' : '' ?>>드라마</option>
            <option value="SF" <?= $movie['genre'] == 'SF' ? 'selected' : '' ?>>SF</option>
            <option value="판타지" <?= $movie['genre'] == '판타지' ? 'selected' : '' ?>>판타지</option>
            <option value="공포" <?= $movie['genre'] == '공포' ? 'selected' : '' ?>>공포</option>
            <option value="다큐" <?= $movie['genre'] == '다큐' ? 'selected' : '' ?>>다큐</option>
            <option value="역사" <?= $movie['genre'] == '역사' ? 'selected' : '' ?>>역사</option>
            <option value="기타" <?= $movie['genre'] == '기타' ? 'selected' : '' ?>>기타</option>
        </select><br>

        <button type="submit" name="submit">수정</button>
    </form>
</body>
</html>
