/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { Shift } from '~/models/shift'
import { VBreadcrumb } from '~/models/vuetify'

const index = breadcrumb('勤務シフト', '/shifts?restore=1')

const toShift = (shift: Shift | undefined): VBreadcrumb[] => [
  index,
  breadcrumb('勤務シフト詳細', shift ? `/shifts/${shift.id}` : '')
]

export const shifts = {
  index: [
    breadcrumb('勤務シフト')
  ],
  new: [
    index,
    breadcrumb('勤務シフトを登録')
  ],
  view: [
    index,
    breadcrumb('勤務シフト詳細')
  ],
  edit: (shift: Shift | undefined) => [
    ...toShift(shift),
    breadcrumb('編集')
  ],
  imports: {
    new: [
      index,
      breadcrumb('勤務シフトを一括登録')
    ]
  }
} as const
