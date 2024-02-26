<?php
$db = new mysqli('localhost:3306', 'root', 'root', 'mydb');

if (isset($_GET['id']) && isset($_GET['comment_id'])) {
  // URLパラメータからコメントIDを取得
  $comment_id = $_GET['comment_id'];

  // コメントを削除するSQLを準備して実行
  $sql = "DELETE FROM comments WHERE id = ?";
  $stmt = $db->prepare($sql);
  $stmt->bind_param("i", $comment_id);
  $stmt->execute();
  $stmt->close();
}

// 元のページにリダイレクト
header("Location: detail.php?id={$_GET['id']}");
exit();
