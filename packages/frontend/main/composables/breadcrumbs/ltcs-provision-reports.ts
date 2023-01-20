/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'

const index = breadcrumb('介護保険サービス予実', '/ltcs-provision-reports?restore=1')

export const ltcsProvisionReports = {
  index: [
    breadcrumb('介護保険サービス予実')
  ],
  edit: [
    index,
    breadcrumb('予実を登録・編集')
  ]
} as const
