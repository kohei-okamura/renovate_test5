/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { UserStore, useUserStore } from '~/composables/stores/use-user-store'
import { usePlugins } from '~/composables/use-plugins'
import { createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-user-store', () => {
  const $api = createMockedApi('bankAccounts', 'users')
  const plugins = createMockedPlugins({ $api })
  let store: UserStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useUserStore()
    })

    it('should have 10 values', () => {
      expect(keys(store.state)).toHaveLength(11)
    })

    describe('bankAccount', () => {
      it('should be ref to undefined', () => {
        expect(store.state.bankAccount).toBeRef()
        expect(store.state.bankAccount.value).toBeUndefined()
      })
    })

    describe('contracts', () => {
      it('should be ref to array', () => {
        expect(store.state.contracts).toBeRef()
        expect(store.state.contracts.value).toBeEmptyArray()
      })
    })

    describe('dwsCertifications', () => {
      it('should be ref to array', () => {
        expect(store.state.dwsCertifications).toBeRef()
        expect(store.state.dwsCertifications.value).toBeEmptyArray()
      })
    })

    describe('dwsProjects', () => {
      it('should be ref to array', () => {
        expect(store.state.dwsProjects).toBeRef()
        expect(store.state.dwsProjects.value).toBeEmptyArray()
      })
    })

    describe('dwsSubsidies', () => {
      it('should be ref to array', () => {
        expect(store.state.dwsSubsidies).toBeRef()
        expect(store.state.dwsSubsidies.value).toBeEmptyArray()
      })
    })

    describe('dwsCalcSpecs', () => {
      it('should be ref to array', () => {
        expect(store.state.dwsCalcSpecs).toBeRef()
        expect(store.state.dwsCalcSpecs.value).toBeEmptyArray()
      })
    })

    describe('ltcsInsCards', () => {
      it('should be ref to array', () => {
        expect(store.state.ltcsInsCards).toBeRef()
        expect(store.state.ltcsInsCards.value).toBeEmptyArray()
      })
    })

    describe('ltcsProjects', () => {
      it('should be ref to array', () => {
        expect(store.state.ltcsProjects).toBeRef()
        expect(store.state.ltcsProjects.value).toBeEmptyArray()
      })
    })

    describe('ltcsSubsidies', () => {
      it('should be ref to array', () => {
        expect(store.state.ltcsSubsidies).toBeRef()
        expect(store.state.ltcsSubsidies.value).toBeEmptyArray()
      })
    })

    describe('user', () => {
      it('should be ref to undefined', () => {
        expect(store.state.user).toBeRef()
        expect(store.state.user.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const response = createUserResponseStub()

    beforeEach(() => {
      jest.spyOn($api.users, 'get').mockResolvedValue(response)
      store = useUserStore()
    })

    afterEach(() => {
      mocked($api.users.get).mockReset()
    })

    it('should call $api.users.get', async () => {
      await store.get({ id })
      expect($api.users.get).toHaveBeenCalledTimes(1)
      expect($api.users.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.bankAccount', async () => {
      expect(store.state.bankAccount.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.bankAccount.value).toStrictEqual(response.bankAccount)
    })

    it('should update state.contracts', async () => {
      expect(store.state.contracts.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.contracts.value).toStrictEqual(response.contracts)
    })

    it('should update state.dwsCertifications', async () => {
      expect(store.state.dwsCertifications.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.dwsCertifications.value).toStrictEqual(response.dwsCertifications)
    })

    it('should update state.dwsProjects', async () => {
      expect(store.state.dwsProjects.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.dwsProjects.value).toStrictEqual(response.dwsProjects)
    })

    it('should update state.dwsCalcSpecs', async () => {
      expect(store.state.dwsCalcSpecs.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.dwsCalcSpecs.value).toStrictEqual(response.dwsCalcSpecs)
    })

    it('should update state.ltcsInsCards', async () => {
      expect(store.state.ltcsInsCards.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.ltcsInsCards.value).toStrictEqual(response.ltcsInsCards)
    })

    it('should update state.ltcsProjects', async () => {
      expect(store.state.ltcsProjects.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.ltcsProjects.value).toStrictEqual(response.ltcsProjects)
    })

    it('should update state.ltcsCalcSpecs', async () => {
      expect(store.state.ltcsCalcSpecs.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.ltcsCalcSpecs.value).toStrictEqual(response.ltcsCalcSpecs)
    })

    it('should update state.ltcsSubsidies', async () => {
      expect(store.state.ltcsSubsidies.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.ltcsSubsidies.value).toStrictEqual(response.ltcsSubsidies)
    })

    it('should update state.user', async () => {
      expect(store.state.user.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.user.value).toStrictEqual(response.user)
    })
  })

  describe('update', () => {
    const id = 1
    const current = createUserResponseStub()
    const user = createUserStub(current.user.id)
    const updated = { user }
    const form = {
      familyName: user.name.familyName,
      givenName: user.name.givenName,
      phoneticFamilyName: user.name.phoneticFamilyName,
      phoneticGivenName: user.name.phoneticGivenName,
      sex: user.sex,
      birthday: user.birthday,
      postcode: user.addr.postcode,
      city: user.addr.city,
      street: user.addr.street,
      apartment: user.addr.apartment,
      contacts: user.contacts
    }

    beforeAll(() => {
      store = useUserStore()
      jest.spyOn($api.users, 'get').mockResolvedValue(current)
      jest.spyOn($api.users, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    it('should call $api.users.update', async () => {
      await store.update({ form, id })
      expect($api.users.update).toHaveBeenCalledTimes(1)
      expect($api.users.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.user', async () => {
      expect(store.state.user.value).toStrictEqual(current.user)
      await store.update({ form, id })
      expect(store.state.user.value).toStrictEqual(updated.user)
    })
  })

  describe('updateBankAccount', () => {
    const current = createUserResponseStub()
    const bankAccount = createBankAccountStub(current.bankAccount.id)
    const updated = { bankAccount }
    const form = {
      userId: bankAccount.id,
      bankName: bankAccount.bankName,
      bankCode: bankAccount.bankCode,
      bankBranchName: bankAccount.bankBranchName,
      bankBranchCode: bankAccount.bankBranchCode,
      bankAccountType: bankAccount.bankAccountType,
      bankAccountNumber: bankAccount.bankAccountNumber,
      bankAccountHolder: bankAccount.bankAccountHolder
    }

    beforeAll(() => {
      store = useUserStore()
      jest.spyOn($api.users, 'get').mockResolvedValue(current)
      jest.spyOn($api.bankAccounts, 'update').mockResolvedValue(updated)
    })

    it('should call $api.bankAccounts.update', async () => {
      await store.updateBankAccount({ form })
      expect($api.bankAccounts.update).toHaveBeenCalledTimes(1)
      expect($api.bankAccounts.update).toHaveBeenCalledWith({ form })
    })

    it('should update state.bankAccount', async () => {
      expect(store.state.bankAccount.value).toStrictEqual(current.bankAccount)
      await store.updateBankAccount({ form })
      expect(store.state.bankAccount.value).toStrictEqual(updated.bankAccount)
    })
  })
})
