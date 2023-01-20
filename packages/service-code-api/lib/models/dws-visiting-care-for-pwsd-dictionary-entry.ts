/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { Range } from './range'

/**
 * 障害福祉サービス：重度訪問介護：サービスコード辞書エントリ ID.
 */
export type DwsVisitingCareForPwsdDictionaryEntryId = number

/**
 * 障害福祉サービス：重度訪問介護：サービスコード辞書エントリ.
 */
export type DwsVisitingCareForPwsdDictionaryEntry = {
  /** サービスコード */
  serviceCode: string

  /** 名称 */
  name: string

  /** サービスコード区分 */
  category: DwsServiceCodeCategory

  /** 2人（2人目の重度訪問介護従業者による場合） */
  isSecondary: boolean

  /** 同行（熟練従業者が同行して支援を行う場合） */
  isCoaching: boolean

  /** 入院（病院等に入院又は入所中に利用した場合） */
  isHospitalized: boolean

  /** 90日（90日以上利用減算） */
  isLongHospitalized: boolean

  /** 単位数 */
  score: number

  /** 時間帯 */
  timeframe: Timeframe

  /** 時間数 */
  duration: Range<number>

  /** 単位 */
  unit: number
}
