/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsUserLocationAddition } from '@zinger/enums/lib/ltcs-user-location-addition'
import { DateLike } from '~/models/date'
import { UserId } from '~/models/user'

/**
 * 介護保険サービス：利用者別算定情報 ID.
 */
export type UserLtcsCalcSpecId = number

/**
 * 介護保険サービス：利用者別算定情報.
 */
export type UserLtcsCalcSpec = Readonly<{
  /** 利用者別算定情報 ID */
  id: UserLtcsCalcSpecId

  /** 利用者 ID */
  userId: UserId

  /** 適用日 */
  effectivatedOn: DateLike

  /** 地域加算 */
  locationAddition: LtcsUserLocationAddition

  /** 有効フラグ */
  isEnabled: boolean

  /** バージョン */
  version: number

  /** 作成日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
