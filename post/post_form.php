<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
    header('X-FRAME-OPTIONS: SAMEORIGIN');

    // CSRF
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];

    // 未ログインなら登録ページに飛ばす
    if (!isset($_SESSION['name'])) {
        header("Location: /register/register_page.php");
        exit;
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>投稿ページ</title>
    </head>
    <body>
        <h1>投稿ページ</h1>
        <form action="post_check.php" method="post">
            <input type="hidden" name="token" value=<?=$token?>>
            <table>
                <tr>
                    <td>提出URL：</td>
                    <td><input type="url" name="url" maxlength="128" required></td>
                </tr>
                <tr>
                    <td>コメント（任意、40文字以内）：</td>
                    <td><input type="text" name="comment" maxlength="40"></td>
                </tr>
                <tr>
                    <td><input type="button" value="戻る" onClick="history.back()"></td>
                    <td><input type="submit" value="投稿"></td>
                </tr>
            </table>
        </form>
    </body>
</html>