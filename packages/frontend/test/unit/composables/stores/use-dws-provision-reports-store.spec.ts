/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import {
  DwsProvisionReportsStore,
  useDwsProvisionReportsStore
} from '~/composables/stores/use-dws-provision-reports-store'
import { usePlugins } from '~/composables/use-plugins'
import { createDwsProvisionReportIndexResponseStub } from '~~/stubs/create-dws-provision-report-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-provision-reports-store', () => {
  const $api = createMockedApi('dwsProvisionReports')
  const plugins = createMockedPlugins({ $api })
  let store: DwsProvisionReportsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsProvisionReportsStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('dwsProvisionReports', () => {
      it('should be ref to empty array', () => {
        expect(store.state.dwsProvisionReports).toBeRef()
        expect(store.state.dwsProvisionReports.value).toBeEmptyArray()
      })
    })

    describe('isLoadingDwsProvisionReports', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingDwsProvisionReports).toBeRef()
        expect(store.state.isLoadingDwsProvisionReports.value).toBeFalse()
      })
    })

    describe('pagination', () => {
      it('should be ref to pagination object', () => {
        expect(store.state.pagination).toBeRef()
        expect(store.state.pagination.value).toStrictEqual({ page: 1 })
      })
    })

    describe('queryParams', () => {
      it('should be ref to undefined', () => {
        expect(store.state.queryParams).toBeRef()
        expect(store.state.queryParams.value).toBeUndefined()
      })
    })
  })

  describe('getIndex', () => {
    const response = createDwsProvisionReportIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.dwsProvisionReports, 'getIndex').mockResolvedValue(response)
      store = useDwsProvisionReportsStore()
    })

    afterEach(() => {
      mocked($api.dwsProvisionReports.getIndex).mockReset()
    })

    it('should call $api.dwsProvisionReports.getIndex', async () => {
      await store.getIndex({ officeId: 1 })
      expect($api.dwsProvisionReports.getIndex).toHaveBeenCalledTimes(1)
      expect($api.dwsProvisionReports.getIndex).toHaveBeenCalledWith({ all: true, officeId: 1 })
    })

    it('should update state.dwsProvisionReports', async () => {
      expect(store.state.dwsProvisionReports.value).not.toStrictEqual(response.list)
      await store.getIndex({ officeId: 1 })
      expect(store.state.dwsProvisionReports.value).toStrictEqual(response.list)
    })

    it('should clear state.dwsProvisionReports when params.officeId does not set', async () => {
      await store.getIndex({ officeId: 1 })
      expect(store.state.dwsProvisionReports.value).toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.dwsProvisionReports.value).toStrictEqual([])
    })

    it('should update state.isLoadingDwsProvisionReports', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.dwsProvisionReports.getIndex).mockReturnValue(deferred.promise)
      expect(store.state.isLoadingDwsProvisionReports.value).toBeFalse()

      const promise = store.getIndex({ officeId: 1 })

      expect(store.state.isLoadingDwsProvisionReports.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingDwsProvisionReports.value).toBeFalse()
    })
  })
})
