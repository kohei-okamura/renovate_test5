/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Config } from '@jest/types'

const config: Config.InitialOptions = {
  // ESLint の対象とするファイル拡張子の一覧
  // プロジェクト中に頻出する順番で指定する
  // see https://jestjs.io/ja/docs/configuration#modulefileextensions-arraystring
  moduleFileExtensions: ['ts', 'vue', 'js', 'd.ts'],

  // 通知を送るタイミング
  // see https://jestjs.io/ja/docs/configuration#notifymode-string
  notifyMode: 'always',

  // watch プラグイン
  // see https://jestjs.io/ja/docs/configuration#watchplugins-arraystring--string-object
  watchPlugins: [
    'jest-runner-eslint/watch-fix',
    'jest-watch-master',
    'jest-watch-select-projects',
    'jest-watch-suspend',
    'jest-watch-typeahead/filename',
    'jest-watch-typeahead/testname'
  ]
}
export default config
