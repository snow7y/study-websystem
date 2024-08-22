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

// 返信データを取得
$replies_sth = $dbh->prepare("SELECT * FROM replies WHERE post_id = :post_id ORDER BY id ASC");
$replies_sth->execute([":post_id" => $post_id]);
$replies = $replies_sth->fetchAll(PDO::FETCH_ASSOC);


// >>1をリンクに置き換える関数
function convertReplyAnchors($content)
{
    // ">>数字" にマッチする部分を探し、それをリンクに置き換える
    return preg_replace_callback('/&gt;&gt;(\d+)/', function ($matches) {
        $reply_id = intval($matches[1]);
        // リンクとして表示。指定された返信IDの場所にスクロールさせるリンクを生成
        return '<a href="#reply-' . $reply_id . '">>>' . $reply_id . '</a>';
    }, htmlspecialchars($content));
}

// 返信を一気に20個投稿する関数
if (isset($_POST['reply20times']))
{
    global $dbh;
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

        .post-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .post-content {
            margin-bottom: 20px;
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
        <div class="post-title" id="reply-1"><?= htmlspecialchars($post['title']) ?></div>
        <div class="post-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
        <div class="post-date">投稿日: <?= htmlspecialchars($post['created_at']) ?></div>

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
            <?php foreach ($replies as $reply): ?>
                <div class="reply" id="reply-<?= $reply['id'] ?>">
                    <div class="reply-header">
                        <span class="reply-header-span">ID: <?= htmlspecialchars($reply['reply_number']) ?></span>
                        <span class="reply-header-span">投稿日: <?= htmlspecialchars($reply['created_at']) ?></span>
                        <a class="reply-anchor" href="#reply"
                            onclick="document.getElementById('reply-content').value += '>><?= $reply['reply_number'] ?> ';"><span>返信する</span></a>
                    </div>
                    <div class="reply-content"><?= nl2br(convertReplyAnchors($reply['content'])) ?></div>
                </div>
            <?php endforeach ?>
        <?php endif ?>

        <hr>

        <!-- 返信フォーム -->
        <h2>返信を投稿する</h2>
        <form class="reply-form" action="post.php?id=<?= $post_id ?>" method="post">
            <textarea id="reply-content" name="content" rows="5" placeholder="返信内容を入力" required></textarea>
            <input type="submit" value="返信する">
        </form>
    </div>
</body>

</html>