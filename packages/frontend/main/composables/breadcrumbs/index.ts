/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ComputedRef } from '@nuxtjs/composition-api'
import { attendances } from '~/composables/breadcrumbs/attendances'
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { dwsBillings } from '~/composables/breadcrumbs/dws-billings'
import { dwsProvisionReports } from '~/composables/breadcrumbs/dws-provision-reports'
import { ltcsBillings } from '~/composables/breadcrumbs/ltcs-billings'
import { ltcsProvisionReports } from '~/composables/breadcrumbs/ltcs-provision-reports'
import { offices } from '~/composables/breadcrumbs/offices'
import { ownExpensePrograms } from '~/composables/breadcrumbs/own-expense-programs'
import { roles } from '~/composables/breadcrumbs/roles'
import { setting } from '~/composables/breadcrumbs/setting'
import { settings } from '~/composables/breadcrumbs/settings'
import { shifts } from '~/composables/breadcrumbs/shifts'
import { staffs } from '~/composables/breadcrumbs/staffs'
import { userBillings } from '~/composables/breadcrumbs/user-billings'
import { users } from '~/composables/breadcrumbs/users'
import { VBreadcrumb } from '~/models/vuetify'
import { RefOrValue, unref } from '~/support/reactive'

const breadcrumbs = {
  attendances,
  callings: [breadcrumb('出勤確認')],
  dashboard: [breadcrumb('ダッシュボード')],
  dwsBillings,
  dwsProvisionReports,
  ltcsBillings,
  ltcsProvisionReports,
  officeGroups: [breadcrumb('事業所グループ')],
  offices,
  ownExpensePrograms,
  roles,
  setting,
  settings,
  shifts,
  staffs,
  userBillings,
  users
} as const

type Breadcrumbs = typeof breadcrumbs
type BreadcrumbsPath = ObjectPath<Breadcrumbs>

type BreadcrumbsStaticDefinition = VBreadcrumb[]
type BreadcrumbsDynamicDefinition<T extends any[]> = ((...args: T) => VBreadcrumb[])
type BreadcrumbsDefinition<T extends any[]> = BreadcrumbsStaticDefinition | BreadcrumbsDynamicDefinition<T>

type Resolved = {
  [K in BreadcrumbsPath]: Resolve<Breadcrumbs, K>
}
type Wrap<T> = {
  [K in keyof T]: RefOrValue<T[K]>
}
type UseBreadcrumbsArgs<T extends BreadcrumbsPath> = Resolved[T] extends (...args: infer U) => VBreadcrumb[]
  ? Wrap<U>
  : []
type Response = {
  breadcrumbs: VBreadcrumb[] | ComputedRef<VBreadcrumb[]>
}

function isDynamic<T extends any[]> (x: BreadcrumbsDefinition<T>): x is BreadcrumbsDynamicDefinition<T> {
  return typeof x === 'function'
}

type UseBreadcrumbs = {
  <T extends BreadcrumbsPath> (path: T, ...args: UseBreadcrumbsArgs<T>): Response
}

export const useBreadcrumbs: UseBreadcrumbs = <T extends BreadcrumbsPath> (path: T, ...args: any[]) => {
  const resolved = path.split('.').reduce((z, x) => z[x], breadcrumbs as any)
  if (isDynamic<UseBreadcrumbsArgs<T>>(resolved)) {
    const xs = args.map(unref) as UseBreadcrumbsArgs<T>
    return { breadcrumbs: computed(() => resolved(...xs)) }
  } else {
    return { breadcrumbs: resolved }
  }
}
