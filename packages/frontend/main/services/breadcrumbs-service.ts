/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, toRefs } from '@nuxtjs/composition-api'
import { VBreadcrumb } from '~/models/vuetify'
import { Refs } from '~/support/reactive'

type Data = {
  breadcrumbs: VBreadcrumb[]
}

export type BreadcrumbsService = Refs<Data> & {
  readonly update: (xs: VBreadcrumb[]) => void
}

export function createBreadcrumbsService (): BreadcrumbsService {
  const data = reactive<Data>({
    breadcrumbs: []
  })
  return {
    ...toRefs(data),
    update: xs => {
      data.breadcrumbs = xs
    }
  }
}
