/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsServiceDivisionCode } from '@zinger/enums/lib/ltcs-service-division-code'
import { LtcsBillingStatementAggregateInsurance } from '~/models/ltcs-billing-statement-aggregate-insurance'
import { LtcsBillingStatementAggregateSubsidy } from '~/models/ltcs-billing-statement-aggregate-subsidy'

/**
 * 介護保険サービス：明細書：集計：公費.
 */
export type LtcsBillingStatementAggregate = Readonly<{
  /** サービス種類コード */
  serviceDivisionCode: LtcsServiceDivisionCode

  /** サービス実日数 */
  serviceDays: number

  /** 計画単位数 */
  plannedScore: number

  /** 限度額管理対象単位数 */
  managedScore: number

  /** 限度額管理対象外単位数 */
  unmanagedScore: number

  /** 保険集計結果 */
  insurance: LtcsBillingStatementAggregateInsurance

  /** 公費集計結果 */
  subsidies: LtcsBillingStatementAggregateSubsidy[]
}>
