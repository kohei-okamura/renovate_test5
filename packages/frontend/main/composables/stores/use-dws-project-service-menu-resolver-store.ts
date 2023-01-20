/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsProjectServiceMenu, DwsProjectServiceMenuId } from '~/models/dws-project-service-menu'
import { VSelectOption } from '~/models/vuetify'

const toSelectOption = (item: DwsProjectServiceMenu): VSelectOption<DwsProjectServiceMenuId> => ({
  text: item.displayName,
  value: item.id
})

export const createDwsProjectServiceCategoryResolverState = () => ({
  menus: [] as DwsProjectServiceMenu[],
  isLoadingServiceMenus: false
})

export type DwsProjectServiceCategoryResolverData = ReturnType<typeof createDwsProjectServiceCategoryResolverState>

export const filterDwsProjectServiceMenuByCategory = (menus: DwsProjectServiceMenu[]) => {
  return (category?: DwsProjectServiceCategory) => {
    switch (category) {
      case DwsProjectServiceCategory.physicalCare:
      case DwsProjectServiceCategory.housework:
      case DwsProjectServiceCategory.accompanyWithPhysicalCare:
      case DwsProjectServiceCategory.accompany:
      case DwsProjectServiceCategory.visitingCareForPwsd:
        return menus.filter(x => x.category === category).map(toSelectOption)
      default:
        return menus.map(toSelectOption)
    }
  }
}

export const createDwsProjectServiceCategoryResolverStoreGetters = (state: DwsProjectServiceCategoryResolverData) => ({
  resolveDwsProjectServiceMenuName: computed(() => {
    const menus = state.menus
    return (x: DwsProjectServiceMenu | DwsProjectServiceMenuId, alternative: string = '-') => {
      return typeof x === 'number'
        ? (menus.find(menu => menu.id === x)?.displayName ?? alternative)
        : x.displayName
    }
  }),
  getDwsProjectServiceMenuOptions: computed(() => filterDwsProjectServiceMenuByCategory(state.menus))
})

export const useDwsProjectServiceMenuResolverStore = () => {
  const { $api } = usePlugins()
  const state = reactive(createDwsProjectServiceCategoryResolverState())
  const getters = createDwsProjectServiceCategoryResolverStoreGetters(state)
  const actions = {
    async update () {
      try {
        state.isLoadingServiceMenus = true
        const { list } = await $api.dwsProjectServiceMenus.getIndex({ all: true })
        state.menus = [...list]
      } finally {
        state.isLoadingServiceMenus = false
      }
    }
  }
  return createStore({ actions, getters, state })
}

export type DwsProjectServiceMenuResolverStore = ReturnType<typeof useDwsProjectServiceMenuResolverStore>

export type DwsProjectServiceMenuResolverState = DwsProjectServiceMenuResolverStore['state']

export const dwsProjectServiceMenuResolverStoreKey: InjectionKey<DwsProjectServiceMenuResolverStore> = Symbol('DwsProjectServiceCategoryResolverStore')

export const dwsProjectServiceMenuResolverStateKey: InjectionKey<DwsProjectServiceMenuResolverState> = Symbol('DwsProjectServiceCategoryResolverState')
