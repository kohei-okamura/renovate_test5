/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { Office } from '~/models/office'
import { VBreadcrumb } from '~/models/vuetify'

const index = breadcrumb('事業所', '/offices?restore=1')

const toOffice = (office: Office | undefined, fragment: string = ''): VBreadcrumb[] => [
  index,
  breadcrumb(office?.abbr ?? '', office ? `/offices/${office.id}${fragment}` : '')
]

export const offices = {
  index: [
    breadcrumb('事業所')
  ],
  new: [
    index,
    breadcrumb('事業所を登録')
  ],
  view: (office: Office | undefined) => [
    index,
    breadcrumb(office?.abbr ?? '')
  ],
  edit: (office: Office | undefined) => [
    ...toOffice(office),
    breadcrumb('編集')
  ],
  homeHelpServiceCalcSpecs: {
    new: (office: Office | undefined) => [
      ...toOffice(office),
      breadcrumb('算定情報（障害・居宅介護）を登録')
    ],
    edit: (office: Office | undefined) => [
      ...toOffice(office),
      breadcrumb('算定情報（障害・居宅介護）を編集')
    ]
  },
  homeVisitLongTermCareCalcSpecs: {
    new: (office: Office | undefined) => [
      ...toOffice(office),
      breadcrumb('算定情報（介保・訪問介護）を登録')
    ],
    edit: (office: Office | undefined) => [
      ...toOffice(office),
      breadcrumb('算定情報（介保・訪問介護）を編集')
    ]
  },
  visitingCareForPwsdCalcSpecs: {
    new: (office: Office | undefined) => [
      ...toOffice(office),
      breadcrumb('算定情報（障害・重度訪問介護）を登録')
    ],
    edit: (office: Office | undefined) => [
      ...toOffice(office),
      breadcrumb('算定情報（障害・重度訪問介護）を編集')
    ]
  }
} as const
