# COMPONENTS

## プレフィクス
すべてのコンポーネントにはプレフィクス `z-` をつけてください.

## ディレクトリ構成
下記の様なディレクトリ構成（分類）となっています.

```
main
├── components
│     ├── domain
│     ├── tools
│     ├── ui
│     └── util
├── composables
└── pages
```

## コンポーネントの分類について
### domain / ドメイン固有コンポーネント
特定のドメインモデル（ドメイン知識）に密結合となるコンポーネントを
「ドメイン固有コンポーネント」と呼び, `domain` ディレクトリ以下に配置します.

`domain` ディレクトリ以下にはさらに領域ごとのサブディレクトリがあるので
適切なディレクトリを選択（または作成）してください.

### ui / UI コンポーネント
特定のドメインモデル（ドメイン知識）に依存しない, 純粋な UI を実現するコンポーネントを
「UI コンポーネント」と呼び, `ui` ディレクトリ以下に配置します.

`ui` ディレクトリ以下にサブディレクトリはありません.

### util / ユーティリティコンポーネント
特定のドメインモデル（ドメイン知識）に依存せず, さらに UI を提供しないコンポーネントを
「ユーティリティコンポーネント」と呼び, `util` ディレクトリ以下に配置します.

`util` ディレクトリ以下にサブディレクトリはありません.

## その他
### tools / コンポーネント定義ツール関数群
コンポーネントそのものを定義する際に用いる関数を `tools` ディレクトリ以下に配置します.

コンポーネント内で使用する関数は `composables` ディレクトリに配置してください.
