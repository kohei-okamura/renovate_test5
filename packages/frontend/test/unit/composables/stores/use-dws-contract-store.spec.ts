/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { DwsContractStore, useDwsContractStore } from '~/composables/stores/use-dws-contract-store'
import { usePlugins } from '~/composables/use-plugins'
import { createContractResponseStub } from '~~/stubs/create-contract-response-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-contract-store', () => {
  const $api = createMockedApi('dwsContracts')
  const plugins = createMockedPlugins({ $api })
  let store: DwsContractStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsContractStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('contract', () => {
      it('should be ref to undefined', () => {
        expect(store.state.contract).toBeRef()
        expect(store.state.contract.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const userId = 517
    const response = createContractResponseStub()

    beforeEach(() => {
      jest.spyOn($api.dwsContracts, 'get').mockResolvedValue(response)
      store = useDwsContractStore()
    })

    afterEach(() => {
      mocked($api.dwsContracts.get).mockReset()
    })

    it('should call $api.dwsContracts.get', async () => {
      await store.get({ id, userId })
      expect($api.dwsContracts.get).toHaveBeenCalledTimes(1)
      expect($api.dwsContracts.get).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.contract', async () => {
      expect(store.state.contract.value).toBeUndefined()
      await store.get({ id, userId })
      expect(store.state.contract.value).toStrictEqual(response.contract)
    })
  })

  describe('update', () => {
    const id = 1
    const userId = 1
    const current = createContractResponseStub()
    const dwsContracts = createContractStub(current.contract.id)
    const updated = { contract: dwsContracts }
    const form = {
      id: dwsContracts.id,
      userId: dwsContracts.userId,
      officeId: dwsContracts.officeId,
      serviceSegment: dwsContracts.serviceSegment,
      status: dwsContracts.status,
      contractedOn: dwsContracts.contractedOn,
      terminatedOn: dwsContracts.terminatedOn
    }

    beforeAll(() => {
      store = useDwsContractStore()
      jest.spyOn($api.dwsContracts, 'update').mockResolvedValue(updated)
      jest.spyOn($api.dwsContracts, 'get').mockResolvedValue(current)
    })

    beforeEach(async () => {
      await store.get({ id, userId })
    })

    it('should call $api.dwsContracts.update', async () => {
      await store.update({ form, id, userId })
      expect($api.dwsContracts.update).toHaveBeenCalledTimes(1)
      expect($api.dwsContracts.update).toHaveBeenCalledWith({ form, id, userId })
    })

    it('should update state.contract', async () => {
      expect(store.state.contract.value).toStrictEqual(current.contract)
      await store.update({ form, id, userId })
      expect(store.state.contract.value).toStrictEqual(updated.contract)
    })
  })

  describe('disable', () => {
    const id = 1
    const userId = 1
    const current = createContractResponseStub()
    const dwsContracts = createContractStub(current.contract.id)
    const updated = { contract: dwsContracts }

    beforeAll(() => {
      store = useDwsContractStore()
      jest.spyOn($api.dwsContracts, 'disable').mockResolvedValue(updated)
      jest.spyOn($api.dwsContracts, 'get').mockResolvedValue(current)
    })

    beforeEach(async () => {
      await store.get({ id, userId })
    })

    it('should call $api.dwsContracts.disable', async () => {
      await store.disable({ id, userId })
      expect($api.dwsContracts.disable).toHaveBeenCalledTimes(1)
      expect($api.dwsContracts.disable).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.contract', async () => {
      expect(store.state.contract.value).toStrictEqual(current.contract)
      await store.disable({ id, userId })
      expect(store.state.contract.value).toStrictEqual(updated.contract)
    })
  })
})
