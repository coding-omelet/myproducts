<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
    header('X-FRAME-OPTIONS: SAMEORIGIN');

    // CSRF
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>ユーザー登録</title>
    </head>
    <body>
        ユーザー登録用ページです。仮登録のため、メールアドレスを入力してください。
        <form action="register_send.php" method="post">
            <input type="hidden" name="token" value=<?=$token?>>
            メールアドレス：<input type="text" name="mail_address"><br>
            <input type="submit" name="submit" value="登録">
        </form>
        <hr>
        <a href="/login/login_form.php">既にユーザー登録済みの方はこちらからログインしてください。</a>
    </body>
</html>