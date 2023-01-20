/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'

const profile = breadcrumb('登録情報', '/profile')

export const settings = {
  profile: {
    index: [
      breadcrumb('登録情報')
    ],
    edit: [
      profile,
      breadcrumb('登録情報を編集')
    ],
    bankAccount: {
      edit: [
        profile,
        breadcrumb('給与振込口座を編集')
      ]
    }
  }
} as const
