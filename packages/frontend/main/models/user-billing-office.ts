/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Addr } from '~/models/addr'

/**
 * 利用者請求：事業所.
 */
export type UserBillingOffice = Readonly<{
  /** 事業所名 */
  name: string

  /** 法人名 */
  corporationName: string

  /** 住所 */
  addr: Addr

  /** 電話番号 */
  tel: string
}>
