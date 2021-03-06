<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
    header('X-FRAME-OPTIONS: SAMEORIGIN');
    $errors = array();

    // CSRF
    $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
    $token = $_SESSION['token'];

    
    // メールから遷移していない場合
    if (!isset($_GET["urltoken"])) {
        // ユーザー登録ページに飛ばす
        header("Location: register_page.php");
        exit;

    // メールから遷移している場合
    } else {
        try {
            $urltoken = $_GET["urltoken"];

            // データベースに接続
            require_once '/public_html/db.php';

            // urltokenに一致し、未登録かつ仮登録から24時間以内のレコードを取り出す
            // 同一アドレスからは複数の登録はないとする
            $sql = 'SELECT mail FROM pre_member WHERE urltoken=:urltoken AND flag=0 AND date > now() - interval 24 hour';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':urltoken', $urltoken, PDO::PARAM_STR);
            $stmt->execute();

            // 取り出したレコード数
            $row_count = $stmt->rowCount();

            // レコード数が0なら、期限切れまたは存在しないトークンまたは本登録済み
            if ($row_count == 0) {
                $errors['expire'] = 'エラーが発生しました。次のような原因が考えられます：<br>'
                .'リンクの有効期限が切れている。<br>'
                .'既にユーザー登録が完了している。<br>'
                .'無効なリンクを使用している。';
            
            // レコード数が複数なら、全く同時に複数人に同じトークンが発行された
            } elseif ($row_count > 1) {
                $errors['double'] = 'エラーが発生したため、初めから登録をやり直してください。';

            // レコード数が1なら、メールアドレスを変数に格納
            } else {
                $mail_array = $stmt->fetch();
                $mail = $mail_array['mail'];
                $_SESSION['mail'] = $mail;
            }
        } catch (PDOException $e) {
            print('Error:'.$e->getMessage());
            exit;
        }
    }
?>

<!-- ここから表示部分 -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo count($errors) ? "エラー" : "ユーザー登録"; ?></title>
    </head>
    <body>
        <!-- エラーがある場合 -->
        <?php if (count($errors)): ?>
            <?php
                foreach ($errors as $error) {
                    echo $error."<br>";
                }
            ?>
        <!-- エラーがない場合 -->
        <?php else: ?>
            以下の項目を記入してください。
            <form action="register_check.php" method="post">
                <input type="hidden" name="token" value=<?=$token?>>
                <table>
                    <tr>
                        <td>ユーザー名（表示名）：</td>
                        <td><input type="text" name="name"></td>
                    </tr>
                    <tr>
                        <td>パスワード（半角英数字）：</td>
                        <td><input type="password" name="password"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="submit" value="確認画面へ"></td>
                    </tr>
                </table>
            </form>
        <?php endif; ?>
    </body>
</html>