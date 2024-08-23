<?php
$dbh = new PDO('mysql:host=mysql;dbname=kyototech', 'root', '');

// 投稿IDを取得
$post_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$post_id) {
    echo "無効な投稿IDです。";
    return;
}

// 投稿データを取得
$post_sth = $dbh->prepare("SELECT * FROM posts WHERE id = :post_id");
$post_sth->execute([":post_id" => $post_id]);
$post = $post_sth->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo "投稿が見つかりません。";
    return;
}

// 返信を投稿する処理
if (isset($_POST['content'])) {
    // 返信の最大を取得
    $reply_number_sth = $dbh->prepare("SELECT MAX(reply_number) FROM replies WHERE post_id = :post_id");
    $reply_number_sth->execute([":post_id" => $post_id]);
    $reply_number = $reply_number_sth->fetchColumn();
    // もし返信がない場合は2をセットしそれ以外は+1する
    if (!$reply_number) {
        $reply_number = 2;
    } else {
        $reply_number++;
    }
    // 返信を挿入
    $insert_reply_sth = $dbh->prepare("INSERT INTO replies (post_id, reply_number, content) VALUES (:post_id, :reply_number, :content)");
    $insert_reply_sth->execute([
        ":post_id" => $post_id,
        ":reply_number" => $reply_number,
        ":content" => $_POST["content"],
    ]);

    // リダイレクトして再度ページを表示（リロード時の多重投稿防止）
    header("HTTP/1.1 302 Found");
    header("Location: ./post.php?id=" . $post_id);
    return;
}
// 返信を一気に20個投稿するボタンが押された場合の処理
if (isset($_POST['reply20times'])) {
    $insert_reply_sth = $dbh->prepare("INSERT INTO replies (post_id, reply_number, content) VALUES (:post_id, :reply_number, :content)");
    $i = 2;
    for (; $i <= 20; $i++) {
        $insert_reply_sth->execute([
            ":post_id" => $post_id,
            ":reply_number" => $i,
            ":content" => "ID: {$i} の返信です。",
        ]);
    }
    $insert_reply_sth->execute([
        ":post_id" => $post_id,
        ":reply_number" => $i,
        ":content" => "ID: {$i} の返信です。ID:1までのレスアンカーのテストです。 >>1",
    ]);
    header("HTTP/1.1 302 Found");
    header("Location: ./post.php?id=" . $post_id);
    return;
}



// ページのカウント処理
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$count_per_page = 10;
$skip_count = $count_per_page * ($page - 1);

$count_sth = $dbh->prepare('SELECT COUNT(*) FROM replies WHERE post_id = :post_id;');
$count_sth->execute([":post_id" => $post_id,]);

$count_all = $count_sth->fetchColumn();

if ($skip_count > $count_all) {
    // スキップする行数が全行数より多かったらおかしいのでエラーメッセージ表示し終了
    print ('このページは存在しません!');
    return;
}

// 返信データを取得
$replies_sth = $dbh->prepare("SELECT * FROM replies WHERE post_id = :post_id ORDER BY id ASC LIMIT :count_per_page OFFSET :skip_count");
$replies_sth->bindparam(':post_id', $post_id, PDO::PARAM_INT);
$replies_sth->bindParam(':count_per_page', $count_per_page, PDO::PARAM_INT);
$replies_sth->bindParam(':skip_count', $skip_count, PDO::PARAM_INT);
$replies_sth->execute();
$replies = $replies_sth->fetchAll(PDO::FETCH_ASSOC);

// html内で割り振るid用
$html_id = 1;

// >>1をリンクに置き換える関数
function convertReplyAnchors($post_id, $content)
{
    // ">>数字" にマッチする部分を探し、それをリンクに置き換える
    return preg_replace_callback('/&gt;&gt;(\d+)/', function ($matches) use ( $post_id) {
        $reply_id = intval($matches[1]);
        
        // reply_idの一桁目と二桁目以上を分け、二桁目以上がある場合pageに置き換える
        $page_id = intval(substr($reply_id, 0, -1)); // 二桁目以上が存在する場合、それをpage_idに
        $page_id ++;
        $reply_id = intval(substr($reply_id, -1));  // 一桁目をreply_idとして扱う

        // リンクとして表示。指定された返信IDの場所にスクロールさせるリンクを生成
        // page_idが0の場合（例えば、>>3 の場合）、リンクにはpage_idを含めない
        if ($page_id == 0) {
            return '<a href="#reply-' . $reply_id . '">>>' . $matches[1] . '</a>';
        } else {
            return '<a href="?id=' . $post_id . '&page=' . $page_id . '#reply-' . $reply_id . '">>>' . $matches[1] . '</a>';
        }
    }, htmlspecialchars($content));
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - 掲示板</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            width: 80%;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        
        .post-header {
            display: flex;
            justify-content: start;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .post-title {
            font-weight: bold;
            margin-right: 30px;
        }

        .post-header-content {
            margin-right: 10px;
        }

        .post-content {
            margin-left: 10px;
        }

        .reply {
            border-top: 1px solid #ccc;
            padding: 10px 0;
        }

        .reply-header {
            display: flex;
            justify-content: start;
            align-items: center;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .reply-header-span {
            margin-right: 10px;
        }

        .reply-content {
            margin-top: 5px;
            padding-left: 10px;
        }

        .reply-anchor {
            color: #00f;
            text-decoration: none;
            margin-right: 10px;
        }

        .reply-form {
            margin-top: 30px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }

        input[type="submit"] {
            padding: 10px 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>投稿詳細</h1>
            <a href="./index.php">一覧に戻る</a>
        </header>

        <!-- 投稿の表示 -->
        <div class="post-header">
            <h2 class="post-title" id="reply-<?= $html_id++ ?>"><?= htmlspecialchars($post['title']) ?></h2>
            <p class="post-header-content">ID: 1</p>
            <p class="post-header-content">投稿日: <?= htmlspecialchars($post['created_at']) ?></p>
        </div>
        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

        <hr>

        <!-- 返信の表示 -->
        <h2>返信一覧</h2>
        <?php if (empty($replies)): ?>
            <p>まだ返信がありません。</p>
            <a class="reply-anchor" href="#reply"
                onclick="document.getElementById('reply-content').value += '>>1 ';"><span>最初の返信をする</span></a>
            <form action="post.php?id=<?= $post_id ?>" method="post">
                <input type="submit" value="20個の返信を投稿する" name="reply20times">
            </form>
        <?php else: ?>

            <div style="display: flex; justify-content: space-between; margin-bottom: 2em;">
                <div>
                    <?php if ($page > 1): // 前のページがあれば表示 ?>
                        <a href="?id=<?= $post_id ?>&page=<?= $page - 1 ?>">前のページ</a>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($count_all > $page * $count_per_page): // 次のページがあれば表示 ?>
                        <a href="?id=<?= $post_id ?>&page=<?= $page + 1 ?>">次のページ</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php foreach ($replies as $reply): ?>
                <div class="reply" id="reply-<?= $html_id++ ?>">
                    <div class="reply-header">
                        <p class="reply-header-span">ID: <?= htmlspecialchars($reply['reply_number']) ?></p>
                        <p class="reply-header-span">投稿日: <?= htmlspecialchars($reply['created_at']) ?></p>
                        <a class="reply-anchor" href="#reply-form"
                            onclick="document.getElementById('reply-content').value += '>><?= $reply['reply_number'] ?> ';">返信する</a>
                    </div>
                    <div class="reply-content"><?= nl2br(convertReplyAnchors($post_id, $reply['content'])) ?></div>
                </div>
            <?php endforeach ?>
        <?php endif ?>

        <hr>

        <!-- 返信フォーム -->
        <h2 id="reply-form">返信を投稿する</h2>
        <form class="reply-form" action="post.php?id=<?= $post_id ?>" method="post">
            <textarea id="reply-content" name="content" rows="5" placeholder="返信内容を入力" required></textarea>
            <input type="submit" value="返信する">
        </form>
    </div>
</body>

</html>
