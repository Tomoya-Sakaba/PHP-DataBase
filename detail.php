<?php

$db = new mysqli('localhost:3306', 'root', 'root', 'mydb');

$errors = [];

// 投稿IDを取得
$id = $_GET['id'];

// 投稿の取得
$sql = "SELECT title, message FROM posts WHERE id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $message);
$stmt->fetch();
$stmt->close();

// &#13;&#10;という改行を表す文字列で保存されるためそれをbrタグに変換
function newLineText($val)
{
  $val = str_replace('&#13;&#10;', '<br>', $val);
  return $val;
}

// コメントを書くボタンを押した時の処理
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment_send'])) {
  $comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_SPECIAL_CHARS);
  $delete_new_line = str_replace(array("\r", "\n", "&#13;", "&#10;"), '', $comment_text);
  $comment_length = mb_strlen($delete_new_line);

  // コメントのバリデーション
  if (empty($comment_text)) {
    $errors[] = "コメントは必須です。";
  } elseif ($comment_length > 50) {
    $errors[] = "コメントは50文字以下で入力してください。";

    // 上記条件に該当しなければコメントを書くことができる処理
  } else {
    // コメントをデータベースに挿入
    $sql = "INSERT INTO comments (post_id, comment, created_at) VALUES (?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("is", $id, $comment_text);
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

// コメントの取得
$sql = "SELECT * FROM comments WHERE post_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result_comments = $stmt->get_result();
$stmt->close();

// コメントの表示
function comments($result_comments)
{
  while ($row = $result_comments->fetch_assoc()) {
    echo "<div class='box-item'>";
    echo "<p>" . newLineText($row["comment"]);
    echo "<a href='delete_comment.php?id=" . $row["post_id"] . "&comment_id=" . $row["id"] . "'>コメントを消す</a>";
    echo "</p>";
    echo "</div>";
  }
}


?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/sanitize.css">
  <link rel="stylesheet" href="css/common.css">
  <link rel="stylesheet" href="css/detail.css">
  <title>Detail Page</title>
</head>

<body>
  <header>
    <h1><a href="index.php">Laravel News</a></h1>
  </header>
  <div class="news">
    <h2><?php echo $title ?></h2>
    <!-- 改行を読み込むための関数 -->
    <p><?php echo newLineText($message); ?></p>
  </div>
  <hr>
  <div class="errors">
    <ul>
      <?php Errors($errors) ?>
    </ul>
  </div>
  <div class="comments">
    <div class="form">
      <form action="" method="post" class="box-item">
        <textarea id="comment_text" name="comment_text"></textarea>
        <input type="submit" name="comment_send" value="コメントを書く">
      </form>
    </div>
    <?php comments($result_comments) ?>
  </div>
  <script>
    const comments = document.querySelectorAll('.box-item');
    let i = 0;
    while (i < comments.length) {
      const comment = comments[i];
      comment.style.backgroundColor = ['#fff799', '#87cefa', '#ffdada'][Math.floor(Math.random() * 3)];
      i++;
    }
  </script>
</body>

</html>