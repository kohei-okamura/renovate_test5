/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Sex } from '@zinger/enums/lib/sex'
import { Addr } from '~/models/addr'
import { BankAccountId } from '~/models/bank-account'
import { Contact } from '~/models/contact'
import { DateLike } from '~/models/date'
import { Location } from '~/models/location'
import { StructuredName } from '~/models/structured-name'
import { UserBillingDestination } from '~/models/user-billing-destination'

/**
 * 利用者 ID.
 */
export type UserId = number

/**
 * 利用者.
 */
export type User = Readonly<{
  /** 利用者 ID */
  id: UserId

  /** 氏名 */
  name: StructuredName

  /** 性別 */
  sex: Sex

  /** 生年月日 */
  birthday: DateLike

  /** 住所 */
  addr: Addr

  /** 位置 */
  location: Location

  /** 連絡先電話番号 */
  contacts: Contact[]

  /** 銀行口座 ID */
  bankAccountId: BankAccountId

  /** 請求先情報 */
  billingDestination: UserBillingDestination

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
