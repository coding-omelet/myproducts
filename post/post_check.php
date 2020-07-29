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

    function multiexplode ($delimiters,$string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    //エラーメッセージの初期化
    $errors = array();

    // データベースに接続
    require_once '/public_html/db.php';

    //POSTされたデータを各変数に入れる
	$url = $_POST['url'];
	$comment = $_POST['comment'];
	
    //前後にある半角全角スペースを削除
    $url = spaceTrim($url);
    $comment = spaceTrim($comment);
    
    // URL入力判定
    $url_comp = 'https://atcoder.jp/contests/';
	if ($url == '') {
		$errors['url_empty'] = "提出URLが入力されていません。";
    } elseif (mb_strlen($url)>128) {
		$errors['url_length'] = "URLは128文字以内で入力して下さい。";
    } elseif (strncmp($url, $url_comp, 28)) {
        $errors['url_match'] = "URLは \"https://atcoder.jp/contests/\" で始まる必要があります。";
    }
	
    // コメント長さ判定
    if (mb_strlen($comment)>40) {
        $errors['comment_length'] = "コメントは40文字以内で入力して下さい。";
    }


    // 提出情報を取得

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $html = curl_exec($ch);
    curl_close($ch);

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $base_node = $xpath->query('/html/body/div[@id="main-div"]')->item(0);
    $contest = $xpath->evaluate('string(nav//a[@class="contest-title"])', $base_node);
    $sub_info_node = $xpath->query('div[@id="main-container"]//div[@class="col-sm-12"]/div[2]/table', $base_node)->item(0);
    $time = $xpath->evaluate('string(tr[th="Submission Time"]/td/time)', $sub_info_node);
    $problem = $xpath->evaluate('string(tr[th="Task"]/td/a)', $sub_info_node);
    $language = $xpath->evaluate('string(tr[th="Language"]/td)', $sub_info_node);
    $score = $xpath->evaluate('number(tr[th="Score"]/td)', $sub_info_node);
    $result = $xpath->evaluate('string(tr[th="Status"]/td/span)', $sub_info_node);

    // contest入力判定
	if ($contest == '') {
		$errors['contest_empty'] = "コンテスト名の取得に失敗しました。";
    }

    // time整形
    $time = explode(" ", $time);
    $time = $time[0]." ".multiexplode(array("+","-"),$time[1])[0];
    // time入力判定
	if ($time == '') {
		$errors['time_empty'] = "提出日時の取得に失敗しました。";
    }
	
	// problem入力判定
	if ($problem == '') {
		$errors['problem_empty'] = "問題名の取得に失敗しました。";
    }
	
	// language入力判定
	if ($language == '') {
		$errors['language_empty'] = "言語の取得に失敗しました。";
    }
		
	// score入力判定
	if ($score == '') {
		$errors['score_empty'] = "得点の取得に失敗しました。";
    }

    // result入力判定
	if ($result == '') {
		$errors['result_empty'] = "結果の取得に失敗しました。";
    }
    

    // エラーがなければ
    if (!count($errors)) {
        try{
            //例外処理を投げる（スロー）ようにする
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // データベースに登録
            $name = $_SESSION['name'];
            $sql = 'INSERT INTO post (name, contest, problem, language, score, result, url, time, comment) VALUES (:name, :contest, :problem, :language, :score, :result, :url, :time, :comment)';
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