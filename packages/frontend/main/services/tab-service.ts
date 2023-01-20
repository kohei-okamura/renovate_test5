/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ComputedRef, reactive, toRefs } from '@nuxtjs/composition-api'
import { VTab } from '~/models/vuetify'
import { Refs } from '~/support/reactive'

type Data = {
  tabs: VTab[]
  tab: string | number | undefined
}

export type TabService = Refs<Data> & {
  readonly hasTabs: ComputedRef<boolean>
  readonly update: (xs: VTab[]) => void
}

export function createTabService (): TabService {
  const data = reactive<Data>({
    tabs: [],
    tab: undefined
  })
  return {
    ...toRefs(data),
    hasTabs: computed(() => data.tabs.length > 0),
    update: xs => {
      data.tabs = xs
    }
  }
}
