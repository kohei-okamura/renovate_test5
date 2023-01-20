/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { StaffStore, useStaffStore } from '~/composables/stores/use-staff-store'
import { usePlugins } from '~/composables/use-plugins'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'
import { createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-staff-store', () => {
  const $api = createMockedApi('bankAccounts', 'staffs')
  const plugins = createMockedPlugins({ $api })
  const response = createStaffResponseStub()
  let store: StaffStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useStaffStore()
    })

    it('should have 4 values', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('bankAccount', () => {
      it('should be ref to undefined', () => {
        expect(store.state.bankAccount).toBeRef()
        expect(store.state.bankAccount.value).toBeUndefined()
      })
    })

    describe('offices', () => {
      it('should be ref to array', () => {
        expect(store.state.offices).toBeRef()
        expect(store.state.offices.value).toBeEmptyArray()
      })
    })

    describe('roles', () => {
      it('should be ref to array', () => {
        expect(store.state.roles).toBeRef()
        expect(store.state.roles.value).toBeEmptyArray()
      })
    })

    describe('staff', () => {
      it('should be ref to undefined', () => {
        expect(store.state.staff).toBeRef()
        expect(store.state.staff.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1

    beforeEach(() => {
      jest.spyOn($api.staffs, 'get').mockResolvedValue(response)
      store = useStaffStore()
    })

    afterEach(() => {
      mocked($api.staffs.get).mockReset()
    })

    it('should call $api.staffs.get', async () => {
      await store.get({ id })
      expect($api.staffs.get).toHaveBeenCalledTimes(1)
      expect($api.staffs.get).toHaveBeenCalledWith({ id })
    })

    it('should update state.bankAccount', async () => {
      expect(store.state.bankAccount.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.bankAccount.value).toStrictEqual(response.bankAccount)
    })

    it('should update state.offices', async () => {
      expect(store.state.offices.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.offices.value).toStrictEqual(response.offices)
    })

    it('should update state.roles', async () => {
      expect(store.state.roles.value).toBeEmptyArray()
      await store.get({ id })
      expect(store.state.roles.value).toStrictEqual(response.roles)
    })

    it('should update state.staff', async () => {
      expect(store.state.staff.value).toBeUndefined()
      await store.get({ id })
      expect(store.state.staff.value).toStrictEqual(response.staff)
    })
  })

  describe('update', () => {
    const id = 1
    const current = createStaffResponseStub()
    const staff = createStaffStub(current.staff.id)
    const updated = { staff }
    const form = {
      familyName: staff.name.familyName,
      givenName: staff.name.givenName,
      phoneticFamilyName: staff.name.phoneticFamilyName,
      phoneticGivenName: staff.name.phoneticGivenName,
      sex: staff.sex,
      birthday: staff.birthday,
      postcode: staff.addr.postcode,
      prefecture: staff.addr.prefecture,
      city: staff.addr.city,
      street: staff.addr.street,
      apartment: staff.addr.apartment,
      tel: staff.tel,
      fax: staff.fax,
      email: staff.email,
      certifications: staff.certifications
    }

    beforeAll(() => {
      store = useStaffStore()
      jest.spyOn($api.staffs, 'get').mockResolvedValue(current)
      jest.spyOn($api.staffs, 'update').mockResolvedValue(response)
    })

    beforeEach(async () => {
      await store.get({ id })
    })

    it('should call $api.staffs.update', async () => {
      await store.update({ form, id })
      expect($api.staffs.update).toHaveBeenCalledTimes(1)
      expect($api.staffs.update).toHaveBeenCalledWith({ form, id })
    })

    it('should update state.staff', async () => {
      expect(store.state.staff.value).toStrictEqual(current.staff)
      await store.update({ form, id })
      expect(store.state.staff.value).toStrictEqual(updated.staff)
    })
  })

  describe('updateBankAccount', () => {
    const current = createStaffResponseStub()
    const bankAccount = createBankAccountStub(current.bankAccount.id)
    const updated = { bankAccount }
    const form: BankAccountsApi.Form = {
      staffId: bankAccount.id,
      bankName: bankAccount.bankName,
      bankCode: bankAccount.bankCode,
      bankBranchName: bankAccount.bankBranchName,
      bankBranchCode: bankAccount.bankBranchCode,
      bankAccountType: bankAccount.bankAccountType,
      bankAccountNumber: bankAccount.bankAccountNumber,
      bankAccountHolder: bankAccount.bankAccountHolder
    }

    beforeAll(() => {
      store = useStaffStore()
      jest.spyOn($api.staffs, 'get').mockResolvedValue(current)
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
