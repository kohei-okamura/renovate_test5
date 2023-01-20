/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HomeVisitLongTermCareSpecifiedOfficeAddition } from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import { LtcsTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import { DateLike } from '~/models/date'
import { OfficeId } from '~/models/office'
import { Range } from '~/models/range'

/**
 * 算定情報 ID.
 */
export type HomeVisitLongTermCareCalcSpecId = number

/**
 * 介護保険サービス：訪問介護：算定情報.
 */
export type HomeVisitLongTermCareCalcSpec = Readonly<{
  /** 算定情報 ID */
  id: HomeVisitLongTermCareCalcSpecId

  /** 事業所 ID */
  officeId: OfficeId

  /** 適用期間 */
  period: Range<DateLike>

  /** 特定事業所加算 */
  specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition

  /** 処遇改善加算 */
  treatmentImprovementAddition: LtcsTreatmentImprovementAddition

  /** 特定処遇改善加算 */
  specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition

  /** 地域加算 */
  locationAddition: LtcsOfficeLocationAddition

  /** ベースアップ等支援加算 */
  baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
