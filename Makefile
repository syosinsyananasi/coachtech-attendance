# コンテナ起動
up:
	docker-compose up -d

# コンテナ停止
down:
	docker-compose down

# コンテナ再起動
restart:
	docker-compose restart

# コンテナビルド＋起動
build:
	docker-compose up -d --build

# PHPコンテナにログイン
shell:
	docker-compose exec php bash

# マイグレーション実行
migrate:
	docker-compose exec php php artisan migrate

# シーディング実行
seed:
	docker-compose exec php php artisan db:seed

# マイグレーションリフレッシュ＋シーディング
fresh:
	docker-compose exec php php artisan migrate:fresh --seed

# テスト用データベース作成
create-test-db:
	docker-compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS demo_test;"

# 全テスト実行
test:
	docker-compose exec php php artisan test

# 初回セットアップ（ビルド〜シーディングまで一括実行）
setup:
	docker-compose up -d --build
	docker-compose exec php composer install
	docker-compose exec php php artisan key:generate
	sleep 20
	docker-compose exec php php artisan migrate --seed
	@make create-test-db
