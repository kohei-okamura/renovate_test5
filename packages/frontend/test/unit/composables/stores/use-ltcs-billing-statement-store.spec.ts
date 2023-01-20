/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { LtcsServiceDivisionCode } from '@zinger/enums/lib/ltcs-service-division-code'
import { keys } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import {
  LtcsBillingStatementStore,
  useLtcsBillingStatementStore
} from '~/composables/stores/use-ltcs-billing-statement-store'
import { usePlugins } from '~/composables/use-plugins'
import { createLtcsBillingStatementResponseStub } from '~~/stubs/create-ltcs-billing-statement-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-billing-statement-store', () => {
  const $api = createMockedApi('ltcsBillingStatements')
  const plugins = createMockedPlugins({ $api })

  let store: LtcsBillingStatementStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsBillingStatementStore()
    })

    it('should have 3 values (3 states, 0 getters)', () => {
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

    describe('statement', () => {
      it('should be ref to undefined', () => {
        expect(store.state.statement).toBeRef()
        expect(store.state.statement.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createLtcsBillingStatementResponseStub({ id })
    const billingId = response.billing.id
    const bundleId = response.bundle.id
    const params = { billingId, bundleId, id }

    beforeEach(() => {
      store = useLtcsBillingStatementStore()
      jest.spyOn($api.ltcsBillingStatements, 'get').mockResolvedValue(response)
    })

    afterEach(() => {
      mocked($api.ltcsBillingStatements.get).mockReset()
    })

    it('should call $api.ltcsBillingStatements.get', async () => {
      const params = {
        billingId: 1,
        bundleId: 2,
        id: 3
      }
      const expected = { ...params }
      await store.get(params)

      expect($api.ltcsBillingStatements.get).toHaveBeenCalledTimes(1)
      expect($api.ltcsBillingStatements.get).toHaveBeenCalledWith(expected)
    })

    it('should update billing', async () => {
      expect(store.state.billing.value).toBeUndefined()
      await store.get(params)
      expect(store.state.billing.value).toStrictEqual(response.billing)
    })

    it('should update bundle', async () => {
      expect(store.state.bundle.value).toBeUndefined()
      await store.get(params)
      expect(store.state.bundle.value).toStrictEqual(response.bundle)
    })

    it('should update statement', async () => {
      expect(store.state.statement.value).toBeUndefined()
      await store.get(params)
      expect(store.state.statement.value).toStrictEqual(response.statement)
    })
  })

  describe('update', () => {
    const response = createLtcsBillingStatementResponseStub()
    const aggregates = [{
      serviceDivisionCode: LtcsServiceDivisionCode.homeVisitLongTermCare,
      plannedScore: 3000
    }]
    const params = {
      billingId: 10,
      bundleId: 20,
      id: 30,
      form: { aggregates }
    }

    beforeAll(() => {
      jest.spyOn($api.ltcsBillingStatements, 'update').mockResolvedValue(response)
    })

    afterAll(() => {
      mocked($api.ltcsBillingStatements.update).mockRestore()
    })

    beforeEach(() => {
      store = useLtcsBillingStatementStore()
    })

    afterEach(() => {
      mocked($api.ltcsBillingStatements.update).mockClear()
    })

    it('should call $api.ltcsBillingStatements.update', async () => {
      await store.update(params)
      expect($api.ltcsBillingStatements.update).toHaveBeenCalledTimes(1)
      expect($api.ltcsBillingStatements.update).toHaveBeenCalledWith(params)
    })

    it('should update state.bundle', async () => {
      expect(store.state.bundle.value).not.toStrictEqual(response.bundle)
      await store.update(params)
      expect(store.state.bundle.value).toStrictEqual(response.bundle)
    })

    it('should update state.statement', async () => {
      expect(store.state.statement.value).not.toStrictEqual(response.statement)
      await store.update(params)
      expect(store.state.statement.value).toStrictEqual(response.statement)
    })
  })

  describe('updateStatus', () => {
    const id = 1
    const response = createLtcsBillingStatementResponseStub({ id })
    const billingId = response.billing.id
    const bundleId = response.bundle.id
    const params = { billingId, bundleId, id }
    const status = LtcsBillingStatus.fixed

    beforeAll(() => {
      jest.spyOn($api.ltcsBillingStatements, 'updateStatus').mockResolvedValue(response)
    })

    afterAll(() => {
      mocked($api.ltcsBillingStatements.updateStatus).mockRestore()
    })

    beforeEach(() => {
      store = useLtcsBillingStatementStore()
    })

    afterEach(() => {
      mocked($api.ltcsBillingStatements.updateStatus).mockClear()
    })

    it('should call $api.ltcsBillingStatements.updateStatus', async () => {
      const params = {
        billingId: 1,
        bundleId: 2,
        id: 3
      }
      const status = LtcsBillingStatus.checking
      const expected = {
        ...params,
        form: { status }
      }
      await store.updateStatus(params, status)

      expect($api.ltcsBillingStatements.updateStatus).toHaveBeenCalledTimes(1)
      expect($api.ltcsBillingStatements.updateStatus).toHaveBeenCalledWith(expected)
    })

    it('should update billing', async () => {
      expect(store.state.billing.value).toBeUndefined()
      await store.updateStatus(params, status)
      expect(store.state.billing.value).toStrictEqual(response.billing)
    })

    it('should update bundle', async () => {
      expect(store.state.bundle.value).toBeUndefined()
      await store.updateStatus(params, status)
      expect(store.state.bundle.value).toStrictEqual(response.bundle)
    })

    it('should update statement', async () => {
      expect(store.state.statement.value).toBeUndefined()
      await store.updateStatus(params, status)
      expect(store.state.statement.value).toStrictEqual(response.statement)
    })
  })
})
