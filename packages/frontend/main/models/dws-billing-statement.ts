/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatementCopayCoordinationStatus } from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { DateLike } from '~/models/date'
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingStatementAggregate } from '~/models/dws-billing-statement-aggregate'
import { DwsBillingStatementContract } from '~/models/dws-billing-statement-contract'
import { DwsBillingStatementCopayCoordination } from '~/models/dws-billing-statement-copay-coordination'
import { DwsBillingStatementItem } from '~/models/dws-billing-statement-item'
import { DwsBillingUser } from '~/models/dws-billing-user'

/**
 * 明細書 ID.
 */
export type DwsBillingStatementId = number

/**
 * 障害福祉サービス：明細書.
 */
export type DwsBillingStatement = Readonly<{
  /** 明細書 ID */
  id: DwsBillingStatementId

  /** 請求 ID */
  dwsBillingId: DwsBillingId

  /** 請求単位 ID */
  dwsBillingBundleId: DwsBillingBundleId

  /** 助成自治体番号 */
  subsidyCityCode: string

  /** 利用者（支給決定者） */
  user: DwsBillingUser

  /** 地域区分名 */
  dwsAreaGradeName: string

  /** 地域区分コード */
  dwsAreaGradeCode: string

  /** 利用者負担上限月額 */
  copayLimit: number

  /** 請求額集計欄：合計：給付単位数 */
  totalScore: number

  /** 請求額集計欄：合計：総費用額 */
  totalFee: number

  /** 請求額集計欄：合計：上限月額調整 */
  totalCappedCopay: number

  /** 請求額集計欄：合計：調整後利用者負担額 */
  totalAdjustedCopay: number | undefined

  /** 請求額集計欄：合計：上限管理後利用者負担額 */
  totalCoordinatedCopay: number | undefined

  /** 請求額集計欄：合計：決定利用者負担額 */
  totalCopay: number

  /** 請求額集計欄：合計：請求額：給付費 */
  totalBenefit: number

  /** 請求額集計欄：合計：自治体助成分請求額 */
  totalSubsidy: number | undefined

  /** 自社サービス提供有無 */
  isProvided: boolean

  /** 上限管理結果 */
  copayCoordination: DwsBillingStatementCopayCoordination | undefined

  /** 上限管理区分 */
  copayCoordinationStatus: DwsBillingStatementCopayCoordinationStatus

  /** 集計 */
  aggregates: DwsBillingStatementAggregate[]

  /** 契約 */
  contracts: DwsBillingStatementContract[]

  /** 明細 */
  items: DwsBillingStatementItem[]

  /** 状態 */
  status: DwsBillingStatus

  /** 確定日時 */
  fixedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
