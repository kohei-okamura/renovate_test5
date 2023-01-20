/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/* eslint-disable camelcase */
/**
 * 郵便番号情報.
 *
 * 注：https://github.com/kmdsbng/zipcode_jp のデータをそのまま使うため snake_case を用いる.
 */
export type Postcode = Readonly<{
  /** 都道府県 JIS コード */
  prefecture_jis_code: string

  /** 市区町村 JIS コード */
  city_jis_code: string

  /** 郵便番号 */
  zip_code: string

  /** 都道府県（カナ） */
  prefecture_name_kana: string

  /** 市区町村（カナ） */
  city_name_kana: string

  /** 町名（カナ） */
  town_name_kana: string

  /** 都道府県 */
  prefecture_name: string

  /** 市区町村 */
  city_name: string

  /** 町名 */
  town_name: string
}>
