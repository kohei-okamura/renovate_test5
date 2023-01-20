/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { LtcsBillingId } from '~/models/ltcs-billing'

/**
 * 請求単位 ID.
 */
export type LtcsBillingBundleId = number

/**
 * 介護保険サービス：請求単位.
 */
export type LtcsBillingBundle = Readonly<{
  /** 請求単位 ID */
  id: LtcsBillingBundleId

  /** 請求 ID */
  billingId: LtcsBillingId

  /** サービス提供年月 */
  providedIn: string

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
