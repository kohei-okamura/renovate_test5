/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { LtcsInsCard } from '~/models/ltcs-ins-card'
import { LtcsInsCardsApi } from '~/services/api/ltcs-ins-cards-api'

export const createLtcsInsCardState = () => ({
  ltcsInsCard: undefined as LtcsInsCard | undefined
})

export function useLtcsInsCardStore () {
  const { $api } = usePlugins()
  const state = reactive(createLtcsInsCardState())
  const actions = {
    async get (params: LtcsInsCardsApi.GetParams) {
      assign(state, await $api.ltcsInsCards.get(params))
    },
    async update ({ form, id, userId }: Parameters<typeof $api.ltcsInsCards.update>[0]) {
      assign(state, await $api.ltcsInsCards.update({ form, id, userId }))
    }
  }
  return createStore({ actions, state })
}

export type LtcsInsCardData = ReturnType<typeof createLtcsInsCardState>

export type LtcsInsCardStore = ReturnType<typeof useLtcsInsCardStore>

export type LtcsInsCardState = LtcsInsCardStore['state']

export const ltcsInsCardStoreKey: InjectionKey<LtcsInsCardStore> = Symbol('ltcsInsStore')

export const ltcsInsCardStateKey: InjectionKey<LtcsInsCardState> = Symbol('ltcsInsCardState')
