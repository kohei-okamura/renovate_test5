/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { DwsBilling, DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordinationId } from '~/models/dws-billing-copay-coordination'
import { DwsBillingUser } from '~/models/dws-billing-user'

const index = breadcrumb('障害福祉サービス請求', '/dws-billings?restore=1')

const createCopayCoordination = () => {
  type Argument = {
    billingId?: DwsBillingId
    bundleId?: DwsBillingBundleId
    id?: DwsBillingCopayCoordinationId
    name?: DwsBillingUser['name']['displayName']
    statementId?: DwsBillingCopayCoordinationId
  }
  return {
    view: ({ billingId, name }: Argument) => [
      index,
      breadcrumb('請求詳細', `/dws-billings/${billingId}`),
      breadcrumb(`利用者負担上限額管理結果票（${name}）`)
    ],
    new: ({ billingId }: Argument) => [
      index,
      breadcrumb('請求詳細', `/dws-billings/${billingId}`),
      breadcrumb('利用者負担上限額管理結果票を作成')
    ],
    edit: ({ billingId, bundleId, id, name, statementId }: Argument) => [
      index,
      breadcrumb('請求詳細', `/dws-billings/${billingId}`),
      breadcrumb(`利用者負担上限額管理結果票（${name}）`, `/dws-billings/${billingId}/bundles/${bundleId}/statements/${statementId}/copay-coordinations/${id}`),
      breadcrumb('利用者負担上限額管理結果票を編集')
    ]
  }
}

export const dwsBillings = {
  index: [
    breadcrumb('障害福祉サービス請求')
  ],
  new: [
    index,
    breadcrumb('請求を作成')
  ],
  view: [
    index,
    breadcrumb('請求詳細')
  ],
  copayCoordination: createCopayCoordination(),
  reports: {
    view: ({ billingId, name }: {
      billingId: DwsBillingId
      name: DwsBillingUser['name']['displayName']
    }) => [
      index,
      breadcrumb('請求詳細', `/dws-billings/${billingId}`),
      breadcrumb(`サービス提供実績記録票（${name}）`)
    ]
  },
  statement: {
    view: (billing: DwsBilling | undefined) => [
      index,
      breadcrumb('請求詳細', billing ? `/dws-billings/${billing.id}` : ''),
      breadcrumb('明細書')
    ]
  }
} as const
