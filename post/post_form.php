<?php
    session_start();
    header("Content-type: text/html; charset=utf-8");
    header('X_FRAME_OPTIONS: SAMEORIGIN');

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
                    <td><input type="url" name="submission_url" maxlength="128" required></td>
                </tr>
                <tr>
                    <td>コメント（任意、80文字以内）：</td>
                    <td><input type="text" name="comment" maxlength="80"></td>
                </tr>
                <tr>
                    <td><input type="button" value="戻る" onClick="history.back()"></td>
                    <td><input type="submit" value="投稿"></td>
            </table>
        </form>
    </body>
</html>