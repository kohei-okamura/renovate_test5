/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import { createSessionStore, SessionStore } from '~/composables/stores/create-session-store'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/stores/create-session-store', () => {
  const $api = createMockedApi('sessions')
  let sessionStore: SessionStore

  beforeAll(() => {
    setupComposableTest()
    sessionStore = createSessionStore({ $api })
  })

  it('should be initialized with default value', () => {
    const { state } = sessionStore
    expect(state.auth.value).toBeUndefined()
    expect(state.isActive.value).toBeFalse()
  })

  it('should be update state.auth when create is called', async () => {
    const expected = {
      isSystemAdmin: false,
      permissions: [],
      staff: createStaffStub(10)
    }
    jest.spyOn($api.sessions, 'create').mockReturnValue(Promise.resolve({ auth: expected }))
    const { state, create } = sessionStore

    await create({ form: {} })

    expect(state.auth.value).toStrictEqual(expected)
    expect(state.isActive.value).toBeTrue()
    mocked($api.sessions.create).mockReset()
  })

  it('should be update state.auth when get is called', async () => {
    const expected = {
      isSystemAdmin: true,
      permissions: [Permission.listOfficeGroups, Permission.listInternalOffices],
      staff: createStaffStub(1)
    }
    jest.spyOn($api.sessions, 'get').mockReturnValue(Promise.resolve({ auth: expected }))
    const { state, get } = sessionStore

    await get()

    expect(state.auth.value).toStrictEqual(expected)
    expect(state.isActive.value).toBeTrue()
    mocked($api.sessions.get).mockReset()
  })

  it('should be discard state.auth when destroy is called', async () => {
    jest.spyOn($api.sessions, 'delete').mockReturnValue(Promise.resolve())
    const { state, destroy } = sessionStore

    await destroy()

    expect(state.auth.value).toBeUndefined()
    expect(state.isActive.value).toBeFalse()
    mocked($api.sessions.delete).mockReset()
  })
})
