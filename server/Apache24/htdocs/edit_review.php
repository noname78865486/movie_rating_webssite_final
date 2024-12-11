<?php
require_once 'config/db.php';

// 리뷰 ID 가져오기
$id = $_GET['id'];

// 리뷰 정보 가져오기
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $movie = $result->fetch_assoc(); // 리뷰 정보 배열로 저장
} else {
    die("리뷰를 찾을 수 없습니다.");
}

// 리뷰 수정 처리
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
        // 리뷰 정보 업데이트
        $updateSql = "UPDATE movies SET title = ?, director = ?, release_date = ?, genre = ?, poster_path = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sssssi', $title, $director, $release_date, $genre, $poster_path, $id);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            echo "<script>alert('리뷰 정보가 수정되었습니다.'); window.location.href = 'movie_detail.php?id=$id';</script>";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>리뷰 등록</title>
    <style>body {height: 100%;}</style>
</head>
<body>
    <nav>
        <a href="dashboard.php">🏠 home</a><br>
        <a href="movie_list.php">🎞️ 영화 목록</a><br>
        <a href="reviews_board.php">⭐ 리뷰 목록</a>
    </nav>
    <h2>리뷰 등록</h2>
    <form action="add_review_process.php" method="POST" enctype="multipart/form-data" style="width: 600px; text-align: left; margin: 0 auto;">
        <!--영화 목록에서 관련 영화 가져와서 태그하는 기능-->
        <label for="movie_title" style="display: block; margin-bottom: 0px;">영화 선택:</label>
        <input type="text" id="movie_title" name="movie" readonly style="width: 83%; box-sizing: border-box; cursor: pointer;" >
        <input type="hidden" id="movie_id" name="movie_id">
        <button type="button" onclick="openMoviePopup()">영화 선택</button>
        <script>
            function openMoviePopup() {
                // 영화 선택을 위한 팝업창 열기
                window.open('movie_list_popup.php', 'moviePopup', 'width=600,height=400');
            }
            function setSelectedMovie(title, id) {
                // 영화 제목을 텍스트로 표시
                document.getElementById('movie_title').value = title;
                // 영화 ID를 hidden input에 저장
                document.getElementById('movie_id').value = id;
            }
        </script>

        <!-- 리뷰 제목 및 내용 작성 부분 -->
        <label for="title" style="display: block; margin-bottom: 0px;">Title:</label>
        <textarea name="title" id="utitle" rows="1" cols="55" 
            placeholder="제목" maxlength="100" required 
            style="width: 100%; resize: none; box-sizing: border-box;"></textarea>

        <label for="content" style="display: block; margin-top: 16px; margin-bottom: 0px;">Content:</label>
        <textarea name="content" id="ucontent" maxlength="100" 
            placeholder="영화에 대한 리뷰를 작성해주세요." required 
            style="width: 100%; height: 100px; resize: none; box-sizing: border-box;"></textarea>

        <!-- 공개/비공개 여부 선택 및 이미지 업로드 -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; margin-bottom: 8px;">
             <!-- 공개/비공개 여부 -->
            <div style="flex: 1; margin-right: 10px;">
                <label for="visibility" style="display: block; margin-bottom: 4px;">후기 공개/비공개 여부:</label>
                <select style="width: 100%; box-sizing: border-box;" name="visibility">
                    <option value="공개">전체공개</option>
                    <option value="비공개">비공개</option>
                </select>
            </div>

            <!-- 파일 업로드 -->
            <div style="flex: 1;">
                <label>파일 업로드:</label>
                <input type="file" name="file"><br>
            </div>
        </div>

        <!-- 평점 추가 -->
        <label>평점:</label>
            <input type="number" name="rating" min="0" max="10" step="0.5" required placeholder="0~10점 사이 0.5점 단위로 입력"><br>
        
        <!-- 제출 버튼 -->
        <button type="submit" style="margin-top: 16px;">저장</button>
    </form>