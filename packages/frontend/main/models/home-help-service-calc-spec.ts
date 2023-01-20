/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBaseIncreaseSupportAddition } from '@zinger/enums/lib/dws-base-increase-support-addition'
import { DwsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/dws-specified-treatment-improvement-addition'
import { DwsTreatmentImprovementAddition } from '@zinger/enums/lib/dws-treatment-improvement-addition'
import { HomeHelpServiceSpecifiedOfficeAddition } from '@zinger/enums/lib/home-help-service-specified-office-addition'
import { DateLike } from '~/models/date'
import { OfficeId } from '~/models/office'
import { Range } from '~/models/range'

/**
 * 算定情報 ID.
 */
export type HomeHelpServiceCalcSpecId = number

/**
 * 障害福祉サービス：居宅介護：算定情報.
 */
export type HomeHelpServiceCalcSpec = Readonly<{
  /** 算定情報 ID */
  id: HomeHelpServiceCalcSpecId

  /** 事業所 ID */
  officeId: OfficeId

  /** 適用期間 */
  period: Range<DateLike>

  /** 特定事業所加算 */
  specifiedOfficeAddition: HomeHelpServiceSpecifiedOfficeAddition

  /** 処遇改善加算 */
  treatmentImprovementAddition: DwsTreatmentImprovementAddition

  /** 特定処遇改善加算 */
  specifiedTreatmentImprovementAddition: DwsSpecifiedTreatmentImprovementAddition

  /** ベースアップ等支援加算 */
  baseIncreaseSupportAddition: DwsBaseIncreaseSupportAddition

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
