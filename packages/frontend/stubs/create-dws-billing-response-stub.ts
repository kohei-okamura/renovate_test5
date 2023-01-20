/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { createDwsBillingBundleStubsForBilling } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingCopayCoordinationStubs } from '~~/stubs/create-dws-billing-copay-coordination-stub'
import { createDwsBillingServiceReportStubsForBundle } from '~~/stubs/create-dws-billing-service-report-stub'
import { createDwsBillingStatementStubsForBundle } from '~~/stubs/create-dws-billing-statement-stub'
import { createDwsBillingStub } from '~~/stubs/create-dws-billing-stub'
import {
  DWS_BILLING_ID_MIN,
  DWS_BILLING_STATEMENT_STUB_COUNT_PER_BUNDLE
} from '~~/stubs/create-dws-billing-stub-settings'
import { createDwsBillingUserStubs } from '~~/stubs/create-dws-billing-user-stub'

export function createDwsBillingResponseStub (
  id: DwsBillingId = DWS_BILLING_ID_MIN,
  numberOfUsers = DWS_BILLING_STATEMENT_STUB_COUNT_PER_BUNDLE
): DwsBillingsApi.GetResponse {
  const billing = createDwsBillingStub({ id })
  const bundles = createDwsBillingBundleStubsForBilling(billing)
  const users = createDwsBillingUserStubs(numberOfUsers)
  const statements = bundles.flatMap(bundle => createDwsBillingStatementStubsForBundle({ bundle, users }))
  const reports = bundles.flatMap(bundle => createDwsBillingServiceReportStubsForBundle({ bundle, users }))
  const copayCoordinations = createDwsBillingCopayCoordinationStubs({ statements })
  return {
    billing,
    bundles,
    statements,
    reports,
    copayCoordinations
  }
}
