/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportAggregateCategory } from '@zinger/enums/lib/dws-billing-service-report-aggregate-category'
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'

/**
 * サービス提供実績記録票：合計.
 */
export type DwsBillingServiceReportAggregate =
  Record<DwsBillingServiceReportAggregateGroup, Record<DwsBillingServiceReportAggregateCategory, number>>
