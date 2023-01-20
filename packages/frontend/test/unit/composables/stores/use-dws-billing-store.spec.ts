/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { keys } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import { convertResponseToState, DwsBillingStore, useDwsBillingStore } from '~/composables/stores/use-dws-billing-store'
import { usePlugins } from '~/composables/use-plugins'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-billing-store', () => {
  const $api = createMockedApi('dwsBillings')
  const plugins = createMockedPlugins({ $api })
  const id = 1
  const response = createDwsBillingResponseStub(id, 1)
  const state = convertResponseToState(response)
  let store: DwsBillingStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsBillingStore()
    })

    it('should have 5 values (5 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(5)
    })

    describe('billing', () => {
      it('should be ref to undefined', () => {
        expect(store.state.billing).toBeRef()
        expect(store.state.billing.value).toBeUndefined()
      })
    })

    describe('billingUnitsGroups', () => {
      it('should be ref to empty array', () => {
        expect(store.state.billingUnitsGroups).toBeRef()
        expect(store.state.billingUnitsGroups.value).toBeEmptyArray()
      })
    })

    describe('bundles', () => {
      it('should be ref to empty array', () => {
        expect(store.state.bundles).toBeRef()
        expect(store.state.bundles.value).toBeEmptyArray()
      })
    })

    describe('statusCounts', () => {
      it('should be ref to empty array', () => {
        expect(store.state.statusCounts).toBeRef()
        expect(store.state.statusCounts.value).toBeEmptyArray()
      })
    })
  })

  describe('get', () => {
    beforeAll(() => {
      jest.spyOn($api.dwsBillings, 'get').mockResolvedValue(response)
    })

    beforeEach(() => {
      store = useDwsBillingStore()
    })

    afterAll(() => {
      mocked($api.dwsBillings.get).mockReset()
    })

    it('should call $api.dwsBillings.get', async () => {
      await store.get({ id: 1 })
      expect($api.dwsBillings.get).toHaveBeenCalledTimes(1)
      expect($api.dwsBillings.get).toHaveBeenCalledWith({ id: 1 })
    })

    it('should update state.billing', async () => {
      expect(store.state.billing.value).not.toStrictEqual(state.billing)
      await store.get({ id: 1 })
      expect(store.state.billing.value).toStrictEqual(state.billing)
    })

    it('should update state.billingUnitsGroups', async () => {
      expect(store.state.billingUnitsGroups.value).not.toStrictEqual(state.billingUnitsGroups)
      await store.get({ id: 1 })
      expect(store.state.billingUnitsGroups.value).toStrictEqual(state.billingUnitsGroups)
    })

    it('should update state.bundles', async () => {
      expect(store.state.bundles.value).not.toStrictEqual(state.bundles)
      await store.get({ id: 1 })
      expect(store.state.bundles.value).toStrictEqual(state.bundles)
    })

    it('should update state.statusCounts', async () => {
      expect(store.state.statusCounts.value).not.toStrictEqual(state.statusCounts)
      await store.get({ id: 1 })
      expect(store.state.statusCounts.value).toStrictEqual(state.statusCounts)
    })
  })

  describe('updateStatus', () => {
    const status = DwsBillingStatus.fixed
    const job = createJobStub('token', JobStatus.inProgress)

    beforeAll(() => {
      jest.spyOn($api.dwsBillings, 'updateStatus').mockResolvedValue({ ...response, job })
    })

    beforeEach(() => {
      store = useDwsBillingStore()
    })

    afterAll(() => {
      mocked($api.dwsBillings.updateStatus).mockReset()
    })

    it('should call $api.dwsBillings.updateStatus', async () => {
      const status = DwsBillingStatus.checking
      const expected = {
        id,
        form: {
          status: DwsBillingStatus.checking
        }
      }
      await store.updateStatus(id, status)

      expect($api.dwsBillings.updateStatus).toHaveBeenCalledTimes(1)
      expect($api.dwsBillings.updateStatus).toHaveBeenCalledWith(expected)
    })

    it('should update state.billing', async () => {
      expect(store.state.billing.value).not.toStrictEqual(state.billing)
      await store.updateStatus(id, status)
      expect(store.state.billing.value).toStrictEqual(state.billing)
    })

    it('should update state.billingUnitsGroups', async () => {
      expect(store.state.billingUnitsGroups.value).not.toStrictEqual(state.billingUnitsGroups)
      await store.updateStatus(id, status)
      expect(store.state.billingUnitsGroups.value).toStrictEqual(state.billingUnitsGroups)
    })

    it('should update state.bundles', async () => {
      expect(store.state.bundles.value).not.toStrictEqual(state.bundles)
      await store.updateStatus(id, status)
      expect(store.state.bundles.value).toStrictEqual(state.bundles)
    })

    it('should update state.statusCounts', async () => {
      expect(store.state.statusCounts.value).not.toStrictEqual(state.statusCounts)
      await store.updateStatus(id, status)
      expect(store.state.statusCounts.value).toStrictEqual(state.statusCounts)
    })
    it('should update state.job', async () => {
      const s = { ...state, job }
      expect(store.state.job.value).not.toStrictEqual(s.job)
      await store.updateStatus(id, status)
      expect(store.state.job.value).toStrictEqual(s.job)
    })
  })
})
