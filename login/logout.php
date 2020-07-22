<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
 
    // ログイン状態のチェック
    if (!isset($_SESSION["name"])) {
        header("Location: /top_page.php");
        exit();
    }
     
    //セッション変数を全て解除
    $_SESSION = array();
     
    //セッションクッキーの削除
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 1800, '/');
    }
     
    //セッションを破棄する
    session_destroy();
    header("Location: /top_page.php");
