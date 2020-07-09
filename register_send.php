<?php
    $errors = array();

    // 登録ページから遷移していない場合
    if (!isset($_POST["mail_address"])) {
        // 登録ページに飛ばす
        header("Location: register_page.php");
        exit;
    
    // アドレス欄が空の場合
    } elseif ($_POST["mail_address"] == "") {
        $errors["mail_empty"] = "メールアドレスが入力されていません。";
    
    // アドレスが入力された場合
    } else {
        $mail_address = $_POST["mail_address"];

        // メールアドレスの形式が正しくない場合
        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail_address)) {
            $errors["mail_check"] = "メールアドレスが無効です。";
        }
    }

    // エラーがなければ
    if (count($errors) == 0) {
        // クリック用アドレスを生成
        $urltoken = hash('sha256', uniqid(rand(), 1));
        $url = 'https://tb-220042.tech-base.net/hoge.php'.'?urltoken='.$urltoken;

        // データベースに接続
        require_once 'db.php';

        // 仮登録テーブルに登録
        $sql = 'INSERT INTO pre_member (urltoken, mail, date) VALUES (:urltoken, :mail, now())';
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
        $stmt -> bindParam(':mail', $mail_address, PDO::PARAM_STR);
        $stmt -> execute();

        // メール送信
        require 'phpmailer/send_register_mail.php';
    }
?>

<!-- ここから表示部分 -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo count($errors) ? "エラー" : "メール送信完了"; ?></title>
    </head>
    <body>
        <!-- エラーがある場合 -->
        <?php
            if (count($errors)) {
                // エラーを全て表示して終了
                foreach ($errors as $error) {
                    echo $error."<br>";
                }
                exit;
            }
        ?>
        <!-- エラーがない場合 -->
        入力いただいたメールアドレス宛にユーザー登録用のメールを送信しました。<br>
        メールに記載のリンクからユーザー登録を完了させてください。<br>
        <br>
        メールが届かない場合は、お手数ですが初めから登録をやり直してください。
    </body>
</html>