/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'

/**
 * 介護保険被保険者証：種類支給限度基準額.
 */
export type LtcsInsCardMaxBenefitQuota = Readonly<{
  /** サービスの種類 */
  ltcsInsCardServiceType: LtcsInsCardServiceType

  /** 種類支給限度基準額 */
  maxBenefitQuota: number
}>
