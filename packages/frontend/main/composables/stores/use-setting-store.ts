/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { Setting } from '~/models/setting'

export const createSettingState = () => ({
  organizationSetting: undefined as Setting | undefined
})

export function useSettingStore () {
  const { $api } = usePlugins()
  const state = reactive(createSettingState())
  const actions = {
    async get () {
      assign(state, await $api.setting.get())
    },
    async create ({ form }: Parameters<typeof $api.setting.create>[0]) {
      await $api.setting.create({ form })
    },
    async update ({ form }: Parameters<typeof $api.setting.update>[0]) {
      assign(state, await $api.setting.update({ form }))
    }
  }
  return createStore({ actions, state })
}

export type SettingData = ReturnType<typeof createSettingState>

export type SettingStore = ReturnType<typeof useSettingStore>

export type SettingState = SettingStore['state']

export const settingStoreKey: InjectionKey<SettingStore> = Symbol('settingStore')

export const settingStateKey: InjectionKey<SettingState> = Symbol('settingState')
