/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { DwsSubsidyStore, useDwsSubsidyStore } from '~/composables/stores/use-dws-subsidy-store'
import { usePlugins } from '~/composables/use-plugins'
import { createDwsSubsidyResponseStub } from '~~/stubs/create-dws-subsidy-response-stub'
import { createDwsSubsidyStub } from '~~/stubs/create-dws-subsidy-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('/composables/stores/use-dws-subsidy-store', () => {
  const $api = createMockedApi('dwsSubsidies')
  const plugins = createMockedPlugins({ $api })
  let store: DwsSubsidyStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsSubsidyStore()
    })

    it('should have a value', () => {
      mocked(usePlugins).mockReturnValue(plugins)
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('dwsSubsidy', () => {
      it('should be ref to undefined', () => {
        expect(store.state.dwsSubsidy).toBeRef()
        expect(store.state.dwsSubsidy.value).toBeUndefined()
      })
    })

    describe('get', () => {
      const id = 1
      const userId = 1
      const response = createDwsSubsidyResponseStub()

      beforeEach(() => {
        jest.spyOn($api.dwsSubsidies, 'get').mockResolvedValue(response)
        store = useDwsSubsidyStore()
      })

      afterEach(() => {
        mocked($api.dwsSubsidies.get).mockReset()
      })

      it('should call $api.dwsProjects.get', async () => {
        await store.get({ id, userId })
        expect($api.dwsSubsidies.get).toHaveBeenCalledTimes(1)
        expect($api.dwsSubsidies.get).toHaveBeenCalledWith({ id, userId })
      })

      it('should update state.dwsSubsidy', async () => {
        expect(store.state.dwsSubsidy.value).toBeUndefined()
        await store.get({ id, userId })
        expect(store.state.dwsSubsidy.value).toStrictEqual(response.dwsSubsidy)
      })
    })

    describe('update', () => {
      const id = 1
      const userId = 1
      const current = createDwsSubsidyResponseStub()
      const dwsSubsidy = createDwsSubsidyStub(current.dwsSubsidy.id)
      const updated = { dwsSubsidy }
      const form = {
        period: dwsSubsidy.period,
        cityName: dwsSubsidy.cityName,
        cityCode: dwsSubsidy.cityCode,
        subsidyType: dwsSubsidy.subsidyType,
        benefitRate: dwsSubsidy.benefitRate,
        benefitAmount: dwsSubsidy.benefitAmount,
        copay: dwsSubsidy.copayAmount,
        note: dwsSubsidy.note
      }

      beforeAll(() => {
        store = useDwsSubsidyStore()
        jest.spyOn($api.dwsSubsidies, 'get').mockResolvedValue(current)
        jest.spyOn($api.dwsSubsidies, 'update').mockResolvedValue(updated)
      })

      beforeEach(async () => {
        await store.get({ id, userId })
      })

      it('should call $api.dwsSubsidies.update', async () => {
        await store.update({ form, id, userId })
        expect($api.dwsSubsidies.update).toHaveBeenCalledTimes(1)
        expect($api.dwsSubsidies.update).toHaveBeenCalledWith({ form, id, userId })
      })

      it('should update state.dwsSubsidies', async () => {
        expect(store.state.dwsSubsidy.value).toStrictEqual(current.dwsSubsidy)
        await store.update({ form, id, userId })
        expect(store.state.dwsSubsidy.value).toStrictEqual(updated.dwsSubsidy)
      })
    })
  })
})
