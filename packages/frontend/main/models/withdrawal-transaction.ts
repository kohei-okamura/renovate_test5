/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { WithdrawalTransactionItem } from '~/models/withdrawal-transaction-item'

/**
 * 口座振替データ ID.
 */
export type WithdrawalTransactionId = number

/**
 * 口座振替データ.
 */
export type WithdrawalTransaction = Readonly<{
  /** 口座振替データ ID */
  id: WithdrawalTransactionId

  /** 明細 */
  items: WithdrawalTransactionItem[]

  /** 口座振替日 */
  deductedOn: DateLike

  /** 最終ダウンロード日時 */
  downloadedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
