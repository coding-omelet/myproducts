<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");

    $errors = array();

    // 確認ページから遷移していなければ
    if (!isset($_POST['urltoken'])) {
        // ユーザー登録ページに飛ばす
        header("Location: register_page.php");
        exit;
    
    // 確認ページから遷移していれば
    } else {
        // データベースに接続
        require_once '/public_html/db.php';

        // データを変数に入れる
        $urltoken = $_POST['urltoken'];
        $mail = $_SESSION['mail'];
        $name = $_SESSION['name'];
        $password_hash = password_hash($_SESSION['password'], PASSWORD_DEFAULT);

        try {
            // 例外を投げるようにする
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // トランザクション開始
            $pdo->beginTransaction();

            // memberテーブルに本登録する
            $sql = 'INSERT INTO member (name,mail,password) VALUES (:name,:mail,:password)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
            $stmt->execute();

            // pre_memberテーブルのflagを1にする
            $sql = 'UPDATE pre_member SET flag=1 WHERE mail=(:mail)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam('mail', $mail, PDO::PARAM_STR);
            $stmt->execute();

            // トランザクション完了
            $pdo->commit();

            // セッション変数を全て削除
            $_SESSION = array();

            // セッションクッキーの削除
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 1800, '/');
            }

            // セッションを破棄する
            session_destroy();

        // エラーが起きたら
        } catch (PDOException $e) {
            // ロールバック
            $pdo->rollback();
            $errors['error'] = "通信中にエラーが発生しました。もう一度やり直してください。";
            print('Error:'.$e->getMessage());
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ユーザー登録完了</title>
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
            ユーザー登録が完了しました。<br>
            <a href="/login/login_form.php">ログイン</a>

        <?php endif; ?>
    </body>
</html>
