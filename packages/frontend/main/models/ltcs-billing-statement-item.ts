/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { LtcsBillingStatementItemSubsidy } from '~/models/ltcs-billing-statement-item-subsidy'

/**
 * 介護保険サービス：明細書：明細.
 */
export type LtcsBillingStatementItem = Readonly<{
  /** サービスコード */
  serviceCode: string

  /** サービスコード区分 */
  serviceCodeCategory: LtcsServiceCodeCategory

  /** 単位数 */
  unitScore: number

  /** 日数・回数 */
  count: number

  /** サービス単位数 */
  totalScore: number

  /** 公費 */
  subsidies: LtcsBillingStatementItemSubsidy[]

  /** 摘要 */
  note: string
}>
