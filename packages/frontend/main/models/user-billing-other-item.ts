/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ConsumptionTaxRate } from '@zinger/enums/lib/consumption-tax-rate'

/**
 * 利用者請求：その他サービス明細.
 */
export type UserBillingOtherItem = Readonly<{
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

  /** 金額 */
  totalAmount: number

  /** 自己負担額（税抜） */
  copayWithoutTax: number

  /** 自己負担額（税込） */
  copayWithTax: number
}>
