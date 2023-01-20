/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import {
  DwsBillingStatementStore,
  useDwsBillingStatementStore
} from '~/composables/stores/use-dws-billing-statement-store'
import { usePlugins } from '~/composables/use-plugins'
import { createDwsBillingStatementAggregateStubs } from '~~/stubs/create-dws-billing-statement-aggregate-stub'
import {
  createDwsBillingStatementResponseStub,
  getServiceCodeDictionary
} from '~~/stubs/create-dws-billing-statement-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-billing-statement-store', () => {
  const $api = createMockedApi('dwsBillingStatements')
  const plugins = createMockedPlugins({ $api })
  let store: DwsBillingStatementStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsBillingStatementStore()
    })

    it('should have 5 values (4 states, 1 getters)', () => {
      expect(keys(store.state)).toHaveLength(5)
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

    describe('statement', () => {
      it('should be ref to undefined', () => {
        expect(store.state.statement).toBeRef()
        expect(store.state.statement.value).toBeUndefined()
      })
    })

    describe('serviceCodeDictionary', () => {
      it('should be ref to undefined', () => {
        expect(store.state.serviceCodeDictionary).toBeRef()
        expect(store.state.serviceCodeDictionary.value).toBeUndefined()
      })
    })

    describe('resolveServiceContentAbbr', () => {
      it('should be ref to undefined', () => {
        expect(store.state.resolveServiceContentAbbr).toBeRef()
        expect(store.state.resolveServiceContentAbbr.value).toBeFunction()
      })
    })
  })

  describe('resolveServiceContentAbbr', () => {
    const response = createDwsBillingStatementResponseStub({})
    const dictionary = getServiceCodeDictionary()
    const { statement } = response

    const get = async () => {
      await store.get({ id: 10, billingId: 1, bundleId: 10 })
    }

    beforeAll(() => {
      jest.spyOn($api.dwsBillingStatements, 'get').mockResolvedValue(response)
    })

    afterAll(() => {
      mocked($api.dwsBillingStatements.get).mockReset()
    })

    beforeEach(() => {
      store = useDwsBillingStatementStore()
    })

    it.each(statement.items)('should return  service content abbreviation associated with service code', async x => {
      await get()
      expect(store.state.resolveServiceContentAbbr.value(x.serviceCode)).toEqual(dictionary[x.serviceCode])
    })

    it('should be reflected state changes', async () => {
      const key = Object.keys(dictionary)[0]
      const resolvedName = computed(() => store.state.resolveServiceContentAbbr.value(key))
      expect(resolvedName.value).toBeUndefined()
      await get()
      expect(resolvedName.value).toBe(dictionary[key])
    })
  })

  describe('get', () => {
    const params = {
      billingId: 10,
      bundleId: 20,
      id: 30
    }
    const response = createDwsBillingStatementResponseStub({})

    beforeEach(() => {
      jest.spyOn($api.dwsBillingStatements, 'get').mockResolvedValue(response)
      store = useDwsBillingStatementStore()
    })

    afterEach(() => {
      mocked($api.dwsBillingStatements.get).mockReset()
    })

    it('should call $api.dwsBillingStatements.get', async () => {
      await store.get(params)
      expect($api.dwsBillingStatements.get).toHaveBeenCalledTimes(1)
      expect($api.dwsBillingStatements.get).toHaveBeenCalledWith(params)
    })

    it('should update state.bundle', async () => {
      expect(store.state.bundle.value).not.toStrictEqual(response.bundle)
      await store.get(params)
      expect(store.state.bundle.value).toStrictEqual(response.bundle)
    })

    it('should update state.statement', async () => {
      expect(store.state.statement.value).not.toStrictEqual(response.statement)
      await store.get(params)
      expect(store.state.statement.value).toStrictEqual(response.statement)
    })
  })

  describe('update', () => {
    const aggregates = createDwsBillingStatementAggregateStubs()
      .map(({ serviceDivisionCode, managedCopay }) => ({ serviceDivisionCode, managedCopay }))
    const result = CopayCoordinationResult.coordinated

    describe.each([
      ['update', { aggregates }],
      ['updateCopayCoordination', { result, amount: 2800 }],
      ['updateStatus', { status: DwsBillingStatus.fixed }]
    ])('%s', (key, form) => {
      const name = key as keyof Omit<DwsBillingStatementStore, 'get' | 'state'>
      const params = {
        billingId: 10,
        bundleId: 20,
        id: 30,
        form
      }

      const response = createDwsBillingStatementResponseStub()

      beforeEach(() => {
        jest.spyOn($api.dwsBillingStatements, name).mockResolvedValue(response)
        store = useDwsBillingStatementStore()
      })

      afterEach(() => {
        mocked($api.dwsBillingStatements[name]).mockReset()
      })

      it(`should call $api.dwsBillingStatements.${name}`, async () => {
        await store[name](params)
        expect($api.dwsBillingStatements[name]).toHaveBeenCalledTimes(1)
        expect($api.dwsBillingStatements[name]).toHaveBeenCalledWith(params)
      })

      it('should update state.bundle', async () => {
        expect(store.state.bundle.value).not.toStrictEqual(response.bundle)
        await store[name](params)
        expect(store.state.bundle.value).toStrictEqual(response.bundle)
      })

      it('should update state.statement', async () => {
        expect(store.state.statement.value).not.toStrictEqual(response.statement)
        await store[name](params)
        expect(store.state.statement.value).toStrictEqual(response.statement)
      })
    })
  })
})
