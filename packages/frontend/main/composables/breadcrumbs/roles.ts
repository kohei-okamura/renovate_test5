/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { Role } from '~/models/role'
import { VBreadcrumb } from '~/models/vuetify'

const index = breadcrumb('ロール', '/roles?restore=1')

const toRole = (role: Role | undefined): VBreadcrumb[] => [
  index,
  breadcrumb(role?.name ?? '', role ? `/roles/${role.id}` : '')
]

export const roles = {
  index: [
    breadcrumb('ロール')
  ],
  new: [
    index,
    breadcrumb('ロールを登録')
  ],
  view: (role: Role | undefined) => [
    index,
    breadcrumb(role?.name ?? '')
  ],
  edit: (role: Role | undefined) => [
    ...toRole(role),
    breadcrumb('編集')
  ]
} as const
