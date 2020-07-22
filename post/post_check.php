<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
    header('X-FRAME-OPTIONS: SAMEORIGIN');

    // ログインされていなければ
    if(!isset($_SESSION['token'])) {
        // トップページに飛ばす
        header("Location: /top_page.php");
        exit;
    }

    // CSRF
    if ($_POST['token'] != $_SESSION['token']){
        echo "エラーが発生しました。";
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

    // データベースに接続
    require_once '/public_html/db.php';

	//POSTされたデータを各変数に入れる
	$url = isset($_POST['url']) ? $_POST['url'] : NULL;
	$comment = isset($_POST['comment']) ? $_POST['comment'] : NULL;
	
	//前後にある半角全角スペースを削除
	$url = spaceTrim($url);
	$comment = spaceTrim($comment);

	// URL入力判定
	if ($url == '') {
		$errors['url_empty'] = "提出URLが入力されていません。";
    } elseif (mb_strlen($url)>128) {
		$errors['url_length'] = "URLは128文字以内で入力して下さい。";
    }
	
    // コメント長さ判定
    if (mb_strlen($comment)>80) {
        $errors['comment_length'] = "コメントの長さが80文字を超えています。";
    }
    
    // エラーがなければ
    if (!count($errors)) {
        // 提出情報を取得する


        /*
        try{
            //例外処理を投げる（スロー）ようにする
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            //メールアドレスで検索
            $stmt = $pdo->prepare("SELECT * FROM member WHERE mail=:mail AND flag = 1");
            $stmt->bindValue(':mail', $url, PDO::PARAM_STR);
            $stmt->execute();
     
            //メールアドレスが一致
            if ($row = $stmt->fetch()) {
     
                $comment_hash = $row['comment'];
     
                //パスワードが一致
                if (comment_verify($comment, $comment_hash)) {
                    
                    //セッションハイジャック対策
                    session_regenerate_id(true);
                    
                    $_SESSION['url'] = $mail_address;
                    $_SESSION['name'] = $row['name'];
                    header("Location: /top_page.php");
                    exit;
                } else {
                    $errors['comment'] = "メールアドレスまたはパスワードが一致しません。";
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