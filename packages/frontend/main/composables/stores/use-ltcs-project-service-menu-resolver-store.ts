/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { LtcsProjectServiceMenu, LtcsProjectServiceMenuId } from '~/models/ltcs-project-service-menu'
import { VSelectOption } from '~/models/vuetify'

const toSelectOption = (item: LtcsProjectServiceMenu): VSelectOption<LtcsProjectServiceMenuId> => ({
  text: item.displayName,
  value: item.id
})

export const createLtcsProjectServiceMenuResolverState = () => ({
  menus: [] as LtcsProjectServiceMenu[],
  isLoadingServiceMenus: false
})

export type LtcsProjectServiceMenuResolverData = ReturnType<typeof createLtcsProjectServiceMenuResolverState>

export const filterLtcsProjectServiceMenuByCategory = (menus: LtcsProjectServiceMenu[]) => {
  return (category?: LtcsProjectServiceCategory) => {
    switch (category) {
      case LtcsProjectServiceCategory.physicalCare:
      case LtcsProjectServiceCategory.housework:
        return menus.filter(x => x.category === category).map(toSelectOption)
      default:
        return menus.map(toSelectOption)
    }
  }
}

export const createLtcsProjectServiceMenuResolverStoreGetters = (state: LtcsProjectServiceMenuResolverData) => ({
  resolveLtcsProjectServiceMenuName: computed(() => {
    const menus = state.menus
    return (x: LtcsProjectServiceMenu | LtcsProjectServiceMenuId, alternative: string = '-') => {
      return typeof x === 'number'
        ? (menus.find(menu => menu.id === x)?.displayName ?? alternative)
        : x.displayName
    }
  }),
  getLtcsProjectServiceMenuOptions: computed(() => filterLtcsProjectServiceMenuByCategory(state.menus))
})

export const useLtcsProjectServiceMenuResolverStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createLtcsProjectServiceMenuResolverState())
  const getters = createLtcsProjectServiceMenuResolverStoreGetters(state)
  const actions = {
    async update () {
      try {
        state.isLoadingServiceMenus = true
        const { list } = await $api.ltcsProjectServiceMenus.getIndex({ all: true })
        state.menus = [...list]
      } finally {
        state.isLoadingServiceMenus = false
      }
    }
  }
  return createStore({ actions, getters, state })
}

export type LtcsProjectServiceMenuResolverStore = ReturnType<typeof useLtcsProjectServiceMenuResolverStore>

export type LtcsProjectServiceMenuResolverState = LtcsProjectServiceMenuResolverStore['state']

export const ltcsProjectServiceMenuResolverStoreKey: InjectionKey<LtcsProjectServiceMenuResolverStore> = Symbol('LtcsProjectServiceMenuResolverStore')

export const ltcsProjectServiceMenuResolverStateKey: InjectionKey<LtcsProjectServiceMenuResolverState> = Symbol('LtcsProjectServiceMenuResolverState')
