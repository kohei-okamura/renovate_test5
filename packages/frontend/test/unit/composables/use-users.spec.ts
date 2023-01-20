/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { usePlugins } from '~/composables/use-plugins'
import { useUsers } from '~/composables/use-users'
import { UserId } from '~/models/user'
import { createUserStubs } from '~~/stubs/create-user-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-users', () => {
  const $api = createMockedApi('options')
  const options = createUserStubs().map(x => ({ value: x.id, text: x.name.displayName }))

  beforeAll(() => {
    setupComposableTest()
    const plugins = createMockedPlugins({ $api })
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($api.options, 'users').mockResolvedValue(options)
  })

  afterEach(() => {
    $api.options.users.mockReset()
  })

  it.each([
    [Permission.createAttendances],
    [Permission.updateAttendances]
  ])('should call $api.options.users with permission: %s', permission => {
    useUsers({ permission })

    expect($api.options.users).toHaveBeenCalledTimes(1)
    expect($api.options.users).toHaveBeenCalledWith({ permission })
  })

  describe('userOptions', () => {
    it('should be ref to userOptions', () => {
      const { userOptions } = useUsers({ permission: Permission.createAttendances })

      expect(userOptions).toBeRef()
      expect(userOptions.value).toBeEmptyArray()
    })

    it('should be ref to api response', async () => {
      const { userOptions } = useUsers({ permission: Permission.createAttendances })
      await flushPromises()

      expect(userOptions.value).toBe(options)
    })
  })

  describe('isLoadingUsers', () => {
    it('should be ref to boolean', () => {
      const { isLoadingUsers } = useUsers({ permission: Permission.createAttendances })

      expect(isLoadingUsers).toBeRef()
      expect(isLoadingUsers.value).toBeTrue()
    })

    it('should be ref to api response', async () => {
      const { isLoadingUsers } = useUsers({ permission: Permission.createAttendances })
      await flushPromises()

      expect(isLoadingUsers.value).toBeFalse()
    })
  })

  describe('resolveUserName', () => {
    it('should be ref to function', () => {
      const { resolveUserName } = useUsers({ permission: Permission.createAttendances })

      expect(resolveUserName).toBeRef()
      expect(resolveUserName.value).toBeFunction()
    })

    it.each<[UserId, string]>(
      options.map(x => [x.value, x.text])
    )('should return user\'s name', async (id, expected) => {
      const { resolveUserName } = useUsers({ permission: Permission.updateAttendances })
      await flushPromises()

      const actual = resolveUserName.value(id)

      expect(actual).toBe(expected)
    })
  })
})
