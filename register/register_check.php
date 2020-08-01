<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
    header('X-FRAME-OPTIONS: SAMEORIGIN');

    // 入力前後の半角全角スペースを削除する関数
    function spaceTrim($str) {
        $str = preg_replace('/^[ 　]+/u', '', $str);
        $str = preg_replace('/[ 　]+$/u', '', $str);
        return $str;
    }

    $errors = array();

    // CSRF
    if (!isset($_SESSION['token']) || $_POST['token'] != $_SESSION['token']){
        echo "エラーが発生しました。";
        exit;
    }
    
    // 入力ページから遷移していなければ
    if (empty($_POST)) {
        // ユーザー登録ページに飛ばす
        header("Location: register_page.php");
        exit;
    
    // 入力ページから遷移していれば
    } else {
        // POSTされたデータを変数に入れてスペースを取る
        $name = isset($_POST['name']) ? spaceTrim($_POST['name']) : NULL;
        $password = isset($_POST['password']) ? spaceTrim($_POST['password']) : NULL;

        // 正しい入力か判定
        if ($name == '') {
            $errors['name_empty'] = "ユーザー名が入力されていません。";
        } elseif (mb_strlen($name) > 15) {
            $errors['name_length'] = "ユーザー名は15文字以内で入力してください。";
        }
        if ($password == '') {
            $errors['password_empty'] = "パスワードが入力されていません。";
        } elseif (!preg_match('/^[0-9a-zA-Z]{1,30}$/', $password)) {
            $errors['password_length'] = "パスワードは半角英数字30文字以内で入力してください。";
        } else {
            $password_hide = str_repeat('*', strlen($password));
        }
    }

    // エラーが無ければセッションに登録
    if (count($errors) == 0) {
        $_SESSION['name'] = $name;
        $_SESSION['password'] = $password;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ユーザー登録確認画面</title>
    </head>
    <body>
        <!-- エラーがある場合 -->
        <?php if (count($errors)): ?>
            <?php
                foreach ($errors as $error) {
                    echo $error."<br>";
                }
            ?>
            <form>
                <input type="button" value="戻る" onClick="history.back()">
            </form>

        <!-- エラーがない場合 -->
        <?php else: ?>
            以下の項目を確認し、問題が無ければ登録ボタンをクリックしてください。<br>
            <form action="register_complete.php" method="post">
                <input type="hidden" name="token" value=<?=$_POST['token']?>>
                <table>
                    <tr>
                        <td>メールアドレス：</td>
                        <td><?=$_SESSION['mail']?></td>
                    <tr>
                        <td>ユーザー名（表示名）：</td>
                        <td><?=$name?></td>
                    </tr>
                    <tr>
                        <td>パスワード（半角英数字）：</td>
                        <td><?=$password_hide?></td>
                    </tr>
                    <tr>
                        <td><input type="button" value="戻る" onClick="history.back()"></td>
                        <td><input type="submit" value="登録"></td>
                    </tr>
                </table>
            </form>

        <?php endif; ?>
    </body>
</html>