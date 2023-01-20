/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { LtcsServiceDivisionCode } from '@zinger/enums/lib/ltcs-service-division-code'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'
import { createLtcsBillingStatementResponseStub } from '~~/stubs/create-ltcs-billing-statement-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/ltcs-billing-statements-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let ltcsBillingStatements: LtcsBillingStatementsApi.Definition

  beforeEach(() => {
    ltcsBillingStatements = LtcsBillingStatementsApi.create(axios)
  })

  describe('get', () => {
    it('should get /api/ltcs-billings/:billingId/bundles/:bundleId/statements/:id', async () => {
      const billingId = 1
      const bundleId = 2
      const id = 3
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await ltcsBillingStatements.get({ billingId, bundleId, id })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const billingId = 4
      const bundleId = 5
      const id = 6
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      const response = createLtcsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await ltcsBillingStatements.get({ billingId, bundleId, id })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 7
      const bundleId = 8
      const id = 9
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillingStatements.get({ billingId, bundleId, id })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const aggregates = [{
      serviceDivisionCode: LtcsServiceDivisionCode.homeVisitLongTermCare,
      plannedScore: 3000
    }]
    const form: LtcsBillingStatementsApi.UpdateForm = {
      aggregates
    }

    it('should put /api/ltcs-billings/:billingId/bundles/:bundleId/statements/:id', async () => {
      const billingId = 10
      const bundleId = 11
      const id = 12
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createLtcsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await ltcsBillingStatements.update({ billingId, bundleId, id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillingStatements.update({ billingId, bundleId, id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      const response = createLtcsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await ltcsBillingStatements.update({ billingId, bundleId, id, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('updateStatus', () => {
    const form: LtcsBillingStatementsApi.UpdateStatusForm = {
      status: LtcsBillingStatus.fixed
    }

    it('should put /api/ltcs-billings/:billingId/bundles/:bundleId/statements/:id/status', async () => {
      const billingId = 10
      const bundleId = 11
      const id = 12
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createLtcsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await ltcsBillingStatements.updateStatus({ billingId, bundleId, id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsBillingStatements.updateStatus({ billingId, bundleId, id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/${id}/status`
      const response = createLtcsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await ltcsBillingStatements.updateStatus({ billingId, bundleId, id, form })

      expect(actual).toStrictEqual(expected)
    })
  })
  describe('bulkUpdateStatus', () => {
    const form: LtcsBillingStatementsApi.BulkUpdateStatusForm = {
      ids: [1, 2, 3, 4, 5],
      status: LtcsBillingStatus.fixed
    }
    const billingId = 10
    const bundleId = 11
    const url = `/api/ltcs-billings/${billingId}/bundles/${bundleId}/statements/bulk-status`

    it('should post /api/ltcs-billings/:billingId/bundles/:bundleId/statements/bulk-status', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted)
      })

      await ltcsBillingStatements.bulkUpdateStatus({ billingId, bundleId, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsBillingStatements.bulkUpdateStatus({ billingId, bundleId, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
  describe('refresh', () => {
    const form: LtcsBillingStatementsApi.RefreshForm = {
      ids: [1, 2, 3, 4, 5]
    }
    const billingId = 10
    const url = `/api/ltcs-billings/${billingId}/statement-refresh`

    it('should post /api/ltcs-billings/:billingId/statement-refresh', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted)
      })

      await ltcsBillingStatements.refresh({ billingId, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsBillingStatements.refresh({ billingId, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
