/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { DateLike } from '~/models/date'
import { Range } from '~/models/range'
import { UserId } from '~/models/user'

/**
 * 自治体助成情報 ID.
 */
export type UserDwsSubsidyId = number

/**
 * 利用者：自治体助成情報.
 */
export type UserDwsSubsidy = Readonly<{
  /** 自治体助成情報 ID */
  id: UserDwsSubsidyId

  /** 利用者 ID */
  userId: UserId

  /** 適用期間 */
  period: Range<DateLike>

  /** 助成自治体名 */
  cityName: string

  /** 助成自治体番号 */
  cityCode: string

  /** 給付方式 */
  subsidyType: UserDwsSubsidyType

  /** 基準値種別 */
  factor: UserDwsSubsidyFactor

  /** 給付率 */
  benefitRate: number

  /** 本人負担率 */
  copayRate: number

  /** 端数処理区分 */
  rounding: Rounding

  /** 給付額 */
  benefitAmount: number

  /** 本人負担額 */
  copayAmount: number

  /** 備考 */
  note: string

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
