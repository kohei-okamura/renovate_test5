/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { keys } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import { LtcsBillingStore, useLtcsBillingStore } from '~/composables/stores/use-ltcs-billing-store'
import { usePlugins } from '~/composables/use-plugins'
import { createLtcsBillingResponseStub } from '~~/stubs/create-ltcs-billing-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-billing-store', () => {
  const $api = createMockedApi('ltcsBillings')
  const plugins = createMockedPlugins({ $api })

  let store: LtcsBillingStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsBillingStore()
    })

    it('should have 4 values (4 states, 4 getters)', () => {
      expect(keys(store.state)).toHaveLength(8)
    })

    describe('billing', () => {
      it('should be ref to undefined', () => {
        expect(store.state.billing).toBeRef()
        expect(store.state.billing.value).toBeUndefined()
      })
    })

    describe('bundles', () => {
      it('should be ref to empty array', () => {
        expect(store.state.bundles).toBeRef()
        expect(store.state.bundles.value).toBeEmptyArray()
      })
    })

    describe('statements', () => {
      it('should be ref to empty array', () => {
        expect(store.state.statements).toBeRef()
        expect(store.state.statements.value).toBeEmptyArray()
      })
    })

    describe('groupedStatements', () => {
      it('should be ref to empty object', () => {
        expect(store.state.groupedStatements).toBeRef()
        expect(store.state.groupedStatements.value).toMatchObject({})
      })
    })

    describe('hasStatements', () => {
      it('should be ref to false', () => {
        expect(store.state.hasStatements).toBeRef()
        expect(store.state.hasStatements.value).toBeFalse()
      })
    })

    describe('providedInList', () => {
      it('should be ref to empty array', () => {
        expect(store.state.providedInList).toBeRef()
        expect(store.state.providedInList.value).toBeEmptyArray()
      })
    })

    describe('statusAggregate', () => {
      it('should be ref to empty object', () => {
        expect(store.state.statusAggregate).toBeRef()
        expect(store.state.statusAggregate.value).toMatchObject({})
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createLtcsBillingResponseStub()

    beforeEach(() => {
      store = useLtcsBillingStore()
      jest.spyOn($api.ltcsBillings, 'get').mockResolvedValue(response)
    })

    afterEach(() => {
      mocked($api.ltcsBillings.get).mockReset()
    })

    it('should call $api.ltcsBillings.get', async () => {
      await store.get({ id: 1 })
      expect($api.ltcsBillings.get).toHaveBeenCalledTimes(1)
      expect($api.ltcsBillings.get).toHaveBeenCalledWith({ id: 1 })
    })

    it('should update billing', async () => {
      expect(store.state.billing.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.billing.value).toStrictEqual(response.billing)
    })

    it('should update bundles', async () => {
      expect(store.state.bundles.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.bundles.value).toStrictEqual(response.bundles)
    })

    it('should update statements', async () => {
      expect(store.state.statements.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.statements.value).toStrictEqual(response.statements)
    })

    it('should update groupedStatements', async () => {
      expect(store.state.groupedStatements.value).toMatchObject({})
      await store.get({ id })
      expect(store.state.groupedStatements.value).toMatchSnapshot()
    })

    it('should update hasStatements', async () => {
      expect(store.state.hasStatements.value).toBeFalse()
      await store.get({ id })
      expect(store.state.hasStatements.value).toBeTrue()
    })

    it('should update providedInList', async () => {
      expect(store.state.providedInList.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.providedInList.value).toMatchSnapshot()
    })

    it('should update statusAggregate', async () => {
      expect(store.state.statusAggregate.value).toMatchObject({})
      await store.get({ id })
      expect(store.state.statusAggregate.value).toMatchSnapshot()
    })
  })

  describe('updateStatus', () => {
    const id = 1
    const status = LtcsBillingStatus.fixed
    const response = createLtcsBillingResponseStub()

    beforeEach(() => {
      store = useLtcsBillingStore()
      jest.spyOn($api.ltcsBillings, 'updateStatus').mockResolvedValue(response)
    })

    afterEach(() => {
      mocked($api.ltcsBillings.updateStatus).mockReset()
    })

    it('should call $api.ltcsBillings.updateStatus', async () => {
      const id = 1
      const status = LtcsBillingStatus.checking
      const expected = {
        id: 1,
        form: {
          status: LtcsBillingStatus.checking
        }
      }
      await store.updateStatus(id, status)

      expect($api.ltcsBillings.updateStatus).toHaveBeenCalledTimes(1)
      expect($api.ltcsBillings.updateStatus).toHaveBeenCalledWith(expected)
    })

    it('should update billing', async () => {
      expect(store.state.billing.value).toBeUndefined()
      await store.updateStatus(id, status)
      expect(store.state.billing.value).toStrictEqual(response.billing)
    })

    it('should update bundles', async () => {
      expect(store.state.bundles.value).toBeEmptyArray()
      await store.updateStatus(id, status)
      expect(store.state.bundles.value).toStrictEqual(response.bundles)
    })

    it('should update statements', async () => {
      expect(store.state.statements.value).toBeEmptyArray()
      await store.updateStatus(id, status)
      expect(store.state.statements.value).toStrictEqual(response.statements)
    })

    it('should update groupedStatements', async () => {
      expect(store.state.groupedStatements.value).toMatchObject({})
      await store.updateStatus(id, status)
      expect(store.state.groupedStatements.value).toMatchSnapshot()
    })

    it('should update hasStatements', async () => {
      expect(store.state.hasStatements.value).toBeFalse()
      await store.updateStatus(id, status)
      expect(store.state.hasStatements.value).toBeTrue()
    })

    it('should update providedInList', async () => {
      expect(store.state.providedInList.value).toBeEmptyArray()
      await store.updateStatus(id, status)
      expect(store.state.providedInList.value).toMatchSnapshot()
    })

    it('should update statusAggregate', async () => {
      expect(store.state.statusAggregate.value).toMatchObject({})
      await store.updateStatus(id, status)
      expect(store.state.statusAggregate.value).toMatchSnapshot()
    })
  })
})
