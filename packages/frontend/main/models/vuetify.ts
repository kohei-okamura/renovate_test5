/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'

// see vuetify/src/components/VBreadcrumbs/index.ts
export type VBreadcrumb = Readonly<{
  disabled?: boolean
  exact?: boolean
  text: string
  to?: string
}>

// see vuetify/src/components/VDataTable/mixins/header.ts
export type VDataTableHeader = Readonly<{
  text: string
  value: string
  align?: 'start' | 'center' | 'end'
  sortable?: boolean
  divider?: boolean
  class?: string | string[]
  width?: string | number
  filter?: (value: any, search: string | undefined, item: any) => boolean
  filterExclusive?: boolean
  sort?: (a: any, b: any) => number
}>

export type VDatePickerType = 'year' | 'month' | 'date'

export type VSelectOption<T = number | string | undefined> = Readonly<{
  permissions?: Permission[]
  text: string
  value: T
}>

export type VTab = Readonly<{
  label: string
  permissions?: Permission[]
  to: string
}>
