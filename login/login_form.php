<?php
    session_start();
    header("Content-type: text/html; charset=utf-8");

    // CSRF
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];

    // CJ
    header('X_FRAME_OPTIONS: SAMEORIGIN');

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ログイン画面</title>
    </head>
    <body>
        <form action="login_check.php" method="post">
            <input type="hidden" name="token" value=<?=$token?>>
            <table>
                <tr>
                    <td>メールアドレス：</td>
                    <td><input type="text" name="mail_address" maxlength="50"></td>
                </tr>
                <tr>
                    <td>パスワード：</td>
                    <td><input type="text" name="password" maxlength="30"></td>
                </tr>
                <tr>
                    <td><input type="button" value="戻る" onClick="history.back()"></td>
                    <td><input type="submit" value="ログイン"></td>
            </table>
        </form>
    </body>
</html>