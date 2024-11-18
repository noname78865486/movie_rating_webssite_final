<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 등록</title>
</head>
<body>
    <h2>영화 등록</h2>
    <form action="add_movie_process.php" method="POST">
        <label>영화 제목:</label>
        <input type="text" name="title" required><br>

        <label>평점:</label>
        <input type="number" name="rating" min="0" max="10" step="0.5" required><br>

        <button type="submit">등록</button>
    </form>
</body>
</html>