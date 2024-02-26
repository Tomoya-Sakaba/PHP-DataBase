<?php
$db = new mysqli('localhost:3306', 'root', 'root', 'mydb');

$errors = [];

// 投稿ボタンが押された時の処理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send'])) {
    // タイトルとメッセージを取得
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS);

    // タイトルとメッセージのバリデーション
    if (empty($title)) {
        $errors[] = "タイトルは必須です。";
    } elseif (mb_strlen($title) > 30) {
        $errors[] = "タイトルは30文字以下で入力してください。";
    }

    if (empty($message)) {
        $errors[] = "記事は必須です。";
    }

    // エラーがなければデータベースに投稿を追加
    if (empty($errors)) {
        $sql = "INSERT INTO posts (title, message, created) VALUES (?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $title, $message);
        $stmt->execute();
        $stmt->close();
    }
}

function Errors($errors)
{
    $i = 0;
    while ($i < count($errors)) {
        echo "<li>{$errors[$i]}</li>";
        $i++;
    }
}

// 投稿一覧の取得
$sql = "SELECT id, title, message FROM posts";
$result = $db->query($sql);

// 投稿の表示
function news($result)
{
    while ($row = $result->fetch_assoc()) {
        echo "<li>";
        echo "<h3>" . $row["title"] . "</h3>";
        echo "<p class='post'>" . $row["message"] . "</p>";
        echo "<a href='detail.php?id=" . $row["id"] . "'>記事全文・コメントを見る</a>";
        echo "</li>";
        echo "<hr>";
    }
}

// データベース接続を閉じる
$db->close();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/sanitize.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/home.css">
    <title>掲示板</title>
    <script>
        // 投稿ボタンを押した時に確認ダイアログを表示する処理
        function confirmSubmit() {
            return confirm("投稿してもよろしいでしょうか？");
        }
    </script>
</head>

<body>
    <header>
        <!-- ナビゲーションバーにホームに戻るリンクを貼る -->
        <h1><a href="index.php">Laravel News</a></h1>
    </header>
    <h2>さあ、最新のニュースシェアしましょう</h2>
    <div class="errors">
        <ul>
            <?php Errors($errors) ?>
        </ul>
    </div>
    <form action="" method="post" onsubmit="return confirmSubmit()">
        <div class="form">
            <label for="title">タイトル：</label>
            <input type="text" id="title" name="title">
        </div>
        <div class="form">
            <label for="message">記事：</label>
            <textarea id="message" name="message"></textarea>
        </div>
        <div class="container">
            <input type="submit" name="send" value="投稿" class="btn">
        </div>
    </form>
    <hr>
    <div class="contents">
        <ul>
            <?php news($result) ?>
        </ul>
    </div>
</body>

</html>