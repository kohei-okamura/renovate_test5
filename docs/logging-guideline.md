# バックエンドログ出力ガイドライン

## ログを出力する方法
TBD

## ログを出力するタイミングと内容
下記のタイミングでログを出力する。

1. スタッフがログインに成功した直後
2. スタッフがログアウトした直後
3. 各種ユースケースにおいて、リポジトリにエンティティを登録した直後
4. 各種ユースケースにおいて、リポジトリのエンティティを更新した直後
5. 各種ユースケースにおいて、リポジトリのエンティティを削除した直後
6. 各種ゲートウェイにおいて、外部 API との通信を行った直後
7. エラーが発生した時

以下に、各タイミングにおけるログ出力内容を示す。
なお「追加データ」については、ログ出力箇所に応じて適宜必要そうなパラメータを追加してもよい。

### 1. スタッフがログインに成功した直後
| 項目       | 値                                         |
|:---------- |:------------------------------------------ |
| ログレベル | INFO                                       |
| メッセージ | スタッフがログインしました                 |
| 追加データ | `{ staffId: (ログインしたスタッフの ID) }` |

### 2. スタッフがログアウトした直後
| 項目       | 値                                           |
|:---------- |:-------------------------------------------- |
| ログレベル | INFO                                         |
| メッセージ | スタッフがログアウトしました                 |
| 追加データ | `{ staffId: (ログアウトしたスタッフの ID) }` |

### 3. 各種ユースケースにおいて、リポジトリにエンティティを登録した直後
| 項目         | 値                                                                                                   |
|:------------ |:---------------------------------------------------------------------------------------------------- |
| ログレベル   | INFO                                                                                                 |
| メッセージ   | （エンティティ名）が登録されました                                                                   |
| メッセージ例 | 事業所が登録されました                                                                               |
| 追加データ   | `{ id: (登録されたエンティティの ID), organizationId: (事業者ID), staffId: (操作したスタッフのID) }` |

### 4. 各種ユースケースにおいて、リポジトリのエンティティを更新した直後
| 項目         | 値                                                                                                   |
|:------------ |:---------------------------------------------------------------------------------------------------- |
| ログレベル   | INFO                                                                                                 |
| メッセージ   | （エンティティ名）が更新されました                                                                   |
| メッセージ例 | 障害福祉サービス受給者証が更新されました                                                             |
| 追加データ   | `{ id: (更新されたエンティティの ID), organizationId: (事業者ID), staffId: (操作したスタッフのID) }` |

### 5. 各種ユースケースにおいて、リポジトリのエンティティを削除した直後
| 項目         | 値                                                                                                   |
|:------------ |:---------------------------------------------------------------------------------------------------- |
| ログレベル   | INFO                                                                                                 |
| メッセージ   | （エンティティ名）が削除されました                                                                   |
| メッセージ例 | 利用者が削除されました                                                                               |
| 追加データ   | `{ id: (削除されたエンティティの ID), organizationId: (事業者ID), staffId: (操作したスタッフのID) }` |

### 6. 各種ゲートウェイにおいて、外部 API との通信を行った直後
| 項目         | 値                                                                                                                    |
|:------------ |:--------------------------------------------------------------------------------------------------------------------- |
| ログレベル   | INFO                                                                                                                  |
| メッセージ   | （外部API名）との通信に成功しました                                                                                   |
| メッセージ例 | Google Geocoding API との通信に成功しました                                                                           |
| 追加データ   | `{ url: (通信先URL), method: (通信メソッド e.g. POST), organizationId: (事業者ID), staffId: (操作したスタッフのID) }` |

### 7. 各種コマンド実行直後
| 項目         | 値                                                                            |
|:------------ |:----------------------------------------------------------------------------- |
| ログレベル   | INFO                                                                          |
| メッセージ   | (コマンド名）を実行します                                                     |
| メッセージ例 | 障害福祉サービス：居宅介護：サービスコード辞書インポートコマンド を実行します |
| 追加データ   | `{ command: (コマンド物理名), arguments: (引数) options: (オプション)`        |

### 8. 各種コマンド実行完了直前
| 項目         | 値                                                                                       |
|:------------ |:---------------------------------------------------------------------------------------- |
| ログレベル   | INFO                                                                                     |
| メッセージ   | (コマンド名）を実行しました                                                              |
| メッセージ例 | 障害福祉サービス：居宅介護：サービスコード辞書インポートコマンド を実行しました          |
| 追加データ   | `{ command: (コマンド物理名), arguments: (引数) options: (オプション) count: (処理件数)` |

### 9. エラーが発生した時
* エラーハンドラに適切に実装する。
* エラーの深刻度によって適切なログレベルを用いること。

### 10. その他、上記に当てはらないログ
| 項目         | 値                         |
|:------------ |:-------------------------- |
| ログレベル   | INFO                       |
| メッセージ   | (状況によって都度考える)   |
| メッセージ例 | 住所が特定できませんでした |
| 追加データ   | (状況によって都度考える)   |
