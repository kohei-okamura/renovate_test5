/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Prefecture } from '@zinger/enums/lib/prefecture'

/**
 * 住所.
 */
export type Addr = Readonly<{
  /** 郵便番号 */
  postcode: string

  /** 都道府県 */
  prefecture: Prefecture

  /** 市区町村 */
  city: string

  /** 町名・番地 */
  street: string

  /** 建物名など */
  apartment: string
}>
