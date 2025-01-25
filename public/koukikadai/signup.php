<?php
// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    // name, email, password がPOSTで送られてきた場合 → DB登録
    $insert_sth = $dbh->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $insert_sth->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
    ]);

    // 登録完了後、ログイン画面へリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./login.php");
    return;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>会員登録</title>
  <!-- Tailwind CSSをCDNから読み込み -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- ヘッダー -->
  <header class="bg-blue-600 text-white">
    <div class="container mx-auto px-4 py-4">
      <h1 class="text-xl font-bold">会員登録</h1>
    </div>
  </header>

  <!-- メインコンテンツ -->
  <main class="container mx-auto px-4 py-8 flex-1">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
      <h2 class="text-2xl font-bold mb-6 text-center">新規アカウント作成</h2>

      <form method="POST" class="space-y-4">
        <div>
          <label class="block mb-1 font-semibold">名前</label>
          <input
            type="text"
            name="name"
            required
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300"
          >
        </div>
        <div>
          <label class="block mb-1 font-semibold">メールアドレス</label>
          <input
            type="email"
            name="email"
            required
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300"
          >
        </div>
        <div>
          <label class="block mb-1 font-semibold">パスワード</label>
          <input
            type="password"
            name="password"
            minlength="6"
            autocomplete="new-password"
            required
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300"
          >
        </div>

        <div>
          <button
            type="submit"
            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors"
          >
            決定
          </button>
        </div>
      </form>
    </div>
  </main>

  <!-- フッター -->
  <footer class="bg-gray-800 text-gray-100 py-4 text-center text-sm">
    &copy; 2025 My BBS. All rights reserved.
  </footer>

</body>
</html>

