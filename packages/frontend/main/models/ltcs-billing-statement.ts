/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { DateLike } from '~/models/date'
import { LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundleId } from '~/models/ltcs-billing-bundle'
import { LtcsBillingStatementAggregate } from '~/models/ltcs-billing-statement-aggregate'
import { LtcsBillingStatementInsurance } from '~/models/ltcs-billing-statement-insurance'
import { LtcsBillingStatementItem } from '~/models/ltcs-billing-statement-item'
import { LtcsBillingStatementSubsidy } from '~/models/ltcs-billing-statement-subsidy'
import { LtcsBillingUser } from '~/models/ltcs-billing-user'
import { LtcsCarePlanAuthor } from '~/models/ltcs-care-plan-author'

/**
 * 明細書 ID.
 */
export type LtcsBillingStatementId = number

/**
 * 介護保険サービス：明細書.
 */
export type LtcsBillingStatement = Readonly<{
  /** 明細書 ID */
  id: LtcsBillingStatementId

  /** 請求 ID */
  billingId: LtcsBillingId

  /** 請求単位 ID */
  bundleId: LtcsBillingBundleId

  /** 保険者番号 */
  insurerNumber: string

  /** 保険者名 */
  insurerName: string

  /** 被保険者 */
  user: LtcsBillingUser

  /** 居宅サービス計画 */
  carePlanAuthor: LtcsCarePlanAuthor

  /** 開始年月日 */
  agreedOn: DateLike | undefined

  /** 中止年月日 */
  expiredOn: DateLike | undefined

  /** 中止理由 */
  expiredReason: LtcsExpiredReason

  /** 保険請求内容 */
  insurance: LtcsBillingStatementInsurance

  /** 公費請求内容 */
  subsidies: LtcsBillingStatementSubsidy[]

  /** 明細 */
  items: LtcsBillingStatementItem[]

  /** 集計 */
  aggregates: LtcsBillingStatementAggregate[]

  /** 状態 */
  status: LtcsBillingStatus

  /** 確定日時 */
  fixedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
