/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { StaffId } from '~/models/staff'
import { createOfficeStubs } from '~~/stubs/create-office-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-offices', () => {
  setupComposableTest()

  const $api = createMockedApi('options')
  const options = createOfficeStubs().map(x => ({ value: x.id, text: x.abbr, keyword: x.name }))

  beforeAll(() => {
    const plugins = createMockedPlugins({ $api })
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($api.options, 'offices').mockResolvedValue(options)
  })

  afterEach(() => {
    $api.options.offices.mockReset()
  })

  it.each([
    [Permission.createAttendances],
    [Permission.updateAttendances]
  ])('should call $api.options.offices with permission: %s', permission => {
    useOffices({ permission })

    expect($api.options.offices).toHaveBeenCalledTimes(1)
    expect($api.options.offices).toHaveBeenCalledWith({ permission })
  })

  it.each<string, Purpose | undefined>([
    ['without purpose', undefined],
    ['with purpose (internal)', Purpose.internal],
    ['with purpose (external)', Purpose.external]
  ])('should call $api.options.offices %s', (_, purpose) => {
    const params = {
      permission: Permission.createBillings,
      ...purpose ? { purpose } : undefined
    }
    useOffices(params)

    expect($api.options.offices).toHaveBeenCalledTimes(1)
    expect($api.options.offices).toHaveBeenCalledWith(params)
  })

  it('should call $api.options.offices with Purpose.internal when internal is true', () => {
    const permission = Permission.createBillings
    const expected = { permission, purpose: Purpose.internal }
    useOffices({ permission, internal: true })

    expect($api.options.offices).toHaveBeenCalledTimes(1)
    expect($api.options.offices).toHaveBeenCalledWith(expected)
  })

  it('should call $api.options.offices with qualifications', () => {
    const params = {
      permission: Permission.createBillings,
      qualifications: [OfficeQualification.dwsHomeHelpService, OfficeQualification.dwsVisitingCareForPwsd]
    }
    useOffices(params)

    expect($api.options.offices).toHaveBeenCalledTimes(1)
    expect($api.options.offices).toHaveBeenCalledWith(params)
  })

  describe('officeOptions', () => {
    it('should be ref to officeOptions', () => {
      const { officeOptions } = useOffices({ permission: Permission.createAttendances })

      expect(officeOptions).toBeRef()
      expect(officeOptions.value).toBeEmptyArray()
    })

    it('should be ref to api response', async () => {
      const { officeOptions } = useOffices({ permission: Permission.createAttendances })
      await flushPromises()

      expect(officeOptions.value).toBe(options)
    })
  })

  describe('isLoadingOffices', () => {
    it('should be ref to boolean', () => {
      const { isLoadingOffices } = useOffices({ permission: Permission.createAttendances })

      expect(isLoadingOffices).toBeRef()
      expect(isLoadingOffices.value).toBeTrue()
    })

    it('should be ref to api response', async () => {
      const { isLoadingOffices } = useOffices({ permission: Permission.createAttendances })
      await flushPromises()

      expect(isLoadingOffices.value).toBeFalse()
    })
  })

  describe('resolveOfficeAbbr', () => {
    it('should be ref to function', () => {
      const { resolveOfficeAbbr } = useOffices({ permission: Permission.createAttendances })

      expect(resolveOfficeAbbr).toBeRef()
      expect(resolveOfficeAbbr.value).toBeFunction()
    })

    it.each<[StaffId, string]>(
      options.map(x => [x.value, x.text])
    )('should return staff\'s name', async (id, expected) => {
      const { resolveOfficeAbbr } = useOffices({ permission: Permission.updateAttendances })
      await flushPromises()

      const actual = resolveOfficeAbbr.value(id)

      expect(actual).toBe(expected)
    })
  })
})
