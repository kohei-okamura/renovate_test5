/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ConsumptionTaxRate } from '@zinger/enums/lib/consumption-tax-rate'

/**
 * 障害福祉明細書 ID.
 */
export type DwsBillingStatementId = number

/**
 * 利用者請求：障害福祉サービス明細.
 */
export type UserBillingDwsItem = Readonly<{
  /** 障害福祉明細書 ID */
  dwsStatementId: DwsBillingStatementId

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

  /** 自治体助成額 */
  subsidyAmount: number

  /** 金額 */
  totalAmount: number

  /** 自己負担額（税抜） */
  copayWithoutTax: number

  /** 自己負担額（税込） */
  copayWithTax: number
}>
