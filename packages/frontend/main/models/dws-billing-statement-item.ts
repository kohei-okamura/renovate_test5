/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'

/**
 * 障害福祉サービス：明細書：明細.
 */
export type DwsBillingStatementItem = Readonly<{
  /** サービスコード */
  serviceCode: string

  /** サービスコード区分 */
  serviceCodeCategory: DwsServiceCodeCategory

  /** 単位数 */
  unitScore: number

  /** 回数 */
  count: number

  /** サービス単位数 */
  totalScore: number
}>
