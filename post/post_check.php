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
	$contest = $_POST['contest'];
	$problem = $_POST['problem'];
    $language = $_POST['language'];
    $score = $_POST['score'];
	$result = $_POST['result'];
	$url = $_POST['url'];
	$comment = $_POST['comment'];
	
	//前後にある半角全角スペースを削除
	$contest = spaceTrim($contest);
	$problem = spaceTrim($problem);
	$language = spaceTrim($language);
	$comment = spaceTrim($comment);

	// contest入力判定
	if ($contest == '') {
		$errors['contest_empty'] = "コンテスト名が入力されていません。";
    } elseif (mb_strlen($contest)>30) {
		$errors['contest_length'] = "コンテスト名は30文字以内で入力して下さい。";
    }
	
	// problem入力判定
	if ($problem == '') {
		$errors['problem_empty'] = "問題名が入力されていません。";
    } elseif (mb_strlen($problem)>30) {
		$errors['problem_length'] = "問題名は30文字以内で入力して下さい。";
    }
	
	// language入力判定
	if ($language == '') {
		$errors['language_empty'] = "言語が入力されていません。";
    } elseif (mb_strlen($language)>10) {
		$errors['language_length'] = "言語は10文字以内で入力して下さい。";
    }
		
	// URL入力判定
	if ($url == '') {
		$errors['url_empty'] = "提出URLが入力されていません。";
    } elseif (mb_strlen($url)>128) {
		$errors['url_length'] = "URLは128文字以内で入力して下さい。";
    }
	
    // コメント長さ判定
    if (mb_strlen($comment)>40) {
        $errors['comment_length'] = "コメントは40文字以内で入力して下さい。";
    }
    
    // エラーがなければ
    if (!count($errors)) {
        try{
            //例外処理を投げる（スロー）ようにする
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // データベースに登録
            $name = $_SESSION['name'];
            $time = date("Y/m/d G:i:s");
            $sql = 'INSERT INTO post (name, contest, problem, language, score, result, url, time, comment) VALUES (:name, :contest, :problem, :language, :score, :url, :time, :comment)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':contest', $contest, PDO::PARAM_STR);
            $stmt->bindParam(':problem', $problem, PDO::PARAM_STR);
            $stmt->bindParam(':language', $language, PDO::PARAM_STR);
            $stmt->bindParam(':score', $score, PDO::PARAM_INT);
            $stmt->bindParam(':result', $result, PDO::PARAM_STR);
            $stmt->bindParam(':url', $url, PDO::PARAM_STR);
            $stmt->bindParam(':time', $time, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->execute();

            // トップページに移動
            header("Location: /top_page.php");
            exit;
                        
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
        <title>投稿</title>
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