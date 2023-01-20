/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeGroupId } from '~/models/office-group'
import { createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-office-groups', () => {
  const $api = createMockedApi('options')
  const options = createOfficeGroupStubs().map(x => ({ value: x.id, text: x.name }))

  beforeAll(() => {
    setupComposableTest()
    const plugins = createMockedPlugins({ $api })
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($api.options, 'officeGroups').mockResolvedValue(options)
  })

  afterEach(() => {
    $api.options.officeGroups.mockReset()
  })

  it.each([
    [Permission.createInternalOffices],
    [Permission.updateInternalOffices]
  ])('should call $api.options.officeGroups with permission: %s', permission => {
    useOfficeGroups({ permission })

    expect($api.options.officeGroups).toHaveBeenCalledTimes(1)
    expect($api.options.officeGroups).toHaveBeenCalledWith({ permission })
  })

  describe('officeGroupOptions', () => {
    it('should be ref to officeGroupOptions', () => {
      const { officeGroupOptions } = useOfficeGroups({ permission: Permission.createInternalOffices })

      expect(officeGroupOptions).toBeRef()
      expect(officeGroupOptions.value).toBeEmptyArray()
    })

    it('should be ref to api response', async () => {
      const { officeGroupOptions } = useOfficeGroups({ permission: Permission.createInternalOffices })
      await flushPromises()

      expect(officeGroupOptions.value).toBe(options)
    })
  })

  describe('isLoadingOfficeGroups', () => {
    it('should be ref to boolean', () => {
      const { isLoadingOfficeGroups } = useOfficeGroups({ permission: Permission.createInternalOffices })

      expect(isLoadingOfficeGroups).toBeRef()
      expect(isLoadingOfficeGroups.value).toBeTrue()
    })

    it('should be ref to api response', async () => {
      const { isLoadingOfficeGroups } = useOfficeGroups({ permission: Permission.createInternalOffices })
      await flushPromises()

      expect(isLoadingOfficeGroups.value).toBeFalse()
    })
  })

  describe('resolveOfficeGroupName', () => {
    it('should be ref to function', () => {
      const { resolveOfficeGroupName } = useOfficeGroups({ permission: Permission.createInternalOffices })

      expect(resolveOfficeGroupName).toBeRef()
      expect(resolveOfficeGroupName.value).toBeFunction()
    })

    it.each<[OfficeGroupId, string]>(
      options.map(x => [x.value, x.text])
    )('should return staff\'s name', async (id, expected) => {
      const { resolveOfficeGroupName } = useOfficeGroups({ permission: Permission.updateInternalOffices })
      await flushPromises()

      const actual = resolveOfficeGroupName.value(id)

      expect(actual).toBe(expected)
    })
  })
})
