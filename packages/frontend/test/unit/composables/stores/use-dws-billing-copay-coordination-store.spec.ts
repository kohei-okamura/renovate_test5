/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import {
  DwsBillingCopayCoordinationStore,
  useDwsBillingCopayCoordinationStore
} from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import {
  createDwsBillingCopayCoordinationResponseStub
} from '~~/stubs/create-dws-billing-copay-coordination-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-billing-copay-coordination-store', () => {
  const $api = createMockedApi('dwsBillingCopayCoordinations')
  const plugins = createMockedPlugins({ $api })
  let store: DwsBillingCopayCoordinationStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsBillingCopayCoordinationStore()
    })

    it('should have 5 values (3 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(3)
    })

    describe('billing', () => {
      it('should be ref to undefined', () => {
        expect(store.state.billing).toBeRef()
        expect(store.state.billing.value).toBeUndefined()
      })
    })

    describe('bundle', () => {
      it('should be ref to undefined', () => {
        expect(store.state.bundle).toBeRef()
        expect(store.state.bundle.value).toBeUndefined()
      })
    })

    describe('copay-coordination', () => {
      it('should be ref to undefined', () => {
        expect(store.state.copayCoordination).toBeRef()
        expect(store.state.copayCoordination.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const params: DwsBillingCopayCoordinationsApi.GetParams = {
      billingId: 10,
      bundleId: 20,
      id: 30
    }
    const response = createDwsBillingCopayCoordinationResponseStub()

    beforeEach(() => {
      jest.spyOn($api.dwsBillingCopayCoordinations, 'get').mockResolvedValue(response)
      store = useDwsBillingCopayCoordinationStore()
    })

    afterEach(() => {
      mocked($api.dwsBillingCopayCoordinations.get).mockReset()
    })

    it('should call $api.dwsBillingCopayCoordinations.get', async () => {
      await store.get(params)
      expect($api.dwsBillingCopayCoordinations.get).toHaveBeenCalledTimes(1)
      expect($api.dwsBillingCopayCoordinations.get).toHaveBeenCalledWith(params)
    })

    it('should update state.bundle', async () => {
      expect(store.state.bundle.value).not.toStrictEqual(response.bundle)
      await store.get(params)
      expect(store.state.bundle.value).toStrictEqual(response.bundle)
    })

    it('should update state.copayCoordination', async () => {
      expect(store.state.copayCoordination.value).not.toStrictEqual(response.copayCoordination)
      await store.get(params)
      expect(store.state.copayCoordination.value).toStrictEqual(response.copayCoordination)
    })
  })
})
