<?php
// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $select_sth = $dbh->prepare("SELECT * FROM users WHERE email = :email ORDER BY id DESC LIMIT 1");
    $select_sth->execute([':email' => $_POST['email']]);
    $user = $select_sth->fetch();

    if (empty($user)) {
        header("HTTP/1.1 302 Found");
        header("Location: ./login.php?error=1");
        return;
    }

    if (!password_verify($_POST['password'], $user['password'])) {
        header("HTTP/1.1 302 Found");
        header("Location: ./login.php?error=1");
        return;
    }

    session_start();
    $_SESSION["login_user_id"] = $user['id'];

    header("HTTP/1.1 302 Found");
    header("Location: ./bbs.php");
    return;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>ログイン</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <!-- ヘッダー -->
  <header class="bg-blue-600 text-white">
    <div class="container mx-auto px-4 py-4">
      <h1 class="text-xl font-bold">サイトログイン</h1>
    </div>
  </header>

  <!-- メイン -->
  <main class="container mx-auto px-4 py-8 flex-1">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
      <h2 class="text-2xl font-bold mb-6 text-center">ログイン</h2>

      <form method="POST" class="space-y-4">
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
            required
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300"
          >
        </div>

        <?php if (!empty($_GET['error'])): ?>
          <div class="text-red-500 text-sm">
            メールアドレスかパスワードが間違っています。
          </div>
        <?php endif; ?>

        <div>
          <button
            type="submit"
            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors"
          >
            決定
          </button>
        </div>
      </form>

      <!-- サインアップリンクを追加 -->
      <div class="text-center mt-4">
        <p class="text-sm text-gray-700">
          アカウントをまだお持ちでないですか？
          <a href="./signup.php" class="text-blue-500 hover:underline">
            会員登録はこちら
          </a>
        </p>
      </div>
    </div>
  </main>

  <footer class="bg-gray-800 text-gray-100 py-4 text-center text-sm">
    &copy; 2025 My BBS. All rights reserved.
  </footer>
</body>
</html>

