/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { StructuredName } from '~/models/structured-name'
import { UserId } from '~/models/user'

/**
 * 障害福祉サービス：予実：概要.
 */
export type DwsProvisionReportDigest = Readonly<{
  /** 利用者 ID */
  userId: UserId

  /** 利用者氏名 */
  name: StructuredName

   /** 受給者証番号 */
  dwsNumber: string

   /** 利用者の状態 */
  isEnabled: boolean

   /** 予実の状態 */
  status: DwsProvisionReportStatus
}>
