<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>ユーザー登録</title>
    </head>
    <body>
        <?php
            ini_set('display_errors', 'On');
        ?>
        ユーザー登録用ページです。仮登録のため、メールアドレスを入力してください。
        <form action="register_send.php" method="post">
            メールアドレス：<input type="text" name="mail_address"><br>
            <input type="submit" name="submit" value="登録">
        </form>
    </body>
</html>