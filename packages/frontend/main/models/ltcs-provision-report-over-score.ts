/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介護保険サービス：予実：超過単位.
 */
export type LtcsProvisionReportOverScore = Readonly<{
  /** 区分支給限度基準を超える単位数 */
  maxBenefitExcessScore: number

  /** 種類支給限度基準を超える単位数 */
  maxBenefitQuotaExcessScore: number
}>
