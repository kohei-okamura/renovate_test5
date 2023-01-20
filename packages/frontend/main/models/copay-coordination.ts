/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationType } from '@zinger/enums/lib/copay-coordination-type'
import { OfficeId } from '~/models/office'

/**
 * 障害福祉サービス受給者証：上限管理情報.
 */
export type CopayCoordination = Readonly<{
  /** 上限管理区分 */
  copayCoordinationType: CopayCoordinationType

  /** 事業所 ID */
  officeId: OfficeId | undefined
}>
