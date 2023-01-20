/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  none: 0,
  hokkaido: 1,
  aomori: 2,
  iwate: 3,
  miyagi: 4,
  akita: 5,
  yamagata: 6,
  fukushima: 7,
  ibaraki: 8,
  tochigi: 9,
  gunma: 10,
  saitama: 11,
  chiba: 12,
  tokyo: 13,
  kanagawa: 14,
  niigata: 15,
  toyama: 16,
  ishikawa: 17,
  fukui: 18,
  yamanashi: 19,
  nagano: 20,
  gifu: 21,
  shizuoka: 22,
  aichi: 23,
  mie: 24,
  shiga: 25,
  kyoto: 26,
  osaka: 27,
  hyogo: 28,
  nara: 29,
  wakayama: 30,
  tottori: 31,
  shimane: 32,
  okayama: 33,
  hiroshima: 34,
  yamaguchi: 35,
  tokushima: 36,
  kagawa: 37,
  ehime: 38,
  kochi: 39,
  fukuoka: 40,
  saga: 41,
  nagasaki: 42,
  kumamoto: 43,
  oita: 44,
  miyazaki: 45,
  kagoshima: 46,
  okinawa: 47
} as const

/**
 * 都道府県.
 */
export type Prefecture = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Prefecture = createEnumerable($$, [
  [$$.none, '未設定'],
  [$$.hokkaido, '北海道'],
  [$$.aomori, '青森県'],
  [$$.iwate, '岩手県'],
  [$$.miyagi, '宮城県'],
  [$$.akita, '秋田県'],
  [$$.yamagata, '山形県'],
  [$$.fukushima, '福島県'],
  [$$.ibaraki, '茨城県'],
  [$$.tochigi, '栃木県'],
  [$$.gunma, '群馬県'],
  [$$.saitama, '埼玉県'],
  [$$.chiba, '千葉県'],
  [$$.tokyo, '東京都'],
  [$$.kanagawa, '神奈川県'],
  [$$.niigata, '新潟県'],
  [$$.toyama, '富山県'],
  [$$.ishikawa, '石川県'],
  [$$.fukui, '福井県'],
  [$$.yamanashi, '山梨県'],
  [$$.nagano, '長野県'],
  [$$.gifu, '岐阜県'],
  [$$.shizuoka, '静岡県'],
  [$$.aichi, '愛知県'],
  [$$.mie, '三重県'],
  [$$.shiga, '滋賀県'],
  [$$.kyoto, '京都府'],
  [$$.osaka, '大阪府'],
  [$$.hyogo, '兵庫県'],
  [$$.nara, '奈良県'],
  [$$.wakayama, '和歌山県'],
  [$$.tottori, '鳥取県'],
  [$$.shimane, '島根県'],
  [$$.okayama, '岡山県'],
  [$$.hiroshima, '広島県'],
  [$$.yamaguchi, '山口県'],
  [$$.tokushima, '徳島県'],
  [$$.kagawa, '香川県'],
  [$$.ehime, '愛媛県'],
  [$$.kochi, '高知県'],
  [$$.fukuoka, '福岡県'],
  [$$.saga, '佐賀県'],
  [$$.nagasaki, '長崎県'],
  [$$.kumamoto, '熊本県'],
  [$$.oita, '大分県'],
  [$$.miyazaki, '宮崎県'],
  [$$.kagoshima, '鹿児島県'],
  [$$.okinawa, '沖縄県']
])

export const resolvePrefecture = Prefecture.resolve
