/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import { noop } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { GlobalStore } from '~/composables/stores'
import { createSessionState, SessionStore } from '~/composables/stores/create-session-store'
import { useSessionStore } from '~/composables/stores/use-session-store'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { SessionsApi } from '~/services/api/sessions-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-session-store', () => {
  setupComposableTest()

  function createSessionStore (): SessionStore {
    const state = reactive({
      ...createSessionState()
    })
    const getters = {
      isActive: computed(() => state.auth !== undefined)
    }
    const actions = {
      create: (_: SessionsApi.CreateParams) => Promise.resolve(),
      deleteAuth: noop,
      destroy: () => Promise.resolve(),
      get: () => Promise.resolve()
    }
    return createStore({ actions, getters, state })
  }

  it('should return $globalStore\'s session', () => {
    // createMockedGlobalStore は内部で useSessionStore をスパイしているので使わない。
    const sessionStore = createSessionStore()
    const $globalStore: GlobalStore = {
      session: sessionStore
    }
    const plugins = createMockedPlugins({ $globalStore })
    mocked(usePlugins).mockReturnValue(plugins)

    const session = useSessionStore()

    expect(session).toEqual(sessionStore!)
  })
})
