/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingStatementId } from '~/models/dws-billing-statement'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingStatementStub } from '~~/stubs/create-dws-billing-statement-stub'
import { createDwsBillingStub } from '~~/stubs/create-dws-billing-stub'
import { DWS_BILLING_ID_MIN, DWS_BILLING_STATEMENT_ID_MIN } from '~~/stubs/create-dws-billing-stub-settings'

export function getServiceCodeDictionary (): { [key: string]: string } {
  return {
    111111: '身体日0.5',
    111112: '身体日0.5・2人',
    111113: '身体日0.5・基',
    111114: '身体日0.5・基・2人',
    '11A001': '身体日0.5・初計',
    '11A002': '身体日0.5・2人・初計',
    '11A003': '身体日0.5・基・初計',
    '11A004': '身体日0.5・基・2人・初計',
    '11A005': '身体日0.5・建1',
    '11A006': '身体日0.5・2人・建1',
    '11A007': '身体日0.5・基・建1',
    '11A008': '身体日0.5・基・2人・建1',
    '11A009': '身体日0.5・初計・建1',
    '11A010': '身体日0.5・2人・初計・建1',
    '11A011': '身体日0.5・基・初計・建1',
    '11A012': '身体日0.5・基・2人・初計・建1',
    '11A013': '身体日0.5・建2',
    '11A014': '身体日0.5・2人・建2',
    '11A015': '身体日0.5・基・建2',
    '11A016': '身体日0.5・基・2人・建2',
    '11A017': '身体日0.5・初計・建2',
    '11A018': '身体日0.5・2人・初計・建2',
    '11A019': '身体日0.5・基・初計・建2',
    '11A020': '身体日0.5・基・2人・初計・建2',
    111115: '身体日1.0',
    111116: '身体日1.0・2人',
    111117: '身体日1.0・基',
    111118: '身体日1.0・基・2人'
  }
}

type Params = {
  billingId?: DwsBillingId
  bundleId?: DwsBillingBundleId
  id?: DwsBillingStatementId
}

export function createDwsBillingStatementResponseStub (params?: Params): DwsBillingStatementsApi.GetResponse {
  const { billingId, bundleId, id } = {
    billingId: DWS_BILLING_ID_MIN,
    id: DWS_BILLING_STATEMENT_ID_MIN,
    ...params
  }
  const billing = createDwsBillingStub({ id: billingId })
  const bundle = createDwsBillingBundleStub({ billing, id: bundleId })
  return {
    billing,
    bundle,
    statement: createDwsBillingStatementStub({ bundle, id }),
    serviceCodeDictionary: getServiceCodeDictionary()
  }
}
