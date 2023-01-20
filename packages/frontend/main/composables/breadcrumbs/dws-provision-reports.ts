/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'

const index = breadcrumb('障害福祉サービス予実', '/dws-provision-reports?restore=1')

export const dwsProvisionReports = {
  index: [
    breadcrumb('障害福祉サービス予実')
  ],
  edit: [
    index,
    breadcrumb('予実を登録・編集')
  ]
} as const
