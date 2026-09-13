# BookShelf 書籍レビューアプリ

## 概要

BookShelf は、書籍を登録・共有し、レビューやお気に入りを通じて本の魅力を発見できる書籍レビューアプリです。ユーザーは書籍の登録・編集・削除、レビューの投稿・いいね、お気に入り登録ができ、ジャンルごとの分類やレビュー平均評価によるランキング表示に対応しています。また、書籍情報を外部から取得するための公開 API を備えています。

### 主な機能

- 会員登録・ログイン・ログアウト（Laravel Fortify による認証）
- 書籍の一覧・詳細・登録・編集・削除（CRUD）
- レビューの投稿・編集・削除・いいね
- 書籍のお気に入り登録・解除
- ジャンルの管理（CRUD）と、ジャンル別の書籍表示
- レビュー平均評価によるランキング表示
- 公開 API（書籍の一覧・詳細・登録・更新・削除）

## ER図

```mermaid
erDiagram
    users ||--o{ books : "登録する"
    users ||--o{ reviews : "投稿する"
    users ||--o{ favorites : "お気に入り"
    users ||--o{ review_likes : "いいね"
    books ||--o{ reviews : "レビューされる"
    books ||--o{ favorites : "お気に入りされる"
    books ||--o{ book_genre : ""
    genres ||--o{ book_genre : ""
    reviews ||--o{ review_likes : "いいねされる"

    users {
        bigint id PK
        string name
        string email UK
        string password
        timestamps created_at_updated_at
    }
    genres {
        bigint id PK
        string name UK
        timestamps created_at_updated_at
    }
    books {
        bigint id PK
        bigint user_id FK
        string title
        string author
        string isbn UK
        date published_date
        text description "nullable"
        string image_url "nullable"
        timestamps created_at_updated_at
    }
    reviews {
        bigint id PK
        bigint user_id FK
        bigint book_id FK
        tinyint rating
        text comment
        timestamps created_at_updated_at
    }
    book_genre {
        bigint id PK
        bigint book_id FK
        bigint genre_id FK
        timestamps created_at_updated_at
    }
    favorites {
        bigint id PK
        bigint user_id FK
        bigint book_id FK
        timestamps created_at_updated_at
    }
    review_likes {
        bigint id PK
        bigint user_id FK
        bigint review_id FK
        timestamps created_at_updated_at
    }
```

## 使用技術

- PHP 8.2
- Laravel 10.x
- MySQL 8.4
- Laravel Sail（Docker）
- Laravel Fortify（認証）
- Vite / Tailwind CSS / Blade（フロントエンド）
- PHPUnit（テスト）
- Laravel Pint（コードフォーマット）
- phpMyAdmin

## 環境構築手順

本アプリは Laravel Sail（Docker）で動作します。以下は本リポジトリをクローンした状態からの手順です。

1. リポジトリをクローンする

```bash
git clone git@github.com:momo888-y/bookshelf-app.git
cd bookshelf-app
```

2. 依存パッケージをインストールする

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer install
```

3. 環境変数ファイルを用意する

```bash
cp .env.example .env
```

`.env` のデータベース接続情報が以下になっていることを確認してください（`DB_HOST` はコンテナ名 `mysql` を指定します）。

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

4. Sail を起動する

```bash
./vendor/bin/sail up -d
```

> Apple Silicon（M1/M2/M3）で `no matching manifest for linux/arm64/v8` エラーが出る場合は、`compose.yaml` の mysql サービスに `platform: 'linux/amd64'` を追加してください。

5. アプリケーションキーを生成する

```bash
./vendor/bin/sail artisan key:generate
```

6. フロントエンドの依存をインストールする

```bash
./vendor/bin/sail npm install
```

7. マイグレーションと初期データ投入を実行する

```bash
./vendor/bin/sail artisan migrate --seed
```

8. Vite 開発サーバーを起動する

```bash
./vendor/bin/sail npm run dev
```

以上で http://localhost にアクセスするとアプリが表示されます。

## 開発環境URL

- アプリケーション: http://localhost
- phpMyAdmin: http://localhost:8080

## APIエンドポイント一覧

すべて認証不要の公開 API です。ベースパスは `/api/v1` です。

| メソッド | パス | 概要 |
| --- | --- | --- |
| GET | `/api/v1/books` | 書籍一覧を取得（検索・絞り込み・ページネーション対応） |
| GET | `/api/v1/books/{id}` | 書籍詳細を取得 |
| POST | `/api/v1/books` | 書籍を登録 |
| PUT | `/api/v1/books/{id}` | 書籍を更新 |
| DELETE | `/api/v1/books/{id}` | 書籍を削除 |

### 一覧取得のクエリパラメータ

| パラメータ | 説明 |
| --- | --- |
| `keyword` | タイトル・著者の部分一致検索 |
| `genre_id` | ジャンルによる絞り込み |
| `per_page` | 1ページあたりの件数（デフォルト 20、最大 100） |
| `page` | ページ番号 |

## テスト

```bash
./vendor/bin/sail artisan test
```

カバレッジ付きで実行する場合:

```bash
./vendor/bin/sail artisan test --coverage
```

## 作成者

ももか