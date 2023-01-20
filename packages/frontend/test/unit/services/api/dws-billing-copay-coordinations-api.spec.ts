/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingCopayCoordinationExchangeAim } from '@zinger/enums/lib/dws-billing-copay-coordination-exchange-aim'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import {
  createDwsBillingCopayCoordinationResponseStub
} from '~~/stubs/create-dws-billing-copay-coordination-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/dws-billing-copay-coordinations-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsBillingCopayCoordinations: DwsBillingCopayCoordinationsApi.Definition

  beforeEach(() => {
    dwsBillingCopayCoordinations = DwsBillingCopayCoordinationsApi.create(axios)
  })

  describe('create', () => {
    const form: DwsBillingCopayCoordinationsApi.Form = {
      userId: 10,
      items: [
        { officeId: 20, subtotal: { fee: 300, copay: 100, coordinatedCopay: 20 } }
      ],
      result: CopayCoordinationResult.appropriated,
      exchangeAim: DwsBillingCopayCoordinationExchangeAim.declaration,
      isProvided: true
    }

    test('/api/dws-billings/:billingId/bundles/:bundleId/copay-coordinations に POST リクエストを送る', async () => {
      adapter.setup(x => {
        const response = createDwsBillingCopayCoordinationResponseStub()
        x.onPost('/api/dws-billings/10/bundles/20/copay-coordinations').replyOnce(HttpStatusCode.Created, response)
      })

      await dwsBillingCopayCoordinations.create({
        billingId: 10,
        bundleId: 20,
        form
      })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({
        method: 'post',
        url: '/api/dws-billings/10/bundles/20/copay-coordinations'
      })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    test('API からのレスポンス内容を返す', async () => {
      const response = createDwsBillingCopayCoordinationResponseStub({ id: 1 })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPost('/api/dws-billings/128/bundles/256/copay-coordinations').replyOnce(HttpStatusCode.Created, response)
      })

      const actual = await dwsBillingCopayCoordinations.create({
        billingId: 128,
        bundleId: 256,
        form
      })

      expect(actual).toStrictEqual(expected)
    })

    test('API レスポンスのステータスコードが 2xx でない場合は AxiosError を投げる', async () => {
      adapter.setup(x => {
        x.onPost('/api/dws-billings/10/bundles/20/copay-coordinations').replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingCopayCoordinations.create({
        billingId: 10,
        bundleId: 20,
        form
      })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    test('/api/dws-billings/:billingId/bundles/:bundleId/copay-coordinations/:id に GET リクエストを送る', async () => {
      adapter.setup(x => {
        const response = createDwsBillingCopayCoordinationResponseStub({ id: 30 })
        x.onGet('/api/dws-billings/10/bundles/20/copay-coordinations/30').replyOnce(HttpStatusCode.OK, response)
      })

      await dwsBillingCopayCoordinations.get({
        billingId: 10,
        bundleId: 20,
        id: 30
      })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({
        method: 'get',
        url: '/api/dws-billings/10/bundles/20/copay-coordinations/30'
      })
    })

    test('API からのレスポンス内容を返す', async () => {
      const response = createDwsBillingCopayCoordinationResponseStub({ id: 5678 })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onGet('/api/dws-billings/12/bundles/34/copay-coordinations/5678').replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingCopayCoordinations.get({
        billingId: 12,
        bundleId: 34,
        id: 5678
      })

      expect(actual).toStrictEqual(expected)
    })

    test('API レスポンスのステータスコードが 2xx でない場合は AxiosError を投げる', async () => {
      adapter.setup(x => {
        x.onGet('/api/dws-billings/10/bundles/20/copay-coordinations/99').replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingCopayCoordinations.get({
        billingId: 10,
        bundleId: 20,
        id: 99
      })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const form: DwsBillingCopayCoordinationsApi.Form = {
      userId: 10,
      items: [
        { officeId: 20, subtotal: { fee: 300, copay: 100, coordinatedCopay: 20 } }
      ],
      result: CopayCoordinationResult.appropriated,
      exchangeAim: DwsBillingCopayCoordinationExchangeAim.declaration,
      isProvided: true
    }

    test('/api/dws-billings/:billingId/bundles/:bundleId/copay-coordinations/:id に PUT リクエストを送る', async () => {
      adapter.setup(x => {
        const response = createDwsBillingCopayCoordinationResponseStub({ id: 3333 })
        x.onPut('/api/dws-billings/1111/bundles/2222/copay-coordinations/3333').replyOnce(HttpStatusCode.OK, response)
      })

      await dwsBillingCopayCoordinations.update({
        billingId: 1111,
        bundleId: 2222,
        id: 3333,
        form
      })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({
        method: 'put',
        url: '/api/dws-billings/1111/bundles/2222/copay-coordinations/3333'
      })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    test('API からのレスポンス内容を返す', async () => {
      const response = createDwsBillingCopayCoordinationResponseStub({ id: 333 })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut('/api/dws-billings/111/bundles/222/copay-coordinations/333').replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingCopayCoordinations.update({
        billingId: 111,
        bundleId: 222,
        id: 333,
        form
      })

      expect(actual).toStrictEqual(expected)
    })

    test('API レスポンスのステータスコードが 2xx でない場合は AxiosError を投げる', async () => {
      adapter.setup(x => {
        x.onPut('/api/dws-billings/11/bundles/22/copay-coordinations/33').replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingCopayCoordinations.update({
        billingId: 11,
        bundleId: 22,
        id: 33,
        form
      })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('updateStatus', () => {
    const form: DwsBillingCopayCoordinationsApi.UpdateStatusForm = {
      status: DwsBillingStatus.fixed
    }

    test(
      '/api/dws-billings/:billingId/bundles/:bundleId/copay-coordinations/:id/status に PUT リクエストを送る',
      async () => {
        adapter.setup(x => {
          const response = createDwsBillingCopayCoordinationResponseStub({ id: 265 })
          x.onPut('/api/dws-billings/3/bundles/4/copay-coordinations/5/status').replyOnce(HttpStatusCode.OK, response)
        })

        await dwsBillingCopayCoordinations.updateStatus({
          billingId: 3,
          bundleId: 4,
          id: 5,
          form
        })

        const actual = adapter.getLastRequest()
        expect(actual).toMatchObject({
          method: 'put',
          url: '/api/dws-billings/3/bundles/4/copay-coordinations/5/status'
        })
        expect(JSON.parse(actual.data)).toStrictEqual(form)
      }
    )

    test('API からのレスポンス内容を返す', async () => {
      const response = createDwsBillingCopayCoordinationResponseStub({ id: 789 })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut('/api/dws-billings/7/bundles/8/copay-coordinations/9/status').replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingCopayCoordinations.updateStatus({
        billingId: 7,
        bundleId: 8,
        id: 9,
        form
      })

      expect(actual).toStrictEqual(expected)
    })

    test('API レスポンスのステータスコードが 2xx でない場合は AxiosError を投げる', async () => {
      adapter.setup(x => {
        x.onPut('/api/dws-billings/999/bundles/888/copay-coordinations/777/status').replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingCopayCoordinations.updateStatus({
        billingId: 999,
        bundleId: 888,
        id: 777,
        form
      })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
