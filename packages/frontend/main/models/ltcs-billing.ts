/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { DateLike } from '~/models/date'
import { LtcsBillingFile } from '~/models/ltcs-billing-file'
import { LtcsBillingOffice } from '~/models/ltcs-billing-office'

/**
 * 請求 ID.
 */
export type LtcsBillingId = number

/**
 * 障害福祉サービス：請求.
 */
export type LtcsBilling = Readonly<{
  /** 請求 ID */
  id: LtcsBillingId

  /** 事業所 */
  office: LtcsBillingOffice

  /** 処理対象年月 */
  transactedIn: string

  /** ファイル */
  files: LtcsBillingFile[]

  /** 状態 */
  status: LtcsBillingStatus

  /** 確定日時 */
  fixedAt: DateLike | undefined

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
