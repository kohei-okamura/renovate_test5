/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { LtcsBilling } from '~/models/ltcs-billing'

const index = breadcrumb('介護保険サービス請求', '/ltcs-billings?restore=1')

export const ltcsBillings = {
  index: [
    breadcrumb('介護保険サービス請求')
  ],
  new: [
    index,
    breadcrumb('請求を作成')
  ],
  view: [
    index,
    breadcrumb('請求詳細')
  ],
  statement: {
    view: (billing: LtcsBilling | undefined) => [
      index,
      breadcrumb('請求詳細', billing ? `/ltcs-billings/${billing.id}` : ''),
      breadcrumb('明細書')
    ]
  }
} as const
