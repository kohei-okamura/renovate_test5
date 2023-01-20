/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsCalcCycle } from '@zinger/enums/lib/ltcs-calc-cycle'
import { LtcsCalcType } from '@zinger/enums/lib/ltcs-calc-type'

/**
 * 介護保険サービス：算定単位数.
 */
export type LtcsCalcScore = {
  /** 単位値 */
  value: number

  /** 単位値区分 */
  calcType: LtcsCalcType

  /** 算定単位 */
  calcCycle: LtcsCalcCycle
}
