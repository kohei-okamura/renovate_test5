/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ComputedRef } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { VDataTableHeader } from '~/models/vuetify'

export type ZDataTableOptions<T> = Readonly<{
  content: string
  fab?: Readonly<{
    icon: string
    to: string
  }>
  footerLink?: string
  footerLinkPermissions?: Permission[]
  footerLinkText?: string
  headers: VDataTableHeader[]
  itemLink?: (x: T) => string
  itemLinkPermissions?: Permission[]
  itemLinkText?: string
  noDataText?: string | ComputedRef<string | undefined>
  title?: string
}>
