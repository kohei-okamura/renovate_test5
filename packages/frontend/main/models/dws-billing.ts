/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { DateLike } from '~/models/date'
import { DwsBillingFile } from '~/models/dws-billing-file'
import { DwsBillingOffice } from '~/models/dws-billing-office'

/**
 * 請求 ID.
 */
export type DwsBillingId = number

/**
 * 障害福祉サービス：請求.
 */
export type DwsBilling = Readonly<{
  /** 請求 ID */
  id: DwsBillingId

  /** 事業所 */
  office: DwsBillingOffice

  /** 処理対象年月 */
  transactedIn: string

  /** ファイル */
  files: DwsBillingFile[]

  /** 状態 */
  status: DwsBillingStatus

  /** 確定日時 */
  fixedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
