/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsUserLocationAddition } from '@zinger/enums/lib/dws-user-location-addition'
import { DateLike } from '~/models/date'
import { UserId } from '~/models/user'

/**
 * 障害福祉サービス：利用者別算定情報 ID.
 */
export type UserDwsCalcSpecId = number

/**
 * 障害福祉サービス：利用者別算定情報.
 */
export type UserDwsCalcSpec = Readonly<{
  /** 利用者別算定情報 ID */
  id: UserDwsCalcSpecId

  /** 利用者 ID */
  userId: UserId

  /** 適用日 */
  effectivatedOn: DateLike

  /** 地域加算 */
  locationAddition: DwsUserLocationAddition

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 作成日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
