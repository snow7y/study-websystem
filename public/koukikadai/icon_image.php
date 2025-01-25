<?php
session_start();
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
  if (preg_match('/^image\//', $_FILES['image']['type']) !== 1) {
    header("HTTP/1,1 302 Found");
    header("Location: ./icon_image.php");
    return;
  }

  $pathinfo = pathinfo($_FILES['image']['name']);
  $extension = $pathinfo['extension'];
  $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
  $filepath =  '/var/www/upload/image/' . $image_filename;
  move_uploaded_file($_FILES['image']['tmp_name'], $filepath);

  $insert_sth = $dbh->prepare("UPDATE users SET image_filename = :image_filename WHERE id = :id");
  $insert_sth->execute([
    ':image_filename' => $image_filename,
    ':id' => $_SESSION['login_user_id'],
  ]);

  header("HTTP/1.1 302 Found");
  header("Location: ./bbs.php");
  return;
}


$select_sth = $dbh->prepare('SELECT * FROM users WHERE id = :id');
$select_sth->execute([
  ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();

?>

<a href="/bbs.php">掲示板に戻る</a>

<h1>アイコン画像設定/変更</h1>
<div>
  <?php if(empty($user['image_filename'])): ?>
  現在未設定
  <?php else: ?>
  <img src="/image/<?= $user['image_filename'] ?>"
    style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data">
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image">
  </div>
  <button type="submit">アップロード</button>
</form>
