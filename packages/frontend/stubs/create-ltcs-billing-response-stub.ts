/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { createLtcsBillingBundleStubsForBilling } from '~~/stubs/create-ltcs-billing-bundle-stub'
import { createLtcsBillingInvoiceStubsForBundle } from '~~/stubs/create-ltcs-billing-invoice-stub'
import { createLtcsBillingStatementStubsForInvoice } from '~~/stubs/create-ltcs-billing-statement-stub'
import { createLtcsBillingStub } from '~~/stubs/create-ltcs-billing-stub'
import { LTCS_BILLING_ID_MIN } from '~~/stubs/create-ltcs-billing-stub-settings'

export const createLtcsBillingResponseStub = (id: LtcsBillingId = LTCS_BILLING_ID_MIN): LtcsBillingsApi.GetResponse => {
  const billing = createLtcsBillingStub({ id })
  const bundles = createLtcsBillingBundleStubsForBilling(billing)
  const invoices = bundles.flatMap(createLtcsBillingInvoiceStubsForBundle)
  const statements = invoices.flatMap(createLtcsBillingStatementStubsForInvoice)
  return {
    billing,
    bundles,
    statements
  }
}
