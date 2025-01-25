<?php
session_start();
$_SESSION = array();
session_destroy();
?>


<!DOCTYPE html>
<html>

<head>
    <title>ログアウト</title>
</head>

<body>
    <?php if (empty($_SESSION['login_user_id'])): ?>
        ログインしていません。
    <?php else: ?>
        <h1>
            ログアウトしました
        </h1>
    <?php endif; ?>
    <p><a href='login.php'>ログインページに戻る</a></p>
</body>

</html>