/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { NuxtAppOptions } from '@nuxt/types/app'
import { Permission } from '@zinger/enums/lib/permission'
import { noop } from '@zinger/helpers'
import { createMock, Mocked } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { SessionData } from '~/composables/stores/create-session-store'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'
import { createSessionStoreStub } from '~~/stubs/create-session-store-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('auth', () => {
  setupComposableTest()

  function createContext (data: Partial<SessionData> = {}): Mocked<NuxtContext> {
    const session = createSessionStoreStub(data)
    const $globalStore = createMock({ session })
    return createMock<NuxtContext>({
      app: createMock<NuxtAppOptions>({ $globalStore }),
      redirect: noop,
      route: { ...createMockedRoute(), fullPath: '/dashboard' }
    })
  }

  it('should redirect to top page', async () => {
    const middleware = auth()
    const context = createContext()
    jest.spyOn(context, 'redirect').mockReturnValue()

    // noinspection ES6MissingAwait
    middleware(context)
    await flushPromises()

    expect(context.redirect).toHaveBeenCalledTimes(1)
    expect(context.redirect).toHaveBeenCalledWith('/?path=/dashboard')
    mocked(context.redirect).mockReset()
  })

  it('should redirect to notfound page when authorization fail', async () => {
    const middleware = auth(...[Permission.listUsers])
    const context = createContext({
      auth: {
        isSystemAdmin: false,
        permissions: [],
        staff: createStaffStub()
      }
    })
    jest.spyOn(context, 'redirect').mockReturnValue()

    // noinspection ES6MissingAwait
    middleware(context)
    await flushPromises()

    expect(context.redirect).toHaveBeenCalledTimes(1)
    expect(context.redirect).toHaveBeenCalledWith('/notfound')
    mocked(context.redirect).mockReset()
  })

  it('should not redirect when authorization succeed', async () => {
    const middleware = auth(...[Permission.listUsers])
    const context = createContext({
      auth: {
        isSystemAdmin: false,
        permissions: [Permission.listUsers],
        staff: createStaffStub()
      }
    })
    jest.spyOn(context, 'redirect').mockReturnValue()

    // noinspection ES6MissingAwait
    middleware(context)
    await flushPromises()

    expect(context.redirect).toHaveBeenCalledTimes(0)
    mocked(context.redirect).mockReset()
  })
})
