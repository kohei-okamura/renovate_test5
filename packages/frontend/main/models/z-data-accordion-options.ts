/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { VDataTableHeader } from '~/models/vuetify'

export type ZDataAccordionOptions<T> = Readonly<{
  content: string
  footerLink?: string
  footerLinkText?: string
  headers: VDataTableHeader[]
  itemLink?: (x: T) => string
  itemLinkPermissions?: Permission[]
  itemLinkText?: string
  title?: string
}>
