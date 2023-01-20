/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import { createSessionState, SessionData, SessionStore } from '~/composables/stores/create-session-store'
import * as m from '~/composables/stores/use-session-store'
import { createStore } from '~/composables/stores/utils'
import { Auth } from '~/models/auth'
import { SessionsApi } from '~/services/api/sessions-api'

type Data = SessionData
type Store = SessionStore & {
  updateAuth: (auth: Auth) => void
}

export const createSessionStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...createSessionState(),
    ...data
  })
  const getters = {
    isActive: computed(() => state.auth !== undefined)
  }
  const actions = {
    create: (_: SessionsApi.CreateParams) => Promise.resolve(),
    deleteAuth: () => {
      state.auth = undefined
    },
    destroy: () => Promise.resolve(),
    get: () => Promise.resolve(),
    updateAuth: (auth: Auth) => {
      state.auth = auth
    }
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useSessionStore').mockReturnValue(store)
  return store
}
