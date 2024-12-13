<?php
require_once 'config/db.php';
session_start();

$id = $_GET['id'];

// 디버깅: 세션 값 확인
if (!isset($_SESSION['role'])) {
    die("세션이 설정되지 않았습니다. 관리자 권한이 필요합니다.");
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// 영화 정보 가져오기
$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $movie = $result->fetch_assoc();
} else {
    die("영화를 찾을 수 없습니다.");
}

// 영화 삭제 처리
if ($isAdmin && isset($_POST['delete'])) {
    $deleteSql = "DELETE FROM movies WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param('i', $id);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows > 0) {
        echo "<script>alert('영화가 삭제되었습니다.'); window.location.href = 'movie_list.php';</script>";
    } else {
        echo "<script>alert('삭제 실패.');</script>";
    }
}

// 연결 종료
$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>영화 상세 정보</title>
    <style>
        a {
            margin: 20px auto;
            padding: 20px;
            width: 300px;
        }
        body {
            line-height: 1.6;
            margin: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        img {
            max-width: 300px;
            height: auto;
        }
        .container {
            height: 100%;
            display: flex;
            flex-direction: column; /* 세로 정렬 */
            align-items: center;    /* 자식 요소들을 가운데 정렬 */
            padding: 20px;
            width: 400px; /* 컨테이너 폭 */
        }
    </style>
</head>

<body style="height:100%;">
    <!--컨텐츠 전체를 감싸는 박스-->
    <div class="container" style="margin:60px auto;">
        <!--영화 포스터-->
        <?php if ($movie['poster_path']): ?>
            <img src="<?= htmlspecialchars($movie['poster_path']) ?>" alt="포스터">
        <?php else: ?>
            <p>포스터 이미지가 없습니다.</p>
        <?php endif; ?>
        
        <!--영화 제목-->
        <h1 style="margin:10px;"><?= htmlspecialchars($movie['title']) ?></h1>

        <!--감독, 개봉연도, 장르-->
        <div class="line" style="width:65%;">
            <p>감독: <?= htmlspecialchars($movie['director']) ?></p>
        </div>
        <div class="line" style="width:65%;">
            <p>개봉연도: <?= htmlspecialchars($movie['release_date']) ?></p>
        </div>       
        <div class="line" style="width:65%;">
            <p>장르: <?= htmlspecialchars($movie['genre']) ?></p>
        </div> 

        <!--평균평점(등록된 후기 또는 평점 없을 경우 오류 방지를 위해 문구 출력)-->
        <div class="line" style="width:65%;">
            <p>
                평균평점: 
                <?php if ($movie['rating'] == 0): ?>
                    아직 등록된 후기 및 평점이 존재하지 않습니다.
                <?php else: ?>
                    <?= htmlspecialchars($movie['rating']) ?>/10
                <?php endif; ?>
            </p>
        </div> 

        <!-- role이 admin인 경우 영화 수정, 삭제 가능 -->
        <?php if ($isAdmin): ?>
            <!-- 영화 수정 및 삭제 버튼을 포함하는 컨테이너 -->
            <div style="display: flex; gap: 10px; align-items: center; margin-top: 16px;">
                <!-- 영화 수정 버튼 -->
                <a href="edit_movie.php?id=<?= $movie['id'] ?>">
                    <button type="button">영화 수정</button>
                </a>

                <!-- 영화 삭제 버튼 -->
                <form method="POST" style="background-color: transparent;" action="" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                    <button type="submit" name="delete" style="margin:0 0;">영화 삭제</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- 뒤로가기 버튼 -->
        <button type="button" style="margin-top: 16px; margin-bottom: 60px;" onclick="history.back();">뒤로가기</button>

    </div>
</body>
</html>