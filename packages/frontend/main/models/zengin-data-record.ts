/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { WithdrawalResultCode } from '@zinger/enums/lib/withdrawal-result-code'
import { ZenginDataRecordCode } from '@zinger/enums/lib/zengin-data-record-code'

/**
 * 全銀レコード：データレコード.
 */
export type ZenginDataRecord = Readonly<{
  /** 引落銀行番号 */
  bankCode: string

  /** 引落支店番号 */
  bankBranchCode: string

  /** 預金種目 */
  bankAccountType: BankAccountType

  /** 口座番号 */
  bankAccountNumber: string

  /** 預金者名 */
  bankAccountHolder: string

  /** 引落金額 */
  amount: number

  /** 新規コード */
  dataRecordCode: ZenginDataRecordCode

  /** 顧客番号 */
  clientNumber: string

  /** 振替結果コード */
  withdrawalResultCode: WithdrawalResultCode
}>
