<?php
session_start(); // 세션 시작

// 로그인된 상태인지 확인
if (!isset($_SESSION['userID'])) {
    // 경고 메시지 및 로그인 페이지로 리다이렉트
    echo "<script>alert('로그인한 회원만 가능한 기능입니다.'); window.location.href = 'login.php';</script>";
    exit;
}
$userID = $_SESSION['userID']; // 로그인한 유저의 ID를 세션에서 가져옴
?>

<!DOCTYPE html>
<html>
<head>
    <!-- CSS 연결 -->
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 등록</title>
    <!-- 기본 스타일 적용 -->
    <style> 
        body { 
            height: 100%; 
            font-family: Arial, sans-serif; 
        } 
        form { 
            margin: 20px; 
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- 네비게이션 메뉴 -->
    <nav>
        <a href="dashboard.php">🏠 Home</a><br>
        <a href="movie_list.php">🎞️ 영화 목록</a>
    </nav>
    
    <!-- 영화 등록 헤더 -->
    <h2>영화 등록</h2>
    
    <!-- 영화 등록 폼 -->
    <form action="add_movie_process.php" method="POST" enctype="multipart/form-data">
        <!-- 영화 포스터 입력 -->
        <label>영화 포스터:</label>
        <input type="file" name="poster" accept="image/*" required>
        
        <!-- 영화 제목 입력 -->
        <label>영화 제목:</label>
        <input type="text" name="title" required>
        
        <!-- 감독 입력 -->
        <label>감독:</label>
        <input type="text" name="director" required>
        
        <!-- 개봉 날짜 입력 -->
        <label>개봉 날짜:</label>
        <input type="text" name="release_date" required placeholder="ex. 1900-00-00">
        
        <!-- 장르 선택 -->
        <label>장르:</label>
        <select name="genre">
            <option value="none">선택</option>
            <option value="액션">액션</option>
            <option value="코미디">코미디</option>
            <option value="로맨스">로맨스</option>
            <option value="스릴러">스릴러</option>
            <option value="애니메이션">애니메이션</option>
            <option value="드라마">드라마</option>
            <option value="SF">SF</option>
            <option value="판타지">판타지</option>
            <option value="공포">공포</option>
            <option value="다큐">다큐</option>
            <option value="역사">역사</option>
            <option value="기타">기타</option>
        </select>
        
        <!-- 폼 제출 버튼 -->
        <button type="submit">등록</button>
    </form>
</body>
</html>