/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import {
  LtcsProvisionReportsStore,
  useLtcsProvisionReportsStore
} from '~/composables/stores/use-ltcs-provision-reports-store'
import { usePlugins } from '~/composables/use-plugins'
import { createLtcsProvisionReportIndexResponseStub } from '~~/stubs/create-ltcs-provision-report-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-provision-reports-store', () => {
  const $api = createMockedApi('ltcsProvisionReports')
  const plugins = createMockedPlugins({ $api })
  let store: LtcsProvisionReportsStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsProvisionReportsStore()
    })

    it('should have 4 values (4 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('ltcsProvisionReports', () => {
      it('should be ref to empty array', () => {
        expect(store.state.ltcsProvisionReports).toBeRef()
        expect(store.state.ltcsProvisionReports.value).toBeEmptyArray()
      })
    })

    describe('isLoadingLtcsProvisionReports', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingLtcsProvisionReports).toBeRef()
        expect(store.state.isLoadingLtcsProvisionReports.value).toBeFalse()
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
    const response = createLtcsProvisionReportIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.ltcsProvisionReports, 'getIndex').mockResolvedValue(response)
      store = useLtcsProvisionReportsStore()
    })

    afterEach(() => {
      mocked($api.ltcsProvisionReports.getIndex).mockReset()
    })

    it('should call $api.ltcsProvisionReports.getIndex', async () => {
      await store.getIndex({ officeId: 1 })
      expect($api.ltcsProvisionReports.getIndex).toHaveBeenCalledTimes(1)
      expect($api.ltcsProvisionReports.getIndex).toHaveBeenCalledWith({ all: true, officeId: 1 })
    })

    it('should update state.ltcsProvisionReports', async () => {
      expect(store.state.ltcsProvisionReports.value).not.toStrictEqual(response.list)
      await store.getIndex({ officeId: 1 })
      expect(store.state.ltcsProvisionReports.value).toStrictEqual(response.list)
    })

    it('should clear state.ltcsProvisionReports when params.officeId does not set', async () => {
      await store.getIndex({ officeId: 1 })
      expect(store.state.ltcsProvisionReports.value).toStrictEqual(response.list)
      await store.getIndex({ all: true })
      expect(store.state.ltcsProvisionReports.value).toStrictEqual([])
    })

    it('should update state.isLoadingLtcsProvisionReports', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.ltcsProvisionReports.getIndex).mockReturnValue(deferred.promise)
      expect(store.state.isLoadingLtcsProvisionReports.value).toBeFalse()

      const promise = store.getIndex({ officeId: 1 })

      expect(store.state.isLoadingLtcsProvisionReports.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingLtcsProvisionReports.value).toBeFalse()
    })
  })
})
