<!-- メール送信完了ページ -->

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

    // 正しい形式のメールアドレスが入力されていれば
    if (count($errors) == 0) {
        // データベースに接続
        require_once '/public_html/db.php';

        // 他と被らないようにトークンを生成
        while (TRUE) {
            $urltoken = hash('sha256', uniqid(rand(), 1));

            // urltokenに一致し、未登録かつ仮登録から24時間以内のレコードを取り出す
            $sql = 'SELECT flag FROM pre_member WHERE urltoken=:urltoken AND flag=0 AND date > now() - interval 24 hour';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
            $stmt->execute();

            // 取り出したレコード数が0なら、トークンは被っていない
            $row_count = $stmt->rowCount();
            if ($row_count == 0) break;
        }
        // クリック用アドレス
        $url = 'https://tb-220042.tech-base.net'.__DIR__.'/register_page_2.php'.'?urltoken='.$urltoken;


        // 既に同じメールアドレスで本登録されていないか？

        $sql = 'SELECT flag FROM member WHERE mail=:mail';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mail', $mail_address, PDO::PARAM_STR);
        $stmt->execute();
        $row_count = $stmt->rowCount();

        // 既に本登録されている場合
        if ($row_count) {
            $errors["mail_used"] = "入力されたメールアドレスは既に使用されています。ログインしてください。";
        
        // 本登録されていない場合
        } else {
            // 過去に仮登録されていないか？
            $sql = 'SELECT flag FROM pre_member WHERE mail=:mail';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':mail', $mail_address, PDO::PARAM_STR);
            $stmt->execute();
            $row_count = $stmt->rowCount();
            // 過去に仮登録されている場合
            if ($row_count) {
                // 仮登録テーブルの内容を更新
                $sql = 'UPDATE pre_member SET urltoken=:urltoken,date=now(),flag=0 WHERE mail=:mail';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
                $stmt->bindParam(':mail', $mail_address, PDO::PARAM_STR);
                $stmt->execute();
    
            // 過去に仮登録されていない場合
            } else {
                // 仮登録テーブルに登録
                $sql = 'INSERT INTO pre_member (urltoken, mail, date) VALUES (:urltoken, :mail, now())';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
                $stmt -> bindParam(':mail', $mail_address, PDO::PARAM_STR);
                $stmt -> execute();
            }

            // メール送信
            require '/public_html/phpmailer/send_register_mail.php';
        }

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
    </body>
</html>