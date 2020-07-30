<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
    header('X-FRAME-OPTIONS: SAMEORIGIN');

    // POSTされていなければ
    if(empty($_POST)) {
        // ログインページに飛ばす
        header("Location: login_form.php");
        exit;
    }

    // CSRF
    if (!isset($_SESSION['token']) || $_POST['token'] != $_SESSION['token']){
        echo "エラー";
        exit;
    }


    //前後にある半角全角スペースを削除する関数
    function spaceTrim ($str) {
        // 行頭
        $str = preg_replace('/^[ 　]+/u', '', $str);
        // 末尾
        $str = preg_replace('/[ 　]+$/u', '', $str);
        return $str;
    }

    //エラーメッセージの初期化
    $errors = array();

	//POSTされたデータを各変数に入れる
	$mail_address = isset($_POST['mail_address']) ? $_POST['mail_address'] : NULL;
	$password = isset($_POST['password']) ? $_POST['password'] : NULL;
	
	//前後にある半角全角スペースを削除
	$mail_address = spaceTrim($mail_address);
	$password = spaceTrim($password);

	//アカウント入力判定
	if ($mail_address == '') {
		$errors['mail_address_empty'] = "メールアドレスが入力されていません。";
    } elseif (mb_strlen($mail_address)>50) {
		$errors['mail_address_length'] = "メールアドレスは50文字以内で入力して下さい。";
    }
	
	//パスワード入力判定
	if ($password == '') {
		$errors['password_empty'] = "パスワードが入力されていません。";
    } elseif (!preg_match('/^[0-9a-zA-Z]{1,30}$/', $_POST["password"])) {
		$errors['password_length'] = "パスワードは半角英数字30文字以下で入力して下さい。";
    }
    
    // エラーがなければ
    if (!count($errors)) {
        try{
            // データベースに接続
            require_once '/public_html/db.php';

            //例外処理を投げる（スロー）ようにする
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //メールアドレスで検索
            $stmt = $pdo->prepare("SELECT * FROM member WHERE mail=:mail AND flag = 1");
            $stmt->bindValue(':mail', $mail_address, PDO::PARAM_STR);
            $stmt->execute();
     
            //メールアドレスが一致
            if ($row = $stmt->fetch()) {
     
                $password_hash = $row['password'];
     
                //パスワードが一致
                if (password_verify($password, $password_hash)) {
                    
                    //セッションハイジャック対策
                    session_regenerate_id(true);
                    
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['mail_address'] = $mail_address;
                    $_SESSION['name'] = $row['name'];
                    header("Location: /top_page.php");
                    exit;
                } else {
                    $errors['password'] = "メールアドレスまたはパスワードが一致しません。";
                }
            } else {
                $errors['mail'] = "メールアドレスまたはパスワードが一致しません。";
            }
                        
        } catch (PDOException $e) {
            print('Error:'.$e->getMessage());
            exit;
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ログイン確認画面</title>
    </head>
    <body>
        <!-- エラーがあるとき -->
        <?php if (count($errors)): ?>
            <?php
                foreach ($errors as $error) {
                    echo $error."<br>";
                }
            ?>
            <input type="button" value="戻る" onClick="history.back()">
        <?php endif; ?>
    <body>
</html>