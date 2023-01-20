/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { Attendance } from '~/models/attendance'

const index = breadcrumb('勤務実績', '/attendances?restore=1')

export const attendances = {
  index: [
    breadcrumb('勤務実績')
  ],
  new: [
    index,
    breadcrumb('勤務実績を登録')
  ],
  view: [
    index,
    breadcrumb('勤務実績詳細')
  ],
  edit: (attendance: Attendance | undefined) => [
    index,
    breadcrumb('勤務実績詳細', attendance ? `/attendances/${attendance.id}` : ''),
    breadcrumb('勤務実績を編集')
  ]
} as const
