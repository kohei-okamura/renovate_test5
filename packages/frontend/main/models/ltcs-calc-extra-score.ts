/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介護保険サービス：きざみ単位数.
 */
export type LtcsCalcExtraScore = {
  /** きざみ有無 */
  isAvailable: boolean

  /** きざみ基準時間数 */
  baseMinutes: number

  /** きざみ単位数 */
  unitScore: number

  /** きざみ時間量 */
  unitMinutes: number

  /** 特定事業所加算係数 */
  specifiedOfficeAdditionCoefficient: number

  /** 時間帯係数 */
  timeframeAdditionCoefficient: number
}
