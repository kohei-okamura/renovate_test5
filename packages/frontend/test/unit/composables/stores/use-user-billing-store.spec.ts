/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { UserBillingStore, useUserBillingStore } from '~/composables/stores/use-user-billing-store'
import { usePlugins } from '~/composables/use-plugins'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { createUserBillingResponseStub } from '~~/stubs/create-user-billing-response-stub'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-user-billing-store', () => {
  const $api = createMockedApi('userBillings')
  const plugins = createMockedPlugins({ $api })
  let store: UserBillingStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useUserBillingStore()
    })

    it('should have 1 values (1 states, 0 getters)', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('userBilling', () => {
      it('should be ref to empty array', () => {
        expect(store.state.userBilling).toBeRef()
        expect(store.state.userBilling.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createUserBillingResponseStub(id)

    beforeAll(() => {
      jest.spyOn($api.userBillings, 'get').mockResolvedValue(response)
    })

    afterAll(() => {
      mocked($api.userBillings.get).mockRestore()
    })

    beforeEach(() => {
      store = useUserBillingStore()
    })

    afterEach(() => {
      mocked($api.userBillings.get).mockClear()
    })

    it('should call $api.userBillings.get', async () => {
      await store.get({ id })
      expect($api.userBillings.get).toHaveBeenCalledTimes(1)
      expect($api.userBillings.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.userBilling', async () => {
      expect(store.state.userBilling.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.userBilling.value).toStrictEqual(response.userBilling)
    })
  })

  describe('update', () => {
    const id = 10
    const current = createUserBillingResponseStub(id)
    const userBilling = createUserBillingStub(current.userBilling.id)
    const updated = { userBilling }
    const form: UserBillingsApi.UpdateForm = {
      carriedOverAmount: userBilling.carriedOverAmount,
      paymentMethod: userBilling.user.billingDestination.paymentMethod,
      bankAccount: userBilling.user.bankAccount
    }

    beforeAll(() => {
      store = useUserBillingStore()
      jest.spyOn($api.userBillings, 'get').mockResolvedValue(current)
      jest.spyOn($api.userBillings, 'update').mockResolvedValue(updated)
    })

    afterAll(() => {
      mocked($api.userBillings.get).mockRestore()
      mocked($api.userBillings.update).mockRestore()
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    afterEach(() => {
      mocked($api.userBillings.get).mockClear()
      mocked($api.userBillings.update).mockClear()
    })

    it('should call $api.userBillings.update', async () => {
      await store.update({ form, id })
      expect($api.userBillings.update).toHaveBeenCalledTimes(1)
      expect($api.userBillings.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.userBilling', async () => {
      expect(store.state.userBilling.value).toStrictEqual(current.userBilling)
      await store.update({ form, id })
      expect(store.state.userBilling.value).toStrictEqual(updated.userBilling)
    })
  })
})
