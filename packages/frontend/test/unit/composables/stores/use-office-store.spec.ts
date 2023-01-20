/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { OfficeStore, useOfficeStore } from '~/composables/stores/use-office-store'
import { usePlugins } from '~/composables/use-plugins'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-office-store', () => {
  const $api = createMockedApi('offices')
  const plugins = createMockedPlugins({ $api })
  let store: OfficeStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useOfficeStore()
    })

    it('should have 5 values', () => {
      expect(keys(store.state)).toHaveLength(5)
    })

    describe('homeHelpServiceCalcSpecs', () => {
      it('should be ref to undefined', () => {
        expect(store.state.homeHelpServiceCalcSpecs).toBeRef()
        expect(store.state.homeHelpServiceCalcSpecs.value).toBeUndefined()
      })
    })

    describe('homeVisitLongTermCareCalcSpecs', () => {
      it('should be ref to undefined', () => {
        expect(store.state.homeVisitLongTermCareCalcSpecs).toBeRef()
        expect(store.state.homeVisitLongTermCareCalcSpecs.value).toBeUndefined()
      })
    })

    describe('visitingCareForPwsdCalcSpecs', () => {
      it('should be ref to undefined', () => {
        expect(store.state.visitingCareForPwsdCalcSpecs).toBeRef()
        expect(store.state.visitingCareForPwsdCalcSpecs.value).toBeUndefined()
      })
    })

    describe('office', () => {
      it('should be ref to undefined', () => {
        expect(store.state.office).toBeRef()
        expect(store.state.office.value).toBeUndefined()
      })
    })

    describe('officeGroup', () => {
      it('should be ref to undefined', () => {
        expect(store.state.officeGroup).toBeRef()
        expect(store.state.officeGroup.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createOfficeResponseStub()

    beforeEach(() => {
      jest.spyOn($api.offices, 'get').mockResolvedValue(response)
      store = useOfficeStore()
    })

    afterEach(() => {
      mocked($api.offices.get).mockReset()
    })

    it('should call $api.offices.get', async () => {
      await store.get({ id })
      expect($api.offices.get).toHaveBeenCalledTimes(1)
      expect($api.offices.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.homeHelpServiceCalcSpecs', async () => {
      expect(store.state.homeHelpServiceCalcSpecs.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.homeHelpServiceCalcSpecs.value).toStrictEqual(response.homeHelpServiceCalcSpecs)
    })

    it('should update state.homeVisitLongTermCareCalcSpecs', async () => {
      expect(store.state.homeVisitLongTermCareCalcSpecs.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.homeVisitLongTermCareCalcSpecs.value)
        .toStrictEqual(response.homeVisitLongTermCareCalcSpecs)
    })

    it('should update state.visitingCareForPwsdCalcSpecs', async () => {
      expect(store.state.visitingCareForPwsdCalcSpecs.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.visitingCareForPwsdCalcSpecs.value)
        .toStrictEqual(response.visitingCareForPwsdCalcSpecs)
    })

    it('should update state.office', async () => {
      expect(store.state.office.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.office.value).toStrictEqual(response.office)
    })

    it('should update state.officeGroup', async () => {
      expect(store.state.officeGroup.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.officeGroup.value).toStrictEqual(response.officeGroup)
    })
  })

  describe('update', () => {
    const id = 1
    const current = createOfficeResponseStub()
    const office = createOfficeStub(current.office.id)
    const updated = { office }
    const form = {
      purpose: office.purpose,
      name: office.name,
      abbr: office.abbr,
      phoneticName: office.phoneticName,
      corporationName: '',
      phoneticCorporationName: '',
      tel: office.tel,
      fax: office.fax,
      email: office.email,
      qualifications: [],
      officeGroupId: office.officeGroupId,
      dwsGenericService: office.dwsGenericService,
      dwsCommAccompanyService: office.dwsCommAccompanyService,
      ltcsHomeVisitLongTermCareService: office.ltcsHomeVisitLongTermCareService,
      ltcsCareManagementService: office.ltcsCareManagementService,
      ltcsCompHomeVisitingService: office.ltcsCompHomeVisitingService,
      status: OfficeStatus.inOperation,
      ...office.addr
    }

    beforeAll(() => {
      store = useOfficeStore()
      jest.spyOn($api.offices, 'get').mockResolvedValue(current)
      jest.spyOn($api.offices, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    it('should call $api.offices.update', async () => {
      await store.update({ form, id })
      expect($api.offices.update).toHaveBeenCalledTimes(1)
      expect($api.offices.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.office', async () => {
      expect(store.state.office.value).toStrictEqual(current.office)
      await store.update({ form, id })
      expect(store.state.office.value).toStrictEqual(updated.office)
    })
  })
})
