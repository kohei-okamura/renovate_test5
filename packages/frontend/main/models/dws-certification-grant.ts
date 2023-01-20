/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { DateLike } from '~/models/date'

/**
 * 障害福祉サービス受給者証：支給量等.
 */
export type DwsCertificationGrant = Readonly<{
  /** サービス種別 */
  dwsCertificationServiceType: DwsCertificationServiceType

  /** 支給量等 */
  grantedAmount: string

  /** 認定の有効期間（開始） */
  activatedOn: DateLike

  /** 認定の有効期間（終了） */
  deactivatedOn: DateLike
}>
