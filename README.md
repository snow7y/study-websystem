# ウェブ技術各論前期課題

## 概要
このリポジトリはウェブ技術各論の前期課題で作成した掲示板のソースコードです。
主な機能としては、投稿、返信が可能になっています。
レスアンカー機能も実装しています。

## 始め方
1. このリポジトリを手元に持ってくる
    ```bash
    git clone https://github.com/yukikimoto/study-websystem.git
    ```
2. Dockerfileを使用して環境を構築する
   ```bash
   cd study-websystem
   docker compose up
   ```
3. データベースにテーブルを作成する
   ``` bash
   docker compose exec mysql mysql kyototech
   ```
   SQL文でテーブルを作成
   postsテーブル
   ```sql
    CREATE TABLE posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ```
    repliesテーブル
    ```sql
    CREATE TABLE replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        reply_number INT NOT NULL,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    );
    ```
    作れたかどうか確認する
    ```sql
    show tables;
    ```
4. ブラウザで`localhost/kadai/index.php`にアクセスすることで見ることができます。
