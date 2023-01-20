/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HomeVisitLongTermCareSpecifiedOfficeAddition } from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsCompositionType } from '@zinger/enums/lib/ltcs-composition-type'
import { LtcsNoteRequirement } from '@zinger/enums/lib/ltcs-note-requirement'
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { DateLike } from '~/models/date'
import { LtcsCalcExtraScore } from '~/models/ltcs-calc-extra-score'
import { LtcsCalcScore } from '~/models/ltcs-calc-score'
import { LtcsHomeVisitLongTermCareDictionaryId } from '~/models/ltcs-home-visit-long-term-care-dictionary'
import { Range } from '~/models/range'

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ ID.
 */
export type LtcsHomeVisitLongTermCareDictionaryEntryId = number

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ.
 */
export type LtcsHomeVisitLongTermCareDictionaryEntry = {
  /** 辞書エントリ ID */
  id: LtcsHomeVisitLongTermCareDictionaryEntryId

  /** 辞書 ID */
  dictionaryId: LtcsHomeVisitLongTermCareDictionaryId

  /** サービスコード */
  serviceCode: string

  /** 名称 */
  name: string

  /** サービスコード区分 */
  category: LtcsServiceCodeCategory

  /** 提供人数 */
  headcount: number

  /** 合成識別区分 */
  compositionType: LtcsCompositionType

  /** 特定事業所加算 */
  specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition

  /** 摘要欄記載要件 */
  noteRequirement: LtcsNoteRequirement

  /** 支給限度額対象 */
  isLimited: boolean

  /** 同一建物減算対象 */
  isBulkSubtractionTarget: boolean

  /** 共生型減算対象 */
  isSymbioticSubtractionTarget: boolean

  /** 算定単位数 */
  score: LtcsCalcScore

  /** きざみ単位数 */
  extraScore: LtcsCalcExtraScore

  /** 時間帯 */
  timeframe: Timeframe

  /** 合計時間数 */
  totalMinutes: Range<number>

  /** 身体時間数 */
  physicalMinutes: Range<number>

  /** 生活時間数 */
  houseworkMinutes: Range<number>

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}
