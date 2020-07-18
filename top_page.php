<?php
    session_start();
    header("Content-type: text/html; charset=utf-8");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ACbb</title>
    </head>
    <body>
        <h1>トップページ<br></h1>
        
        <!-- ログイン中の時 -->
        <?php if (isset($_SESSION['name'])): ?>
            <?php
                $name = $_SESSION['name'];
            ?>
            ログイン中：<?=$name?><br>
            <a href="login/logout.php">ログアウト<br></a>
            
        <!-- ログアウト中の時 -->
        <?php else: ?>
            <a href="register/register_page.php">ユーザー登録<br></a>
            <a href="login/login_form.php">ログイン<br></a>
        
        <?php endif; ?>
    </body>
</html>