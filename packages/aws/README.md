# zinger-ops

> Next-generation ESL system infrastructure

## 事前準備

### アクセスキーを準備する
zinger-ops の各コマンドの実行には各自の IAM ユーザーを利用してください。

アクセスキーおよびシークレットキーを新たに作成する場合は AWS にサインインし、
[セキュリティ認証情報](https://console.aws.amazon.com/iam/home#/security_credentials)の画面で設定してください。

### 多要素認証（MFA）を設定する
[セキュリティ認証情報](https://console.aws.amazon.com/iam/home#/security_credentials)にて多要素認証（MFA）の設定をしてください。

既に設定してある場合、追加する必要はありません。

### アクセスキーを設定する
`~/.aws/credentials` を開き、下記の通り設定してください。

```
[eustylelab]
aws_access_key_id = （作成したアクセスキー）
aws_secret_access_key = （上記アクセスキーに対応するシークレットキー）
```

### プロファイルを設定する
`~/.aws/config` を開き、下記の通り設定してください。

多要素認証シリアルコードおよび IAM ロール ARN についてはインフラ担当者までお問い合わせください。

```
[profile eustylelab]
region = ap-northeast-1
output = json

[profile zinger]
mfa_serial = （多要素認証シリアルコード）
region = ap-northeast-1
role_arn = （IAM ロール ARN）
source_profile = eustylelab

[profile zinger-staging]
mfa_serial = （多要素認証シリアルコード）
region = ap-northeast-1
role_arn = （IAM ロール ARN）
source_profile = eustylelab

[profile zinger-sandbox]
mfa_serial = （多要素認証シリアルコード）
region = ap-northeast-1
role_arn = （IAM ロール ARN）
source_profile = eustylelab
```

## 環境構築手順
### 1. SSM に値を登録する
```bash
# 本番
yarn aws:build && yarn aws:ssm:register:prod

# ステージング
yarn aws:build && yarn aws:ssm:register:staging

# サンドボックス
yarn aws:build && yarn aws:ssm:register:sandbox
```

### 2. Route 53 ホストゾーンを作成する
```bash
# 本番
yarn aws:route53:hosted-zone:create -p zinger -H careid.jp

# ステージング
yarn aws:route53:hosted-zone:create -p zinger-staging -H staging.careid.net

# サンドボックス
yarn aws:route53:hosted-zone:create -p zinger-sandbox -H sandbox.careid.net
```

プロンプトへ表示されるネームサーバーの値を、各レジストラのネームサーバーへ反映してください。

```json
{
  "DelegationSet": {
    "NameServers": [
      "（ネームサーバ１）",
      "（ネームサーバ２）",
      "（ネームサーバ３）",
      "（ネームサーバ４）"
    ]
  }
}
```

### 3. CDK を用いて AWS リソースを作成する
```bash
# 本番
yarn aws:cdk:deploy

# ステージング
yarn aws:cdk:staging:deploy

# サンドボックス
yarn aws:cdk:sandbox:deploy
```

### 4. ECR にログインする
```bash
# 本番
host=$(yarn --silent aws:get-ecr-host -p zinger | tail -n 3 | jq -r .host)
aws ecr get-login-password --profile zinger | docker login --username AWS --password-stdin "${host}"

# ステージング
host=$(yarn --silent aws:get-ecr-host -p zinger-staging | tail -n 3 | jq -r .host)
aws ecr get-login-password --profile zinger-staging | docker login --username AWS --password-stdin "${host}"

# サンドボックス
host=$(yarn --silent aws:get-ecr-host -p zinger-sandbox | tail -n 3 | jq -r .host)
aws ecr get-login-password --profile zinger-sandbox | docker login --username AWS --password-stdin "${host}"
```

### 5. MySQL ユーザー登録
ターミナルより MySQL へログインして設定してください。
手順は、1Passwordの[RDS(MySQL)へアプリ用のユーザを登録する手順](https://eustylelab.1password.com/vaults/7jinv6jc6kvrefmhqqt22kx6s4/allitems/5zjmb6vcnfkusq3ffsbgu7iz4a)を参照してください。

### 6. Redis の認証トークン設定
ターミナルより Bastionへログインして設定してください。
手順は、1Passwordの[Bastionより認証トークンでElastiCache(Redis)へアクセスする設定の手順](https://eustylelab.1password.com/vaults/7jinv6jc6kvrefmhqqt22kx6s4/allitems/b5qedehynpqbacxmv7e3327qwi)を参照してください。

### 7. ECR に Docker イメージを push / タスク定義を登録
```bash
# 本番
make prod

# ステージング
make staging

# サンドボックス
make sandbox
```

### 8. データベースマイグレーションを実行する
```bash
# 本番
yarn aws:ecs:run:migration -p zinger

# ステージング
yarn aws:ecs:run:migration -p zinger-staging

# サンドボックス
yarn aws:ecs:run:migration -p zinger-sandbox
```

### 9. ECS サービスを作成する
```bash
# 本番
yarn aws:ecs:create:service -p zinger

# ステージング
yarn aws:ecs:create:service -p zinger-staging

# サンドボックス
yarn aws:ecs:create:service -p zinger-sandbox
```

### 10. ECS queueサービスを作成する
```bash
# 本番
yarn aws:ecs:create:queue -p zinger

# ステージング
yarn aws:ecs:create:queue -p zinger-staging

# サンドボックス
yarn aws:ecs:create:queue -p zinger-sandbox
```

### 11. EventBridge にバッチ処理イベントを登録する
```bash
# 本番
yarn aws:events:register:rule -p zinger
yarn aws:events:register:target -p zinger

# ステージング
yarn aws:events:register:rule -p zinger-staging
yarn aws:events:register:target -p zinger-staging

# サンドボックス
yarn aws:events:register:rule -p zinger-sandbox
yarn aws:events:register:target -p zinger-sandbox
```

### 12. Redashのプロビジョニングと設定（本番のみ）
以下にて、Redashをプロビジョニング
```bash
# 本番
yarn aws:cdk deploy RedashStack
```

プロビジョニング後、ターミナルより Redash へログインして設定シェルを実行してください。
手順は、1Passwordの「 [hellnav-setup-redash.sh](https://eustylelab.1password.com/vaults/jcoiaj5tibjlmzxswscbls6nva/allitems/4bxhmwijmh6doa4wnqrvl2nae4) 」を参照してください。

※ Redashの構成・仕様は、GROWIの「 [careidのRedash](https://eustylelab-engineers.growi.cloud/6221cb93fed4f6dc0464c720) 」に纏めています。

## デプロイ手順
### 1. ECR にログインする（初回のみ）
```bash
# 本番
host=$(yarn --silent aws:get-ecr-host -p zinger | tail -n 3 | jq -r .host)
aws ecr get-login-password --profile zinger | docker login --username AWS --password-stdin "${host}"

# ステージング
host=$(yarn --silent aws:get-ecr-host -p zinger-staging | tail -n 3 | jq -r .host)
aws ecr get-login-password --profile zinger-staging | docker login --username AWS --password-stdin "${host}"

# サンドボックス
host=$(yarn --silent aws:get-ecr-host -p zinger-sandbox | tail -n 3 | jq -r .host)
aws ecr get-login-password --profile zinger-sandbox | docker login --username AWS --password-stdin "${host}"
```

### 2. ECR に Docker イメージを push / タスク定義を登録
```bash
# 本番
make prod

# ステージング
make staging

# サンドボックス
make sandbox
```

### 3. データベースマイグレーションを実行する
```bash
# 本番
yarn aws:ecs:run:migration -p zinger

# ステージング
yarn aws:ecs:run:migration -p zinger-staging

# サンドボックス
yarn aws:ecs:run:migration -p zinger-sandbox
```

### 4. ECS サービスを更新する
```bash
# 本番
yarn aws:ecs:update:service -p zinger

# ステージング
yarn aws:ecs:update:service -p zinger-staging

# サンドボックス
yarn aws:ecs:update:service -p zinger-sandbox
```

### 5. ECS queueサービスを作成する
```bash
# 本番
yarn aws:ecs:update:queue -p zinger

# ステージング
yarn aws:ecs:update:queue -p zinger-staging

# サンドボックス
yarn aws:ecs:update:queue -p zinger-sandbox
```

### 6. EventBridge にバッチ処理イベントを登録する
```bash
# 本番
yarn aws:events:register:rule -p zinger
yarn aws:events:register:target -p zinger

# ステージング
yarn aws:events:register:rule -p zinger-staging
yarn aws:events:register:target -p zinger-staging

# サンドボックス
yarn aws:events:register:rule -p zinger-sandbox
yarn aws:events:register:target -p zinger-sandbox
```

## Commands
### ビルド
```bash
yarn aws:build
```

### 開発用監視モード
```bash
yarn aws:build -w
```

### SSM（AWS Systems Manager）パラメータストアに値を登録する
```bash
# 本番
yarn aws:ssm:register:prod

# ステージング
yarn aws:ssm:register:staging

# サンドボックス
yarn aws:ssm:register:sandbox
```

### SSM（AWS Systems Manager）パラメータストアから値を削除する
```bash
# 本番
yarn aws:ssm:delete:prod

# ステージング
yarn aws:ssm:delete:staging

# サンドボックス
yarn aws:ssm:delete:sandbox
```

### IAM ユーザー・グループと開発環境で使用するアクセスキー（ポリシー：S3アクセス可）を作成する。
```bash
# 本番
yarn aws:iam:user:create -p zinger

# ステージング
yarn aws:iam:user:create -p zinger-staging

# サンドボックス
yarn aws:iam:user:create -p zinger-sandbox
```

プロンプトへ表示されるアクセスキーの値を開発環境へ反映させてください。
```bash
"AccessKeyId": "（AccessKeyIdの値）"
"SecretAccessKey": "（SecretAccessKeyの値）"
```

### IAM ユーザー・グループと開発環境で使用するアクセスキー（ポリシー：S3アクセス可）を削除する。
```bash
# 本番
yarn aws:iam:user:delete -p zinger

# ステージング
yarn aws:iam:user:delete -p zinger-staging

# サンドボックス
yarn aws:iam:user:delete -p zinger-sandbox
```

### Route 53 ホストゾーンを作成する
```bash
# 本番
yarn aws:route53:hosted-zone:create -p zinger --hostname careid.jp

# ステージング
yarn aws:route53:hosted-zone:create -p zinger-staging --hostname staging.careid.net

# サンドボックス
yarn aws:route53:hosted-zone:create -p zinger-sandbox --hostname sandbox.careid.net
```

### Route 53 ホストゾーンを削除する
```bash
# 本番
yarn aws:route53:hosted-zone:delete -p zinger --hostname careid.jp

# ステージング
yarn aws:route53:hosted-zone:delete -p zinger-staging --hostname staging.careid.net

# サンドボックス
yarn aws:route53:hosted-zone:delete -p zinger-sandbox --hostname sandbox.careid.net
```

### ECS タスク定義を登録する
```bash
# 本番
yarn aws:ecs:register -p zinger --tag [タグ]

# ステージング
yarn aws:ecs:register -p zinger-staging --tag [タグ]

# サンドボックス
yarn aws:ecs:register -p zinger-sandbox --tag [タグ]
```

### データベースマイグレーションを実行する
```bash
# 本番
yarn aws:ecs:run:migration -p zinger

# ステージング
yarn aws:ecs:run:migration -p zinger-staging

# サンドボックス
yarn aws:ecs:run:migration -p zinger-sandbox
```

### ECR リポジトリを削除する
```bash
# 本番
yarn aws:ecr:repository:delete -p zinger

# ステージング
yarn aws:ecr:repository:delete -p zinger-staging

# サンドボックス
yarn aws:ecr:repository:delete -p zinger-sandbox
```

### ECS サービスを作成する
```bash
# 本番
yarn aws:ecs:create:service -p zinger

# ステージング
yarn aws:ecs:create:service -p zinger-staging

# サンドボックス
yarn aws:ecs:create:service -p zinger-sandbox
```

### ECS サービスを更新する
```bash
# 本番
yarn aws:ecs:update:service -p zinger

# ステージング
yarn aws:ecs:update:service -p zinger-staging

# サンドボックス
yarn aws:ecs:update:service -p zinger-sandbox
```

### ECS サービスを削除する
```bash
# 本番
yarn aws:ecs:delete:service -p zinger

# ステージング
yarn aws:ecs:delete:service -p zinger-staging

# サンドボックス
yarn aws:ecs:delete:service -p zinger-sandbox
```

※ コマンドが終了するのに5分ほど時間を要する場合があります。

### ECS Queueサービスを作成する
```bash
# 本番
yarn aws:ecs:create:queue -p zinger

# ステージング
yarn aws:ecs:create:queue -p zinger-staging

# サンドボックス
yarn aws:ecs:create:queue -p zinger-sandbox
```

### ECS Queueサービスを更新する
```bash
# 本番
yarn aws:ecs:update:queue -p zinger

# ステージング
yarn aws:ecs:update:queue -p zinger-staging

# サンドボックス
yarn aws:ecs:update:queue -p zinger-sandbox
```

### ECS Queueサービスを削除する
```bash
# 本番
yarn aws:ecs:delete:queue -p zinger

# ステージング
yarn aws:ecs:delete:queue -p zinger-staging

# サンドボックス
yarn aws:ecs:delete:queue -p zinger-sandbox
```

※ コマンドが終了するのに5分ほど時間を要する場合があります。

### EventBridge にバッチ処理イベントの実行ルールを登録する
```bash
# 本番
yarn aws:events:register:rule -p zinger

# ステージング
yarn aws:events:register:rule -p zinger-staging

# サンドボックス
yarn aws:events:register:rule -p zinger-sandbox
```

### EventBridge にバッチ処理イベントのターゲットを登録する
```bash
# 本番
yarn aws:events:register:target -p zinger

# ステージング
yarn aws:events:register:target -p zinger-staging

# サンドボックス
yarn aws:events:register:target -p zinger-sandbox
```

### EventBridge にバッチ処理イベントの実行ルールとターゲットを削除する
```bash
# 本番
yarn aws:events:delete -p zinger

# ステージング
yarn aws:events:delete -p zinger-staging

# サンドボックス
yarn aws:events:delete -p zinger-sandbox
```

### RDS インスタンスを削除する
```bash
# 本番
yarn aws:rds:delete -p zinger

# ステージング
yarn aws:rds:delete -p zinger-staging

# サンドボックス
yarn aws:rds:delete -p zinger-sandbox
```

### S3 バケットを削除する
```bash
# 本番
yarn aws:s3:delete -p zinger

# ステージング
yarn aws:s3:delete -p zinger-staging

# サンドボックス
yarn aws:s3:delete -p zinger-sandbox
```

### EC2 キーペアを削除する
```bash
# 本番
yarn aws:ec2:key-pair:delete -p zinger

# ステージング
yarn aws:ec2:key-pair:delete -p zinger-staging

# サンドボックス
yarn aws:ec2:key-pair:delete -p zinger-sandbox
```

### EC2 インスタンスを起動する
```bash
# 本番
yarn aws:ec2:bastion:start -p zinger

# ステージング
yarn aws:ec2:bastion:start -p zinger-staging

# サンドボックス
yarn aws:ec2:bastion:start -p zinger-sandbox
```

### EC2 インスタンスを停止する
```bash
# 本番
yarn aws:ec2:bastion:stop -p zinger

# ステージング
yarn aws:ec2:bastion:stop -p zinger-staging

# サンドボックス
yarn aws:ec2:bastion:stop -p zinger-sandbox
```

### 本番環境に向けて CDK コマンドを実行する
```bash
yarn aws:cdk [COMMAND] [STACKS...]
```

### ステージング環境に向けて CDK コマンドを実行する
```bash
yarn aws:cdk:staging [COMMAND] [STACKS...]
```

### サンドボックス環境に向けて CDK コマンドを実行する
```bash
yarn aws:cdk:sandbox [COMMAND] [STACKS...]
```

## AWSリソースのプロビジョニング手順
### 1. CDKのビルド
```bash
# 本番、共通
yarn aws:build
```
※「yarn aws:build -w」でwatchモードでビルド

### 2. CDK DIFFの差分確認
```bash
# 本番
yarn aws:cdk diff

# ステージング
yarn aws:cdk:staging diff

# サンドボックス
yarn aws:cdk:sandbox diff
```

### 3. CDK SYNTHESIZE のCloudFormationテンプレートの確認
```bash
# 本番
yarn aws:cdk ls
yarn aws:cdk synthesize [STACK] --exclusively

# ステージング
yarn aws:cdk:staging ls
yarn aws:cdk:staging synthesize [STACK] --exclusively

# サンドボックス
yarn aws:cdk:sandbox ls
yarn aws:cdk:sandbox synthesize [STACK] --exclusively
```

### 4. CDK DEPLOY のAWSリソースのプロビジョニング
```bash
# 本番
yarn aws:cdk:deploy

# ステージング
yarn aws:cdk:staging:deploy

# サンドボックス
yarn aws:cdk:sandbox:deploy
```

※ 検証での個別プロビジョニングの場合
```bash
# ステージング
yarn aws:cdk:staging deploy [STACK]

# サンドボックス
yarn aws:cdk:sandbox deploy [STACK]
```
