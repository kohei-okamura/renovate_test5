/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsHomeHelpServiceBuildingType } from '@zinger/enums/lib/dws-home-help-service-building-type'
import { DwsHomeHelpServiceProviderType } from '@zinger/enums/lib/dws-home-help-service-provider-type'
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { Range } from './range'

/**
 * 障害福祉サービス：居宅介護：サービスコード辞書エントリ ID.
 */
export type DwsHomeHelpServiceDictionaryEntryId = number

/**
 * 障害福祉サービス：居宅介護：サービスコード辞書エントリ.
 */
export type DwsHomeHelpServiceDictionaryEntry = {
  /** サービスコード */
  serviceCode: string

  /** 名称 */
  name: string

  /** サービスコード区分 */
  category: DwsServiceCodeCategory

  /** 増分 */
  isExtra: boolean

  /** 2人（2人目の居宅介護従業者による場合） */
  isSecondary: boolean

  /** 提供者区分 */
  providerType: DwsHomeHelpServiceProviderType

  /** 初計（初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合） */
  isPlannedByNovice: boolean

  /** 障害居宅介護建物区分 */
  buildingType: DwsHomeHelpServiceBuildingType

  /** 単位数 */
  score: number

  /** 時間数（日中） */
  daytimeDuration: Range<number>

  /** 時間数（早朝） */
  morningDuration: Range<number>

  /** 時間数（夜間） */
  nightDuration: Range<number>

  /** 時間数（深夜1） */
  midnightDuration1: Range<number>

  /** 時間数（深夜2） */
  midnightDuration2: Range<number>
}
