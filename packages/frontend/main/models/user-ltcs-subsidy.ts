/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { DefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { DateLike } from '~/models/date'
import { Range } from '~/models/range'
import { UserId } from '~/models/user'

/**
 * 公費情報 ID.
 */
export type UserLtcsSubsidyId = number

/**
 * 利用者：公費情報.
 */
export type UserLtcsSubsidy = Readonly<{
  /** 公費情報 ID */
  id: UserLtcsSubsidyId

  /** 利用者 ID */
  userId: UserId

  /** 適用期間 */
  period: Range<DateLike>

  /** 公費制度（法別番号） */
  defrayerCategory: DefrayerCategory

  /** 負担者番号 */
  defrayerNumber: string

  /** 受給者番号 */
  recipientNumber: string

  /** 給付率 */
  benefitRate: number

  /** 本人負担額 */
  copay: number

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
