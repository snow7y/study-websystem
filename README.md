# ウェブ技術各論後期課題

## 始め方
1. このリポジトリを手元に持ってくる
    ```bash
    git clone https://github.com/yukikimoto/study-websystem.git
    ```
2. Dockerfileを使用して環境を構築する
   ```bash
   cd study-websystem
   docker compose up --build
   ```
3. データベースにテーブルを作成する
   ``` bash
   docker compose exec mysql mysql kyototech
   ```
   SQL文でテーブルを作成
   usersテーブル
   ```sql
    CREATE TABLE `users` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` TEXT NOT NULL,
        `email` TEXT NOT NULL,
        `password` TEXT NOT NULL,
        `image_filename` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ```
    投稿テーブル
    ```sql
    CREATE TABLE `bbs_entries` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT UNSIGNED NOT NULL,
        `body` TEXT NOT NULL,
        `image_filename` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ```
    作れたかどうか確認する
    ```sql
    show tables;
    ```
4. ブラウザで`localhost/koukikadai/login.php`にアクセスすることで見ることができます。
