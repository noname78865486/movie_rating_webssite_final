<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 등록</title>
</head>
<body>
    <h2>영화 등록</h2>
    
    <form action="add_movie_process.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="poster" required>
        <button type="submit">업로드</button>
    </form>

        
    </form>
    
    <form action="add_movie_process.php" method="POST">
        <label>영화 제목:</label>
        <input type="text" name="title" required><br>

        <label>감독:</label>
        <input type="text" name="director" required><br>
        
        <label>개봉연도:</label>
        <input type="text" name="release_year" required><br>

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
            <option value="etc">기타</select><br>
        </select>

        <label>평점:</label>
        <input type="number" name="rating" min="0" max="10" step="0.5" required><br>

        <button type="submit">등록</button>
    </form>
</body>
</html>