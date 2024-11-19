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

        <label>감독:</label>
        <input type="text" name="title" required><br>
        
        <label>개봉연도:</label>
        <input type="text" name="title" required><br>
       
        <label>포스터:</label>
        <input type="file" class="real-upload" accept="image/*" required multiple>
        <!--<script>
            function getImageFiles(e) {
            const uploadFiles = [];
            const files = e.currentTarget.files;
            const imagePreview = document.querySelector('.image-preview');
            const docFrag = new DocumentFragment();

            if ([...files].length >= 7) {
                alert('포스터는 1개만 업로드가 가능합니다.');
                return;
            }

            // 파일 타입 검사
            [...files].forEach(file => {
                if (!file.type.match("image/.*")) {
                alert('이미지 파일만 업로드가 가능합니다.');
                return
                }

                // 파일 갯수 검사
                if ([...files].length < 2) {
                uploadFiles.push(file);
                const reader = new FileReader();
                reader.onload = (e) => {
                    const preview = createElement(e, file);
                    imagePreview.appendChild(preview);
                };
                reader.readAsDataURL(file);
                }
            });
            }-->

        <label>줄거리:</label>
        <input type="text" name="title" required><br>

        <label>평점:</label>
        <input type="number" name="rating" min="0" max="10" step="0.5" required><br>

        <button type="submit">등록</button>
    </form>
</body>
</html>