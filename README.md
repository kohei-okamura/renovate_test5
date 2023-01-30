# zinger

[![Codeception](https://github.com/eustylelab/zinger/actions/workflows/codeception.yaml/badge.svg)](https://github.com/eustylelab/zinger/actions/workflows/codeception.yaml)
[![Jest for @zinger/aws](https://github.com/eustylelab/zinger/actions/workflows/jest-aws.yaml/badge.svg)](https://github.com/eustylelab/zinger/actions/workflows/jest-aws.yaml)
[![Jest for @zinger/frontend](https://github.com/eustylelab/zinger/actions/workflows/jest-frontend.yaml/badge.svg)](https://github.com/eustylelab/zinger/actions/workflows/jest-frontend.yaml)
[![codecov](https://codecov.io/gh/eustylelab/zinger/branch/main/graph/badge.svg?token=BULTTYUFEW)](https://codecov.io/gh/eustylelab/zinger)

> Next generation esl system

## セットアップ(hoge)

### バックエンド

#### 前提条件

`direnv` がインストール・設定されていること。

#### 事前準備

`direnv` を使い、環境変数 `APP_ENV` の値を `local` としてください。

#### redis の拡張モジュールをインストール

```bash
phpbrew ext install redis
```

#### sqlite の PDO モジュールをインストール

```bash
phpbrew ext install pdo_sqlite
```

#### 依存パッケージをインストール

```bash
composer install && php artisan ide-helper:generate
```

### フロントエンド

#### 依存パッケージをインストール

```bash
yarn install
```

## 開発用サーバー起動・停止方法

```bash
# 開発用 Docker コンテナを起動する
yarn start:docker

# 開発用 Docker コンテナを起動する（省略形）
yarn s:d

# Nuxt.js 開発サーバーを起動する
yarn start:nuxt

# Nuxt.js 開発サーバーを起動する（省略形）
yarn s:n

# 開発用 Docker コンテナと Nuxt.js 開発サーバーを一斉に起動する
yarn start

# 開発用 Docker コンテナと Nuxt.js 開発サーバーを一斉に起動する（省略形）
yarn s

# 開発用 Docker コンテナを停止する
yarn stop:docker

# ローカルモードで起動する
ENV=dev (yarn start:nuxt | yarn s:n | yarn start | yarn s)
```

## OpenAPI

```bash
# OpenAPI ドキュメントを生成する
yarn openapi:build

# OpenAPI ドキュメントを開く（HTTP サーバーが 8080 ポートで起動します）
yarn openapi:start

# OpenAPI ドキュメントを watch モードで開く
yarn openapi:watch
```

## 自動テスト関連

### バックエンド

```bash
# 全テストを実行する
composer test

# 単体テスト（ユニットテスト）を実行する
composer test:unit

# E2E テストを実行する
composer test:e2e

# 単体テストのスナップショットを更新する
composer test:update-snapshots

# いずれかのテストに失敗した時点でテストを終了する（最初のエラーのみを表示する）
composer test:unit -- -f

# 指定したフォルダ以下に含まれるテストのみを実行する（単体テストの場合）
composer test:unit -- server/tests/Unit/App/Http/Resolvers

# 指定したテストファイルのみを実行する（単体テストの場合）
composer test:unit -- server/tests/Unit/App/Http/Resolvers/StaffResolverImplTest.php

# 指定したフォルダ以下に含まれるテストのスナップショットを更新する
composer test:update-snapshots -- server/tests/Unit/App/Http/Resolvers

# 指定したテストのスナップショットを更新する
composer test:update-snapshots -- server/tests/Unit/App/Http/Resolvers/StaffResolverImplTest.php
```

### フロントエンド

```bash
# すべてのテストを実行する
yarn test

# すべてのテストを実行する（省略形）
yarn t

# ESLint のみを実行する
yarn lint

# ESLint による自動コード修正を行う
yarn lint:fix

# jest による単体テスト（ユニットテスト）のみを実行する
yarn test:unit

# jest による単体テスト（ユニットテスト）のみを実行する（省略形）
yarn t:u

# すべてのテストを実行し、カバレッジを取得する
yarn test:coverage

# すべてのテストを実行し、カバレッジを取得する（省略形）
yarn t:c

# 監視モードでテストを開始する
yarn test:watch

# 監視モードでテストを開始する
yarn t:w
```

## ビルド

```bash
# 列挙型 YAML 定義からソースコードを生成する
yarn enums:build

# フロントエンドの本番用ビルドを生成する
yarn frontend:build

# 開発環境用 Docker イメージを生成する
yarn docker:build:dev

# 本番環境用 Docker イメージを生成する
yarn docker:build:prod

# すべての Docker イメージを生成する
yarn docker:build:all
```

## インフラ（AWS）関連

[packages/aws/README.md](./packages/aws/README.md) を参照。

## デプロイ

### AWS (CDK)

TBD

### アプリケーション

TBD

### 辞書 API

```bash
# ステージング環境
yarn service-code-api:deploy:staging

# 本番環境
yarn service-code-api:deploy:prod
```

## クリーンアップ

```bash
# すべての自動生成ファイルを削除（クリーンアップ）する
yarn clean

# AWS 関連の自動生成ファイルを削除（クリーンアップ）する
yarn aws:clean

# Docker 関連の自動生成ファイルを削除（クリーンアップ）する
yarn docker:clean

# フロントエンド関連の自動生成ファイルを削除（クリーンアップ）する
yarn frontend:clean

# サービスコード辞書 API 関連の自動生成ファイルを削除（クリーンアップ）する
yarn service-code-api:clean
```

## REPL を起動する

### バックエンド

```bash
php artisan tinker
```

### フロントエンド

```bash
yarn repl
```

## その他

- [フロントエンド依存パッケージ更新手順](./docs/how-to-upgrade-frontend-dependencies.md)
- [バックエンドログ出力ガイドライン](./docs/logging-guideline.md)
