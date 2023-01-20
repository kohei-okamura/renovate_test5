/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { eraMonth } from '~/composables/era-date'
import { UserBilling } from '~/models/user-billing'

const index = breadcrumb('利用者請求', '/user-billings?restore=1')

export const userBillings = {
  index: [
    breadcrumb('利用者請求')
  ],
  view: (userBilling: UserBilling | undefined) => [
    index,
    breadcrumb(userBilling
      ? `${userBilling.user.name.displayName}（${eraMonth(userBilling.providedIn)}分）`
      : ''
    )
  ],
  download: () => [
    index,
    breadcrumb('全銀ファイルダウンロード')
  ],
  upload: () => [
    index,
    breadcrumb('全銀ファイルアップロード')
  ]
} as const
