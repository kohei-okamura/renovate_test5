/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'

/**
 * 利用者請求：銀行口座.
 */
export type UserBillingBankAccount = Readonly<{
  /** 銀行名 */
  bankName: string

  /** 銀行コード */
  bankCode: string

  /** 支店名 */
  bankBranchName: string

  /** 支店コード */
  bankBranchCode: string

  /** 種別 */
  bankAccountType: BankAccountType

  /** 口座番号 */
  bankAccountNumber: string

  /** 名義 */
  bankAccountHolder: string
}>
