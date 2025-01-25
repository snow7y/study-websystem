<?php
session_start();
// DBへの接続。ユーザー情報があるテーブル(usersなど)にアイコン画像を保存する想定。
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

// ログインしているかどうかのチェック
if (!empty($_SESSION['login_user_id'])) {
    // ログインしている場合のみ、POSTで画像が送られてきたときの処理を行う
    if (isset($_FILES['icon_image']) && !empty($_FILES['icon_image']['tmp_name'])) {
        // 画像かどうかを簡単にチェック
        if (preg_match('/^image\//', mime_content_type($_FILES['icon_image']['tmp_name'])) !== 1) {
            // 画像ではない場合
            header("HTTP/1.1 302 Found");
            header("Location: ./icon_image.php?error=1");
            return;
        }

        // 元のファイル名から拡張子を取得
        $pathinfo = pathinfo($_FILES['icon_image']['name']);
        $extension = $pathinfo['extension'];
        
        // ファイル名をユニークに生成
        $image_filename = time() . bin2hex(random_bytes(16)) . '.' . $extension;
        
        // 実際にファイルを保存するパス。bbs.phpで利用したパスと同じようにするか、別フォルダを作るかは運用次第
        $filepath = '/var/www/upload/image/' . $image_filename;
        move_uploaded_file($_FILES['icon_image']['tmp_name'], $filepath);

        // DBのusersテーブルにアイコンファイル名を更新するカラム(icon_filename等)があると仮定
        // DB構造に合わせて修正
        $update_sth = $dbh->prepare("UPDATE users SET image_filename = :image_filename WHERE id = :id");
        $update_sth->execute([
            ':image_filename' => $image_filename,
            ':id' => $_SESSION['login_user_id'],
        ]);

        // アップロード完了後にリダイレクト
        header("HTTP/1.1 302 Found");
        header("Location: ./icon_image.php?uploaded=1");
        return;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>アイコン画像の設定</title>
  <!-- Tailwind CSSをCDNから読み込み -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- ヘッダー -->
  <header class="bg-blue-600 text-white">
    <div class="container mx-auto px-4 py-4">
      <h1 class="text-xl font-bold">アイコン画像の設定</h1>
    </div>
  </header>

  <!-- メインコンテンツ -->
  <main class="container mx-auto px-4 py-8 flex-1">
    <?php if (empty($_SESSION['login_user_id'])): ?>
      <!-- ログインしていない場合 -->
      <div class="max-w-lg mx-auto bg-white p-6 rounded shadow text-center">
        <h2 class="text-2xl font-semibold mb-4">ログインしていません</h2>
        <p class="mb-4">アイコン画像を変更するにはログインしてください。</p>
        <a
          href="login.php"
          class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
        >
          ログインページへ
        </a>
      </div>
    <?php else: ?>
      <!-- ログインしている場合のみ表示 -->
      <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-6 text-center">アイコン画像をアップロード</h2>

        <!-- アップロード完了メッセージ -->
        <?php if (!empty($_GET['uploaded'])): ?>
          <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded">
            アイコン画像が変更されました。
            <a href="./bbs.php">投稿サイトに戻る</a>
          </div>
        <?php endif; ?>

        <!-- エラーメッセージ -->
        <?php if (!empty($_GET['error'])): ?>
          <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded">
            画像ファイルを選択してください。
          </div>
        <?php endif; ?>

        <form method="POST" action="./icon_image.php" enctype="multipart/form-data" class="space-y-4">
          <div>
            <label for="icon_image" class="block mb-1 font-semibold">新しいアイコン画像</label>
            <input
              type="file"
              id="icon_image"
              name="icon_image"
              accept="image/*"
              class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300"
            >
            <p class="text-gray-500 text-sm mt-1">画像ファイル(5MB以下推奨)</p>
          </div>
          <div class="text-center">
            <button
              type="submit"
              class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
            >
              アップロード
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </main>

  <!-- フッター -->
  <footer class="bg-gray-800 text-gray-100 py-4 text-center text-sm">
    &copy; 2025 My BBS. All rights reserved.
  </footer>

  <!-- 容量チェックなどのJSが必要なら追加 -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const iconInput = document.getElementById('icon_image');
      if (iconInput) {
        iconInput.addEventListener('change', () => {
          if (iconInput.files.length < 1) return;
          // 5MB制限等をかける例
          if (iconInput.files[0].size > 5 * 1024 * 1024) {
            alert("5MB以下の画像を選択してください。");
            iconInput.value = "";
          }
        });
      }
    });
  </script>
</body>
</html>

