/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'
import { createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'

describe('api/bank-accounts-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let bankAccounts: BankAccountsApi.Definition

  beforeEach(() => {
    bankAccounts = BankAccountsApi.create(axios)
  })

  describe('update', () => {
    it('should put /api/users/:userId/bank-account', async () => {
      const form: BankAccountsApi.Form = { userId: 1 }
      const url = `/api/users/${form.userId}/bank-account`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await bankAccounts.update({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should return response of the api', async () => {
      const id = 1
      const bankAccount = createBankAccountStub(id)
      const expected = { bankAccount }
      const form: BankAccountsApi.Form = {
        userId: bankAccount.id,
        bankName: bankAccount.bankName,
        bankCode: bankAccount.bankCode,
        bankBranchName: bankAccount.bankBranchName,
        bankBranchCode: bankAccount.bankBranchCode,
        bankAccountType: bankAccount.bankAccountType,
        bankAccountNumber: bankAccount.bankAccountNumber,
        bankAccountHolder: bankAccount.bankAccountHolder
      }

      adapter.setup(x => {
        x.onPut(`/api/users/${form.userId}/bank-account`).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await bankAccounts.update({ form })

      expect(response).toStrictEqual(expected)
    })

    it('should throw an AxiosError when failed to request(/api/users/:userId/bank-account)', async () => {
      const form: BankAccountsApi.Form = { userId: 3 }
      adapter.setup(x => {
        x.onPut(`/api/users/${form.userId}/bank-account`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = bankAccounts.update({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should put /api/staffs/:staffId/bank-account', async () => {
      const form: BankAccountsApi.Form = { staffId: 5 }
      const url = `/api/staffs/${form.staffId}/bank-account`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.NoContent)
      })

      await bankAccounts.update({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request(/api/staffs/:staffId/bank-account)', async () => {
      const form: BankAccountsApi.Form = { staffId: 7 }
      adapter.setup(x => {
        x.onPut(`/api/staffs/${form.staffId}/bank-account`).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = bankAccounts.update({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should throw an error when parameter has no id', async () => {
      const form: BankAccountsApi.Form = {}

      const promise = bankAccounts.update({ form })

      await expect(promise).rejects.toThrowError('Parameter has no id')
    })

    it('should throw an error when parameter has multiple id', async () => {
      const form: BankAccountsApi.Form = { staffId: 10, userId: 11 }

      const promise = bankAccounts.update({ form })

      await expect(promise).rejects.toThrowError('Parameter has multiple id')
    })
  })
})
