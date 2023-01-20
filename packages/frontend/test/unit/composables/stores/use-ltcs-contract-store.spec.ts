/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { LtcsContractStore, useLtcsContractStore } from '~/composables/stores/use-ltcs-contract-store'
import { usePlugins } from '~/composables/use-plugins'
import { createContractResponseStub } from '~~/stubs/create-contract-response-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-contract-store', () => {
  const $api = createMockedApi('ltcsContracts')
  const plugins = createMockedPlugins({ $api })
  let store: LtcsContractStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsContractStore()
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
      jest.spyOn($api.ltcsContracts, 'get').mockResolvedValue(response)
      store = useLtcsContractStore()
    })

    afterEach(() => {
      mocked($api.ltcsContracts.get).mockReset()
    })

    it('should call $api.ltcsContracts.get', async () => {
      await store.get({ id, userId })
      expect($api.ltcsContracts.get).toHaveBeenCalledTimes(1)
      expect($api.ltcsContracts.get).toHaveBeenCalledWith({ id, userId })
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
    const contract = createContractStub(current.contract.id)
    const updated = { contract }
    const form = {
      contractedOn: contract.contractedOn,
      officeId: contract.officeId,
      serviceSegment: contract.serviceSegment,
      status: contract.status
    }

    beforeAll(() => {
      store = useLtcsContractStore()
      jest.spyOn($api.ltcsContracts, 'get').mockResolvedValue(current)
      jest.spyOn($api.ltcsContracts, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id, userId })
    })

    it('should call $api.ltcsContracts.update', async () => {
      await store.update({ form, id, userId })
      expect($api.ltcsContracts.update).toHaveBeenCalledTimes(1)
      expect($api.ltcsContracts.update).toHaveBeenCalledWith({ form, id, userId })
    })

    it('should update state.ltcsContracts', async () => {
      expect(store.state.contract.value).toStrictEqual(current.contract)
      await store.update({ form, id, userId })
      expect(store.state.contract.value).toStrictEqual(updated.contract)
    })
  })

  describe('disable', () => {
    const id = 1
    const userId = 1
    const current = createContractResponseStub()
    const contract = createContractStub(current.contract.id)
    const updated = { contract }

    beforeAll(() => {
      store = useLtcsContractStore()
      jest.spyOn($api.ltcsContracts, 'get').mockResolvedValue(current)
      jest.spyOn($api.ltcsContracts, 'disable').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id, userId })
    })

    it('should call $api.ltcsContracts.disable', async () => {
      await store.disable({ id, userId })
      expect($api.ltcsContracts.disable).toHaveBeenCalledTimes(1)
      expect($api.ltcsContracts.disable).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.ltcsContracts', async () => {
      expect(store.state.contract.value).toStrictEqual(current.contract)
      await store.disable({ id, userId })
      expect(store.state.contract.value).toStrictEqual(updated.contract)
    })
  })
})
