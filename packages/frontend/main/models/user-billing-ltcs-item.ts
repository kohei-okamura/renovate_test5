/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ConsumptionTaxRate } from '@zinger/enums/lib/consumption-tax-rate'

/**
 * 介護保険明細書 ID.
 */
export type LtcsBillingStatementId = number

/**
 * 利用者請求：介護保険サービス明細.
 */
export type UserBillingLtcsItem = Readonly<{
  /** 介護保険明細書 ID */
  ltcsStatementId: LtcsBillingStatementId

  /** 単位数 */
  score: number

  /** 単価 */
  unitCost: number

  /** 小計 */
  subtotalCost: number

  /** 消費税 */
  tax: ConsumptionTaxRate

  /** 医療費控除対象額 */
  medicalDeductionAmount: number

  /** 介護給付額 */
  benefitAmount: number

  /** 公費負担額 */
  subsidyAmount: number

  /** 金額 */
  totalAmount: number

  /** 自己負担額（税抜） */
  copayWithoutTax: number

  /** 自己負担額（税込） */
  copayWithTax: number
}>
