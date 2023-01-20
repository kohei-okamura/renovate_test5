/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { DwsBillingId } from '~/models/dws-billing'

/**
 * 請求単位 ID.
 */
export type DwsBillingBundleId = number

/**
 * 障害福祉サービス請求単位.
 */
export type DwsBillingBundle = Readonly<{
  /** 請求単位 ID */
  id: DwsBillingBundleId

  /** 請求 ID */
  dwsBillingId: DwsBillingId

  /** サービス提供年月 */
  providedIn: string

  /** 市町村番号 */
  cityCode: string

  /** 市町村名 */
  cityName: string

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
