/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { DateLike } from '~/models/date'

/**
 * 銀行口座 ID.
 */
export type BankAccountId = number

/**
 * 銀行口座.
 */
export type BankAccount = Readonly<{
  /** 銀行口座 ID */
  id: BankAccountId

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

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
