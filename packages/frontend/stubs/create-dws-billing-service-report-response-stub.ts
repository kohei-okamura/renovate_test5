/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingServiceReportsApi } from '~/services/api/dws-billing-service-reports-api'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingServiceReportStub } from '~~/stubs/create-dws-billing-service-report-stub'
import { createDwsBillingStub } from '~~/stubs/create-dws-billing-stub'
import { DWS_BILLING_ID_MIN } from '~~/stubs/create-dws-billing-stub-settings'
import { createDwsBillingUserStub } from '~~/stubs/create-dws-billing-user-stub'

export function createDwsBillingServiceReportResponseStub (
  id: DwsBillingId = DWS_BILLING_ID_MIN
): DwsBillingServiceReportsApi.GetResponse {
  const billing = createDwsBillingStub({ id })
  const bundle = createDwsBillingBundleStub({ billing })
  const user = createDwsBillingUserStub(id + bundle.id)
  const report = createDwsBillingServiceReportStub({ id: (id + bundle.id + user.userId), bundle, user })
  return {
    billing,
    bundle,
    report
  }
}
