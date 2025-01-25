<?php
session_start();

// データベース接続 (元のコードと同様)
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

// 画像付き投稿処理 (元のコードと同様)
if (isset($_POST['body']) && !empty($_SESSION['login_user_id'])) {
    $image_filename = null;
    if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
        if (preg_match('/^image\//', mime_content_type($_FILES['image']['tmp_name'])) !== 1) {
            // 画像以外がアップロードされた場合
            header("HTTP/1.1 302 Found");
            header("Location: ./bbs.php");
            return;
        }
        $pathinfo = pathinfo($_FILES['image']['name']);
        $extension = $pathinfo['extension'];
        $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
        $filepath = '/var/www/upload/image/' . $image_filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
    }

    $insert_sth = $dbh->prepare("INSERT INTO bbs_entries (user_id, body, image_filename) VALUES (:user_id, :body, :image_filename);");
    $insert_sth->execute([
        ':user_id' => $_SESSION['login_user_id'],
        ':body'    => $_POST['body'],
        ':image_filename' => $image_filename,
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ./bbs.php");
    return;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>モダンデザインの投稿サイト (Tailwind版)</title>
  <!-- Tailwind CSSをCDNから読み込み -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- 必要に応じて追加カスタマイズ -->
  <style>
    /* カスタムで追加のスタイルがあればここに書く */
    .icon-img {
      width: 2.5rem;
      height: 2.5rem;
      object-fit: cover;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
  <!-- ナビゲーションバー相当 -->
  <header class="bg-blue-600 text-white">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
      <a href="./bbs.php" class="text-xl font-bold hover:opacity-90">
        My BBS
      </a>
      <nav class="space-x-4">
        <?php if(empty($_SESSION['login_user_id'])): ?>
          <a href="./login.php" class="hover:underline">ログイン</a>
        <?php else: ?>
          <a href="logout.php" class="hover:underline">ログアウト</a>
          <a href="./icon_image.php" class="hover:underline">アイコン画像設定</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- メインコンテンツ -->
  <main class="container mx-auto px-4 py-8 flex-1">
    <h1 class="text-3xl font-semibold text-center mb-8">Tailwindで作るモダン掲示板</h1>

    <?php if(empty($_SESSION['login_user_id'])): ?>
      <!-- ログインを促す表示 -->
      <div class="bg-yellow-100 border border-yellow-300 text-yellow-900 p-4 rounded-md">
        投稿するには
        <a href="./login.php" class="underline font-medium">
          ログイン
        </a>
        が必要である。
      </div>
    <?php else: ?>
      <!-- 投稿フォーム -->
      <div class="bg-white shadow rounded-md p-6 mb-8">
        <h2 class="text-xl font-bold mb-4">新規投稿</h2>
        <form method="POST" action="./bbs.php" enctype="multipart/form-data" class="space-y-4">
          <div>
            <label for="body" class="block mb-1 font-semibold">投稿内容</label>
            <textarea
              id="body"
              name="body"
              rows="4"
              class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300"
              placeholder="ここに投稿内容を入力"
            ></textarea>
          </div>
          <div>
            <label for="imageInput" class="block mb-1 font-semibold">画像ファイル (任意)</label>
            <input
              type="file"
              accept="image/*"
              name="image"
              id="imageInput"
              class="block w-full text-sm text-gray-900 border border-gray-300 rounded cursor-pointer bg-gray-50 focus:outline-none focus:ring focus:ring-blue-300"
            />
            <p class="text-gray-500 text-sm mt-1">5MB以下の画像をアップロードできる。</p>
          </div>
          <div>
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded focus:outline-none focus:ring-2 focus:ring-blue-300"
            >
              投稿
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>

    <!-- 投稿一覧表示領域。JavaScriptで挿入する -->
    <div id="entriesRenderArea" class="space-y-6"></div>
  </main>

  <!-- テンプレート: JavaScriptで複製して使う。display: noneをTailwindで代替するためhiddenクラスを使用する -->
  <dl
    id="entryTemplate"
    class="hidden bg-white shadow rounded-md p-4"
  >
    <div class="mb-3">
      <dt class="text-xs text-gray-500">投稿ID</dt>
      <dd data-role="entryIdArea" class="font-medium"></dd>
    </div>
    <div class="mb-3">
      <dt class="text-xs text-gray-500">投稿者</dt>
      <dd>
        <a
          href="#"
          data-role="entryUserAnchor"
          class="inline-flex items-center space-x-2"
        >
          <img data-role="entryUserIconImage" class="icon-img rounded-full" />
          <span data-role="entryUserNameArea" class="font-semibold"></span>
        </a>
      </dd>
    </div>
    <div class="mb-3">
      <dt class="text-xs text-gray-500">日時</dt>
      <dd data-role="entryCreatedAtArea"></dd>
    </div>
    <div class="mb-3">
      <dt class="text-xs text-gray-500">内容</dt>
      <dd data-role="entryBodyArea" class="mt-1"></dd>
    </div>
  </dl>

  <!-- フッター (必要に応じて) -->
  <footer class="bg-gray-800 text-gray-100 py-4 text-center text-sm">
    &copy; 2025 My BBS. All rights reserved.
  </footer>

  <!-- TailwindはCDNのみでOK。JSのバンドル等は基本不要。 -->
  <!-- ただしInteractivity(ダイアログ等)が必要な場合は別途Alpine.jsやHeadless UIなどを併用できる。 -->

  <!-- 投稿一覧を取得して表示するスクリプト -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const entryTemplate = document.getElementById("entryTemplate");
      const entriesRenderArea = document.getElementById("entriesRenderArea");

      // bbs_json.phpから投稿データを取得
      const request = new XMLHttpRequest();
      request.onload = (event) => {
        const response = event.target.response;
        response.entries.forEach((entry) => {
          // テンプレート要素をクローンする
          const entryCopied = entryTemplate.cloneNode(true);
          // hiddenクラスを削除して表示
          entryCopied.classList.remove("hidden");

          // 投稿ID
          entryCopied.querySelector("[data-role='entryIdArea']").innerText = entry.id.toString();

          // ユーザーアイコン
          const iconImg = entryCopied.querySelector("[data-role='entryUserIconImage']");
          if (entry.user_icon_file_url) {
            iconImg.src = entry.user_icon_file_url;
            iconImg.style.display = "inline-block";
          } else {
            iconImg.style.display = "none";
          }

          // ユーザー名
          entryCopied.querySelector("[data-role='entryUserNameArea']").innerText = entry.user_name;

          // 投稿日時
          entryCopied.querySelector("[data-role='entryCreatedAtArea']").innerText = entry.created_at;

          // 本文(HTML)
          entryCopied.querySelector("[data-role='entryBodyArea']").innerHTML = entry.body;

          // 画像があれば本文エリアに追加
          if (entry.image_file_url) {
            const imageElement = new Image();
            imageElement.src = entry.image_file_url;
            imageElement.classList.add("mt-3", "rounded", "shadow", "max-w-full");
            imageElement.style.maxHeight = "300px";
            entryCopied.querySelector("[data-role='entryBodyArea']").appendChild(imageElement);
          }

          // 実際に描画
          entriesRenderArea.appendChild(entryCopied);
        });
      };
      request.open("GET", "./bbs_json.php", true);
      request.responseType = "json";
      request.send();

      // 画像容量チェック
      const imageInput = document.getElementById("imageInput");
      if (imageInput) {
        imageInput.addEventListener("change", () => {
          if (imageInput.files.length < 1) {
            return;
          }
          if (imageInput.files[0].size > 5 * 1024 * 1024) {
            alert("5MB以下のファイルを選択してください。");
            imageInput.value = "";
          }
        });
      }
    });
  </script>
</body>
</html>

