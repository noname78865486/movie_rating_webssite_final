<?php
session_start(); // 세션 시작

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); // 로그인되지 않았다면 로그인 페이지로 리다이렉트
    exit;
}
$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴
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
        <a href="movie_list.php">⭐ 리뷰 목록</a>
    </nav>
    <h2>리뷰 등록</h2>
    <form action="add_review_process.php" method="POST" enctype="multipart/form-data" style="width: 600px; text-align: left; margin: 0 auto;">
        <!--영화 목록에서 관련 영화 가져와서 태그-->
        <label for="movie_title" style="display: block; margin-bottom: 0px;">영화 선택:</label>
        <input type="text" id="movie_title" name="movie" readonly style="width: 83%; box-sizing: border-box; cursor: pointer;" >
        <input type="hidden" id="movie_id" name="movie_id">
        <button type="button" onclick="openMoviePopup()">영화 선택</button>
        <script>
            function openMoviePopup() {
                // 팝업창 열기
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
                    <option value="public">전체공개</option>
                    <option value="private">비공개</option>
                </select>
            </div>

            <!-- 이미지 업로드 -->
            <div style="flex: 1;">
                <label>파일 업로드:</label>
                <input type="file" name="file"required><br>
            </div><br>
        </div>

            <!-- 평점 추가 -->
            <label>평점:</label>
            <input type="number" name="rating" min="0" max="10" step="0.5" required placeholder="0~10점 사이 0.5점 단위로 입력"><br>

        
        <!-- 제출 버튼 -->
        <button type="submit" style="margin-top: 16px;">저장</button>
        </form>

