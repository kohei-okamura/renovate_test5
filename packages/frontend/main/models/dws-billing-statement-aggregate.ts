/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { DateLike } from '~/models/date'

/**
 * 障害福祉サービス：明細書：集計.
 */
export type DwsBillingStatementAggregate = Readonly<{
  /** サービス種類コード */
  serviceDivisionCode: DwsServiceDivisionCode

  /** サービス開始年月日 */
  startedOn: DateLike

  /** サービス終了年月日 */
  terminatedOn: DateLike

  /** サービス利用日数 */
  serviceDays: number

  /** 給付単位数 */
  subtotalScore: number

  /** 単位数単価 */
  unitCost: number

  /** 総費用額 */
  subtotalFee: number

  /** 1割相当額 */
  unmanagedCopay: number

  /** 利用者負担額 */
  managedCopay: number

  /** 上限月額調整 */
  cappedCopay: number

  /** 調整後利用者負担額 */
  adjustedCopay: number | undefined

  /** 上限額管理後利用者負担額 */
  coordinatedCopay: number | undefined

  /** 決定利用者負担額 */
  subtotalCopay: number

  /** 請求額：給付費 */
  subtotalBenefit: number

  /** 自治体助成分請求額 */
  subtotalSubsidy: number | undefined
}>
