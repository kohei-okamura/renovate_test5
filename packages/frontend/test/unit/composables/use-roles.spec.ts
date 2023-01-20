/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { usePlugins } from '~/composables/use-plugins'
import { useRoles } from '~/composables/use-roles'
import { RoleId } from '~/models/role'
import { createRoleStubs } from '~~/stubs/create-role-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-roles', () => {
  const $api = createMockedApi('options')
  const options = createRoleStubs().map(x => ({ value: x.id, text: x.name }))

  beforeAll(() => {
    setupComposableTest()
    const plugins = createMockedPlugins({ $api })
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($api.options, 'roles').mockResolvedValue(options)
  })

  afterEach(() => {
    $api.options.roles.mockReset()
  })

  it.each([
    [Permission.createStaffs],
    [Permission.updateStaffs]
  ])('should call $api.options.roles with permission: %s', permission => {
    useRoles({ permission })

    expect($api.options.roles).toHaveBeenCalledTimes(1)
    expect($api.options.roles).toHaveBeenCalledWith({ permission })
  })

  describe('roleOptions', () => {
    it('should be ref to roleOptions', () => {
      const { roleOptions } = useRoles({ permission: Permission.createStaffs })

      expect(roleOptions).toBeRef()
      expect(roleOptions.value).toBeEmptyArray()
    })

    it('should be ref to api response', async () => {
      const { roleOptions } = useRoles({ permission: Permission.createStaffs })
      await flushPromises()

      expect(roleOptions.value).toBe(options)
    })
  })

  describe('resolveRoleName', () => {
    it('should be ref to function', () => {
      const { resolveRoleName } = useRoles({ permission: Permission.createStaffs })

      expect(resolveRoleName).toBeRef()
      expect(resolveRoleName.value).toBeFunction()
    })

    it.each<[RoleId, string]>(
      options.map(x => [x.value, x.text])
    )('should return role\'s name', async (id, expected) => {
      const { resolveRoleName } = useRoles({ permission: Permission.updateStaffs })
      await flushPromises()

      const actual = resolveRoleName.value(id)

      expect(actual).toBe(expected)
    })
  })
})
