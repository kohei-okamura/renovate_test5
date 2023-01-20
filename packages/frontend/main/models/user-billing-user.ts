/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Addr } from '~/models/addr'
import { Contact } from '~/models/contact'
import { StructuredName } from '~/models/structured-name'
import { UserBillingBankAccount } from '~/models/user-billing-bank-account'
import { UserBillingDestination } from '~/models/user-billing-destination'

/**
 * 利用者請求：利用者.
 */
export type UserBillingUser = Readonly<{
  /** 氏名 */
  name: StructuredName

  /** 住所 */
  addr: Addr

  /** 連絡先電話番号 */
  contacts: Contact[]

  /** 請求先情報 */
  billingDestination: UserBillingDestination

  /** 銀行口座 */
  bankAccount: UserBillingBankAccount
}>
