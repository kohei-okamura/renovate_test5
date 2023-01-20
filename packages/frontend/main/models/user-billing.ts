/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { WithdrawalResultCode } from '@zinger/enums/lib/withdrawal-result-code'
import { DateLike } from '~/models/date'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'
import { UserBillingDwsItem } from '~/models/user-billing-dws-item'
import { UserBillingLtcsItem } from '~/models/user-billing-ltcs-item'
import { UserBillingOffice } from '~/models/user-billing-office'
import { UserBillingOtherItem } from '~/models/user-billing-other-item'
import { UserBillingUser } from '~/models/user-billing-user'

/**
 * 利用者請求 ID.
 */
export type UserBillingId = number

/**
 * 利用者請求.
 */
export type UserBilling = Readonly<{
  /** 利用者請求 ID */
  id: UserBillingId

  /** 利用者 ID */
  userId: UserId

  /** 事業所 ID */
  officeId: OfficeId

  /** 利用者 */
  user: UserBillingUser

  /** 事業所 */
  office: UserBillingOffice

  /** 障害福祉サービス明細 */
  dwsItem: UserBillingDwsItem | undefined

  /** 介護保険サービス明細 */
  ltcsItem: UserBillingLtcsItem | undefined

  /** その他サービス明細 */
  otherItems: UserBillingOtherItem[]

  /** 請求結果 */
  result: UserBillingResult

  /** 合計金額 */
  totalAmount: number

  /** 繰越金額 */
  carriedOverAmount: number

  /** 振替結果コード */
  withdrawalResultCode: WithdrawalResultCode | undefined

  /** サービス提供年月 */
  providedIn: DateLike

  /** 発行日 */
  issuedOn: DateLike | undefined

  /** 入金日時 */
  depositedAt: DateLike | undefined

  /** 処理日時 */
  transactedAt: DateLike | undefined

  /** 口座振替日 */
  deductedOn: DateLike | undefined

  /** お支払期限日 */
  dueDate: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
