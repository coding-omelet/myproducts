<?php
    ini_set('session.gc_maxlifetime', "604800");
    ini_set('session.cookie_lifetime', "604800");
    session_start();
    header("Content-type: text/html; charset=utf-8");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ACbb</title>
    </head>
    <body>
        <h1>トップページ<br></h1>
        
        <!-- ログイン中の時 -->
        <?php if (isset($_SESSION['name'])): ?>
            <?php
                $name = $_SESSION['name'];
            ?>
            ログイン中：<?=$name?><br>
            <a href="login/logout.php">ログアウト<br></a>
            
        <!-- ログアウト中の時 -->
        <?php else: ?>
            <a href="register/register_page.php">ユーザー登録<br></a>
            <a href="login/login_form.php">ログイン<br></a>
        
        <?php endif; ?>

        <!-- 投稿フォームへのリンク -->
        <hr>
        <a href="post/post_form.php">投稿する<br></a>

        <!-- タイムラインを表示 -->
        <hr>
        <?php
            require_once '/public_html/db.php';

            // 新しい順に最大10件の投稿を取得
            $sql = "SELECT * FROM post ORDER BY id DESC LIMIT 10";
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();

            // 結果を表示
            echo "<table>
                <tr>
                    <td>ユーザー</td>
                    <td>コンテスト</td>
                    <td>問題</td>
                    <td>言語</td>
                    <td>得点</td>
                    <td>結果</td>
                    <td>日時</td>
                    <td>コメント</td>
                    <td>詳細</td>
                </tr>
            ";
            foreach ($results as $post) {
                echo "<tr>
                    <td>$post[name]</td>
                    <td>$post[contest]</td>
                    <td>$post[problem]</td>
                    <td>$post[language]</td>
                    <td>$post[score]</td>
                    <td>$post[result]</td>
                    <td>$post[time]</td>
                    <td>$post[comment]</td>
                    <td><a href=\"$post[url]\">詳細</a></td>
                </tr>";
            }      
            echo "</table>";
        ?>
    </body>
</html>