/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import { StructuredName } from '~/models/structured-name'
import { UserId } from '~/models/user'

/**
 * 介護保険サービス：予実：概要.
 */
export type LtcsProvisionReportDigest = Readonly<{
  /** 利用者 ID */
  userId: UserId

  /** 利用者氏名 */
  name: StructuredName

   /** 被保険者番号 */
  insNumber: string

   /** 利用者の状態 */
  isEnabled: boolean

   /** 予実の状態 */
  status: LtcsProvisionReportStatus
}>
