/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { usePlugins } from '~/composables/use-plugins'
import { useStaffs } from '~/composables/use-staffs'
import { StaffId } from '~/models/staff'
import { createStaffStubs } from '~~/stubs/create-staff-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-staffs', () => {
  const $api = createMockedApi('options')
  const options = createStaffStubs().map(x => ({ value: x.id, text: x.name.displayName }))

  beforeAll(() => {
    setupComposableTest()
    const plugins = createMockedPlugins({ $api })
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($api.options, 'staffs').mockResolvedValue(options)
  })

  afterEach(() => {
    $api.options.staffs.mockReset()
  })

  it.each([
    [Permission.createAttendances],
    [Permission.updateAttendances]
  ])('should call $api.options.staffs with permission: %s', permission => {
    useStaffs({ permission })

    expect($api.options.staffs).toHaveBeenCalledTimes(1)
    expect($api.options.staffs).toHaveBeenCalledWith({ permission })
  })

  describe('staffOptions', () => {
    it('should be ref to staffOptions', () => {
      const { staffOptions } = useStaffs({ permission: Permission.createAttendances })

      expect(staffOptions).toBeRef()
      expect(staffOptions.value).toBeEmptyArray()
    })

    it('should be ref to api response', async () => {
      const { staffOptions } = useStaffs({ permission: Permission.createAttendances })
      await flushPromises()

      expect(staffOptions.value).toBe(options)
    })
  })

  describe('isLoadingStaffs', () => {
    it('should be ref to boolean', () => {
      const { isLoadingStaffs } = useStaffs({ permission: Permission.createAttendances })

      expect(isLoadingStaffs).toBeRef()
      expect(isLoadingStaffs.value).toBeTrue()
    })

    it('should be ref to api response', async () => {
      const { isLoadingStaffs } = useStaffs({ permission: Permission.createAttendances })
      await flushPromises()

      expect(isLoadingStaffs.value).toBeFalse()
    })
  })

  describe('resolveStaffName', () => {
    it('should be ref to function', () => {
      const { resolveStaffName } = useStaffs({ permission: Permission.createAttendances })

      expect(resolveStaffName).toBeRef()
      expect(resolveStaffName.value).toBeFunction()
    })

    it.each<[StaffId, string]>(
      options.map(x => [x.value, x.text])
    )('should return staff\'s name', async (id, expected) => {
      const { resolveStaffName } = useStaffs({ permission: Permission.updateAttendances })
      await flushPromises()

      const actual = resolveStaffName.value(id)

      expect(actual).toBe(expected)
    })
  })
})
