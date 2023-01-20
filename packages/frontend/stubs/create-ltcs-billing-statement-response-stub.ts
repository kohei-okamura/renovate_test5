/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingId } from '~/models/ltcs-billing'
import { LtcsBillingBundleId } from '~/models/ltcs-billing-bundle'
import { LtcsBillingStatementId } from '~/models/ltcs-billing-statement'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'
import { createLtcsBillingBundleStub } from '~~/stubs/create-ltcs-billing-bundle-stub'
import { createLtcsBillingStatementStub } from '~~/stubs/create-ltcs-billing-statement-stub'
import { createLtcsBillingStub } from '~~/stubs/create-ltcs-billing-stub'
import {
  LTCS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING,
  LTCS_BILLING_INVOICE_STUB_COUNT_PER_BUNDLE,
  LTCS_BILLING_STATEMENT_ID_MIN,
  LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE
} from '~~/stubs/create-ltcs-billing-stub-settings'

type Params = {
  billingId?: LtcsBillingId
  bundleId?: LtcsBillingBundleId
  id?: LtcsBillingStatementId
}
export const createLtcsBillingStatementResponseStub = (params: Params = {}): LtcsBillingStatementsApi.GetResponse => {
  // 各自動スタブ生成メソッドの ID 生成ルールを逆算して請求・請求単位の ID を決定する
  const id = params.id ?? LTCS_BILLING_STATEMENT_ID_MIN
  const invoiceId = Math.ceil(id / LTCS_BILLING_STATEMENT_STUB_COUNT_PER_INVOICE)
  const bundleId = params.bundleId ?? Math.ceil(invoiceId / LTCS_BILLING_INVOICE_STUB_COUNT_PER_BUNDLE)
  const billingId = params.billingId ?? Math.ceil(bundleId / LTCS_BILLING_BUNDLE_STUB_COUNT_PER_BILLING)
  return {
    billing: createLtcsBillingStub({ id: billingId }),
    bundle: createLtcsBillingBundleStub({ billingId, id: bundleId }),
    statement: createLtcsBillingStatementStub({ billingId, bundleId, id })
  }
}
