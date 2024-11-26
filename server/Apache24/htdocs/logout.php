<?php
session_start();
session_destroy();
?>
<script>
    alert("성공적으로 로그아웃되었습니다");
    location.replace('index.php');
</script>