/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { Staff } from '~/models/staff'
import { VBreadcrumb } from '~/models/vuetify'

const index = breadcrumb('スタッフ', '/staffs?restore=1')

const toStaff = (staff: Staff | undefined): VBreadcrumb[] => [
  index,
  breadcrumb(staff?.name.displayName ?? '', staff ? `/staffs/${staff.id}` : '')
]

export const staffs = {
  index: [
    breadcrumb('スタッフ')
  ],
  new: [
    index,
    breadcrumb('スタッフを登録')
  ],
  view: (staff: Staff | undefined) => [
    index,
    breadcrumb(staff?.name.displayName ?? '')
  ],
  edit: (staff: Staff | undefined) => [
    ...toStaff(staff),
    breadcrumb('基本情報を編集')
  ],
  bankAccount: {
    edit: (staff: Staff | undefined) => [
      ...toStaff(staff),
      breadcrumb('銀行口座情報を編集')
    ]
  }
} as const
