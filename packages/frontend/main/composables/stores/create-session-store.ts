/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import { createStore } from '~/composables/stores/utils'
import { Auth } from '~/models/auth'
import { NuxtContext } from '~/models/nuxt'
import { SessionsApi } from '~/services/api/sessions-api'

export const createSessionState = () => ({
  auth: undefined as Auth | undefined
})

type CreateSessionStoreParams = Pick<NuxtContext, '$api'>

export function createSessionStore ({ $api }: CreateSessionStoreParams) {
  const state = reactive(createSessionState())
  const getters = {
    isActive: computed(() => state.auth !== undefined)
  }
  const actions = {
    async create (params: SessionsApi.CreateParams) {
      const { auth } = await $api.sessions.create(params)
      state.auth = auth
    },
    deleteAuth () {
      state.auth = undefined
    },
    async destroy () {
      await $api.sessions.delete()
      state.auth = undefined
    },
    async get () {
      const { auth } = await $api.sessions.get()
      state.auth = auth
    }
  }
  return createStore({ actions, getters, state })
}

export type SessionData = ReturnType<typeof createSessionState>

export type SessionStore = ReturnType<typeof createSessionStore>

export type SessionState = SessionStore['state']
