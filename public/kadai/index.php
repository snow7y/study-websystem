<?php
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

if (isset($_POST['title']) && isset($_POST['content'])) {
    // POSTで送られてくるフォームパラメータにtitleとcontentがある場合

    // insertする
    $insert_sth = $dbh->prepare("INSERT INTO posts(title, content) VALUES(:title, :content)");
    $insert_sth->execute([
        ":title" => $_POST["title"],
        ":content" => $_POST["content"],
    ]);

    // 処理が終わったらリダイレクトする
    // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
    $redirect_sth = $dbh->prepare("SELECT id FROM posts ORDER BY id DESC LIMIT 1");
    $redirect_sth->execute();
    $redirect = $redirect_sth->fetchColumn();
    header("HTTP/1.1 302 Found");
    header("Location: ./post.php?id={$redirect}");
    return;
}


// ページのカウント処理
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$count_per_page = 10;
$skip_count = $count_per_page * ($page - 1);

$count_sth = $dbh->prepare('SELECT COUNT(*) FROM posts;');
$count_sth->execute();

$count_all = $count_sth->fetchColumn();

if ($skip_count > $count_all) {
    // スキップする行数が全行数より多かったらおかしいのでエラーメッセージ表示し終了
    print ('このページは存在しません!');
    return;
}
$select_sth = $dbh->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT :count_per_page OFFSET :skip_count");
$select_sth->bindParam(':count_per_page', $count_per_page, PDO::PARAM_INT);
$select_sth->bindParam(':skip_count', $skip_count, PDO::PARAM_INT);
$select_sth->execute();


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>掲示板ホーム</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 80%;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
        }

        form {
            margin-bottom: 30px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            padding: 10px 20px;
        }

        .post {
            border-bottom: 1px solid #ccc;
            padding: 20px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>掲示板ホーム</h1>

        <!-- 投稿フォーム -->
        <form action="./index.php" method="post">
            <input type="text" name="title" placeholder="タイトル" required>
            <textarea name="content" rows="5" placeholder="内容" required></textarea>
            <input type="submit" value="投稿する">
        </form>

        <!-- 投稿の一覧表示 -->
        <?php if ($count_all == 0): ?>
            <p>ひとつも投稿がありません！</p>
        <?php else: ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 2em;">
                <div>
                    <?php if ($page > 1): // 前のページがあれば表示 ?>
                        <a href="?page=<?= $page - 1 ?>">前のページ</a>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($count_all > $page * $count_per_page): // 次のページがあれば表示 ?>
                        <a href="?page=<?= $page + 1 ?>">次のページ</a>
                    <?php endif; ?>
                </div>
            </div>

            <hr>
            <?php foreach ($select_sth as $data): ?>
                <dl>
                    <a href="./post.php?id=<?= $data['id'] ?>">
                        <dt>タイトル</dt>
                        <dd><?= $data["title"] ?></dd>
                    </a>

                    <dt>作成日時</dt>
                    <dd><?= $data["created_at"] ?></dd>
                </dl>
            <?php endforeach ?>
        <?php endif ?>
    </div>
</body>

</html>
