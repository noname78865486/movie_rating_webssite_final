<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 등록</title>
    <style> body{height=100%;} </style>
</head>
<body>
    <h2>영화 등록</h2>
    <form action="add_movie_process.php" method="POST" enctype="multipart/form-data">
        <!--영화 포스터 등록 부분-->
        <label>영화 포스터:</label>
        <input type="file" name="poster" accept="image/*"required><br>
        
        <!--영화 정보 입력 부분-->    
        <label>영화 제목:</label>
        <input type="text" name="title" required><br>

        <label>감독:</label>
        <input type="text" name="director" required><br>
        
        <label>개봉날짜:</label>
        <input type="text" name="release_date" required placeholder="ex. 1900-00-00"><br>

        <label>장르:</label> 
        <select name="genre">
            <option value="none"> 선택 </option>
            <option value="action">액션</option>
            <option value="comedy">코미디</option>
            <option value="romance">로맨스</option>
            <option value="thriler">스릴러</option>
            <option value="animation">애니메이션</option>
            <option value="drama">드라마</option>
            <option value="SF">SF</option>
            <option value="fantasy">판타지</option>
            <option value="horror">공포</option>
            <option value="documentary">다큐</option>
            <option value="etc">기타</option>
        </select><br>

        <label>평점:</label>
        <input type="number" name="rating" min="0" max="10" step="0.5" required placeholder="0~10점 사이 0.5점 단위로 입력"><br>

        <button type="submit">등록</button>
    </form>
</body>
</html>