/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Addr } from '~/models/addr'
import { OfficeId } from '~/models/office'

/**
 * 介護保険サービス請求：事業所.
 */
export type LtcsBillingOffice = Readonly<{
  /** 事業所ID */
  officeId: OfficeId

  /** 事業所番号 */
  code: string

  /** 事業所名 */
  name: string

  /** 略称 */
  abbr: string

  /** 所在地 */
  addr: Addr

  /** 電話番号 */
  tel: string
}>
