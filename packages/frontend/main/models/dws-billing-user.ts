/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertificationId } from '~/models/dws-certification'
import { StructuredName } from '~/models/structured-name'
import { UserId } from '~/models/user'

/**
 * 障害福祉サービス請求：利用者.
 */
export type DwsBillingUser = Readonly<{
  /** 利用者 ID */
  userId: UserId

  /** 障害福祉サービス受給者証 ID */
  dwsCertificationId: DwsCertificationId

  /** 受給者番号 */
  dwsNumber: string

  /** 氏名 */
  name: StructuredName

  /** 児童氏名 */
  childName: StructuredName

  /** 利用者負担上限月額 */
  copayLimit: number
}>
