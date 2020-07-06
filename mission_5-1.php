<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>mission_5-01</title>
    </head>
    <body>
        <?php
            ini_set('display_errors', 'On');

            // データベースに接続
            $dsn = 'データベース名';
            $user = 'ユーザー名';
            $password = 'password';
            $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

            // テーブルがなければ作る
            $sql = "CREATE TABLE IF NOT EXISTS bb"
            ."("
            ."id INT AUTO_INCREMENT PRIMARY KEY,"
            ."name char(32),"
            ."comment TEXT,"
            ."time DATETIME,"
            ."password char(32)"
            .");";
            $stmt = $pdo->query($sql);
            

            // フォームの初期値
            $form_editing_id = "";
            $form_comment_name = "名前";
            $form_comment_str = "コメント";
            $form_comment_password = "";
            
            // 送信フォームから送信されたとき
            if (isset($_POST["submit"]) && $_POST["comment"] != "") {
                if ($_POST["name"] == "") $name = "匿名";
                else $name = $_POST["name"];
                $comment = $_POST["comment"];
                $password = $_POST["password"];
                
                // 編集中の場合
                if ($_POST["editing_num"] != "") {
                    $edit_num = $_POST["editing_num"];
                    
                    $sql = 'UPDATE bb SET name=:name,comment=:comment,password=:password WHERE id=:id';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt -> bindParam(':id', $edit_num, PDO::PARAM_STR);
                    $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt -> execute();

                // 新規投稿の場合
                } else {
                    $time = date("Y/m/d G:i:s");

                    $sql = 'INSERT INTO bb (name, comment, time, password) VALUES (:name, :comment, :time, :password)';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt -> bindParam(':time', $time, PDO::PARAM_STR);
                    $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt -> execute();
                }
                
            // 削除フォームから送信されたとき
            } elseif (isset($_POST["delete"]) && $_POST["del_num"] != "") {
                $del_num = $_POST["del_num"];
                $password = $_POST["password_delete"];

                if ($password != "") {
                    $sql = 'DELETE FROM bb WHERE id=:id AND password=:password';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':id', $del_num, PDO::PARAM_INT);
                    $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt -> execute();
                }

            // 編集フォームから送信されたとき
            } elseif (isset($_POST["edit"]) && $_POST["edit_num"] != "") {
                $edit_num = $_POST["edit_num"];
                $password = $_POST["password_edit"];
                
                if ($password != "") {
                    $sql = 'SELECT * FROM bb WHERE id=:id AND password=:password';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':id', $edit_num, PDO::PARAM_INT);
                    $stmt -> bindParam(':password', $password, PDO::PARAM_STR);
                    $stmt -> execute();
                    $result = $stmt -> fetchAll();
                    
                    if (!empty($result)) {
                        $result = $result[0];
                        $form_editing_id = $result['id'];
                        $form_comment_name = $result['name'];
                        $form_comment_str = $result['comment'];
                        $form_comment_password = $result['password'];
                    }
                }
            }
        ?>
        
        この掲示板のテーマ：　知ってると便利そうな小ネタ<br>
        
        <form action="" method="post">
            <input type="hidden" name="editing_num" value=<?php echo $form_editing_id ?>>
            <input type="text" name="name" value=<?php echo $form_comment_name ?>>
            <input type="text" name="comment" value=<?php echo $form_comment_str ?>>
            <input type="password" name="password" value=<?php echo $form_comment_password ?>>
            <input type="submit" name="submit">
        </form>
        <form action="" method="post">
            <input type="number" name="del_num" >
            <input type="password" name="password_delete">
            <input type="submit" name="delete" value="削除">
        </form>
        <form action="" method="post">
            <input type="number" name="edit_num" >
            <input type="password" name="password_edit">
            <input type="submit" name="edit" value="編集">
        </form>
        
        <?php
            // データベースの内容を表示
            $sql = 'SELECT * FROM bb';
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchAll();
            
            foreach ($results as $row) {
                $date_time = explode(" ", $row['time']);
                $time = $date_time[1];
                $date = explode("-", $date_time[0]);
                $date = implode("/", $date);
                $datetime = $date." ".$time;
                
                echo $row['id']." ";
                echo $row['name']." ";
                echo $row['comment']." ";
                echo $datetime."<br>";
            }
        ?>
    </body>
</html>