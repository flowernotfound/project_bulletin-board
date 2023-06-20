<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>掲示板</title>
</head>
<body>
    <?php
    // データベースに接続
    $dsn = 'mysql:dbname=db;host=localhost';
    $user = 'user';
    $password = 'password';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    // テーブルを作成
        $sql = "CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            comment TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            password VARCHAR(255)
            )";
        $stmt = $pdo->query($sql);

            if (isset($_POST["submitEdit"])) {
                if (isset($_POST["edit"]) && trim($_POST["edit"]) != "") { //投稿番号が指定されているか
                    $editNum = $_POST["edit"];

                    // 投稿をデータベースから取得
                    $sql = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
                    $sql->bindParam(":id", $editNum, PDO::PARAM_INT);
                    $sql->execute();
                    $editPost = $sql->fetch();
                    
                    if ($editPost) { // 投稿が存在しているか
                        if (isset($editPost["password"]) && trim($editPost["password"]) != "") { // パスワードが投稿に設定されているか
                            if (isset($_POST["editPass"]) && trim($_POST["editPass"]) != "") { // パスが入力されているか
                                $editPass = $_POST["editPass"];
                                if ($editPost["password"] == $editPass) { // パスがあっているか
                                    $editName = $editPost["name"];
                                    $editTxt = $editPost["comment"];
                                } else { // パスが間違っているとき
                                    echo "パスワードが一致しません<br>";
                                }
                            } else { // パスが入力されていないとき
                                echo "パスワードを入力してください<br>";
                            }
                        } else { // パスワードが設定されていないとき
                            echo "この投稿はパスワードが設定されていないため編集できません<br>";
                        }
                    } else { // 投稿が存在しないとき
                        echo "指定された投稿は存在しません<br>";
                    }
                } else if (!(isset($_POST["edit"]) && trim($_POST["edit"]) != "")) {
                    echo "投稿番号を入力してください<br>";
                }
            }

    ?>
    
    <form method = "post" action="">
        <input type= "hidden" name="editNum" value="<?php echo isset($editNum) ? $editNum : ""; ?>">
        <input type= "text" name = "txt" placeholder = "コメントを入力" value = "<?php echo isset($editTxt) ? $editTxt : ""; ?>">
        <input type= "text" name = "name" placeholder = "名前を入力" value = "<?php echo isset($editName) ? $editName : ""; ?>">
        <input type= "text" name = "pass" placeholder = "パスワードを入力">
        <input type="submit" name="submit" id="" value = "投稿">
    </form>
    <form method = "post" action="">
        <input type= "number" name = "del" placeholder = "削除番号を入力">
        <input type= "text" name = "delPass" placeholder = "パスワードを入力">
        <input type="submit" name="submitDel" id="" value = "削除">
    </form>
    <form method = "post" action="">
        <input type= "number" name = "edit" placeholder = "編集番号を入力">
        <input type= "text" name = "editPass" placeholder = "パスワードを入力">
        <input type="submit" name="submitEdit" id="" value = "編集">
    </form>
    <?php
        if (isset($_POST["submit"])) {
            if (isset($_POST["txt"]) && trim($_POST["txt"]) != "" && isset($_POST["name"]) && trim($_POST["name"]) != "") {
                $text = $_POST["txt"];
                $name = $_POST["name"];
                $pass = $_POST["pass"];
                echo $text . " を受け付けました<br>";
                
                if (isset($_POST["editNum"]) && trim($_POST["editNum"])) {
                    // 編集の時
                    $editNum = $_POST["editNum"];
                    $sql = $pdo->prepare("UPDATE posts SET name = :name, comment = :comment, password = :password WHERE id = :id");
                    $sql->bindParam(":name", $name, PDO::PARAM_STR);
                    $sql->bindParam(":comment", $text, PDO::PARAM_STR);
                    $sql->bindParam(":password", $pass, PDO::PARAM_STR);
                    $sql->bindParam(":id", $editNum, PDO::PARAM_INT);
                    $sql->execute();                    

                } else {
                    // 新規投稿
                    $sql = $pdo->prepare("INSERT INTO posts (name, comment, password) VALUES (:name, :comment, :password)");
                    $sql->bindParam(":name", $name, PDO::PARAM_STR);
                    $sql->bindParam(":comment", $text, PDO::PARAM_STR);
                    $sql->bindParam(":password", $pass, PDO::PARAM_STR);
                    $sql->execute();
                    $editNum = "";
                }    
            }
            
            
        }

// 削除処理
        if (isset($_POST["submitDel"])) {
            if (isset($_POST["del"]) && trim($_POST["del"]) != "") { //投稿番号が指定されているか
                $delNum = $_POST["del"];
                
                // 削除対象をデータベースから取得
                $sql = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
                $sql->bindParam(":id", $delNum, PDO::PARAM_INT);
                $sql->execute();
                $delPost = $sql->fetch();

                if ($delPost) { // 削除対象が存在している場合
                    if (isset($_POST["delPass"]) && trim($_POST["delPass"]) != "") { // パスが入力されているか
                        $delPass = $_POST["delPass"];

                        if ($delPost["password"] == $delPass) { // パスがあっているか
                            // 削除実行
                            $sql = $pdo->prepare("DELETE FROM posts WHERE id = :id");
                            $sql->bindParam(":id", $delNum, PDO::PARAM_INT);
                            $sql->execute();
                        } else { // パスが一致しないとき
                            echo "パスワードが一致しません<br>";
                        }
                    } else { // パスが入力されていないとき
                        echo "パスワードを入力してください<br>";
                    }
                } else { //削除対象が見つからない場合
                    echo "指定された投稿は存在しません<br>";
                }
            } else if (!(isset($_POST["del"]) && trim($_POST["del"]) != "")) {
                echo "投稿番号を入力してください<br>";
            }
        }
    
        // ブラウザに出力 データベース
        $sql = "SELECT id, name, comment, created_at FROM posts ORDER BY id";
        $result = $pdo->query($sql);
        foreach ($result as $row) {
            echo $row["id"] . " ";
            echo $row["name"] . " ";
            echo $row["comment"] . " ";
            echo $row["created_at"] . "<br>";
        }
    ?>
</body>
</html>
