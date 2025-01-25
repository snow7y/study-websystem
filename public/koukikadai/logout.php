<?php
session_start();

// セッション情報を全てクリア
$_SESSION = array();
session_destroy();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ログアウト</title>
  <!-- Tailwind CSSをCDNから読み込み -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

  <!-- ヘッダー -->
  <header class="bg-blue-600 text-white">
    <div class="container mx-auto px-4 py-4">
      <h1 class="text-xl font-bold">ログアウト</h1>
    </div>
  </header>

  <!-- メインコンテンツ -->
  <main class="container mx-auto px-4 py-8 flex-1">
    <div class="max-w-lg mx-auto bg-white p-6 rounded shadow text-center">
      <?php
      // ここではセッション破棄後なので、login_user_idは常に空になっているはず
      if (empty($_SESSION['login_user_id'])):
      ?>
        <h2 class="text-2xl font-semibold mb-4">ログアウトしました</h2>
        <p class="mb-4">ご利用ありがとうございました。</p>
      <?php else: ?>
        <!-- 何らかの理由でセッションに残っていれば -->
        <h2 class="text-2xl font-semibold mb-4 text-red-500">ログイン状態です</h2>
        <p class="mb-4">意図しないセッション状態かもしれません。</p>
      <?php endif; ?>

      <a
        href="login.php"
        class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
      >
        ログインページへ戻る
      </a>
    </div>
  </main>

  <!-- フッター -->
  <footer class="bg-gray-800 text-gray-100 py-4 text-center text-sm">
    &copy; 2025 My BBS. All rights reserved.
  </footer>
</body>
</html>

