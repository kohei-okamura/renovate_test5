/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingStatementCopayCoordinationStatus } from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { createDwsBillingStatementAggregateStubs } from '~~/stubs/create-dws-billing-statement-aggregate-stub'
import { createDwsBillingStatementResponseStub } from '~~/stubs/create-dws-billing-statement-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/dws-billing-statements-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsBillingStatements: DwsBillingStatementsApi.Definition

  beforeEach(() => {
    dwsBillingStatements = DwsBillingStatementsApi.create(axios)
  })

  describe('get', () => {
    it('should get /api/dws-billings/:billingId/bundles/:bundleId/statements/:id', async () => {
      const billingId = 1
      const bundleId = 2
      const id = 3
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await dwsBillingStatements.get({ billingId, bundleId, id })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const billingId = 4
      const bundleId = 5
      const id = 6
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      const response = createDwsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingStatements.get({ billingId, bundleId, id })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 7
      const bundleId = 8
      const id = 9
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingStatements.get({ billingId, bundleId, id })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const aggregates = createDwsBillingStatementAggregateStubs()
      .map(({
        serviceDivisionCode,
        managedCopay,
        subtotalSubsidy
      }) => ({
        serviceDivisionCode,
        managedCopay,
        subtotalSubsidy
      }))
    const form: DwsBillingStatementsApi.UpdateForm = {
      aggregates
    }

    it('should put /api/dws-billings/:billingId/bundles/:bundleId/statements/:id', async () => {
      const billingId = 10
      const bundleId = 11
      const id = 12
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createDwsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await dwsBillingStatements.update({ billingId, bundleId, id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingStatements.update({ billingId, bundleId, id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}`
      const response = createDwsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingStatements.update({ billingId, bundleId, id, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('updateCopayCoordination', () => {
    const form: DwsBillingStatementsApi.UpdateCopayCoordinationForm = {
      result: CopayCoordinationResult.coordinated,
      amount: 1000
    }

    it('should put /api/dws-billings/:billingId/bundles/:bundleId/statements/:id/copay-coordination', async () => {
      const billingId = 10
      const bundleId = 11
      const id = 12
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/copay-coordination`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createDwsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await dwsBillingStatements.updateCopayCoordination({ billingId, bundleId, id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/copay-coordination`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingStatements.updateCopayCoordination({ billingId, bundleId, id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/copay-coordination`
      const response = createDwsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingStatements.updateCopayCoordination({ billingId, bundleId, id, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('updateStatus', () => {
    const form: DwsBillingStatementsApi.UpdateStatusForm = {
      status: DwsBillingStatus.ready
    }

    it('should put /api/dws-billings/:billingId/bundles/:bundleId/statements/:id/status', async () => {
      const billingId = 10
      const bundleId = 11
      const id = 12
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createDwsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await dwsBillingStatements.updateStatus({ billingId, bundleId, id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingStatements.updateStatus({ billingId, bundleId, id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/status`
      const response = createDwsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingStatements.updateStatus({ billingId, bundleId, id, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('updateCopayCoordinationStatus', () => {
    const form: DwsBillingStatementsApi.UpdateCopayCoordinationStatusForm = {
      status: DwsBillingStatementCopayCoordinationStatus.unclaimable
    }

    it('should put /api/dws-billings/:billingId/bundles/:bundleId/statements/:id/copay-coordination-status', async () => {
      const billingId = 10
      const bundleId = 11
      const id = 12
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/copay-coordination-status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createDwsBillingStatementResponseStub({ billingId, bundleId, id }))
      })

      await dwsBillingStatements.updateCopayCoordinationStatus({ billingId, bundleId, id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/copay-coordination-status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingStatements.updateCopayCoordinationStatus({ billingId, bundleId, id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/statements/${id}/copay-coordination-status`
      const response = createDwsBillingStatementResponseStub({ billingId, bundleId, id })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingStatements.updateCopayCoordinationStatus({ billingId, bundleId, id, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('bulkUpdateStatus', () => {
    const form: DwsBillingStatementsApi.BulkUpdateStatusForm = {
      ids: [1, 2, 3, 4, 5],
      status: DwsBillingStatus.fixed
    }
    const billingId = 10
    const url = `/api/dws-billings/${billingId}/statement-status-update`

    it('should post /api/dws-billings/:billingId/statement-status-update', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted)
      })

      await dwsBillingStatements.bulkUpdateStatus({ billingId, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsBillingStatements.bulkUpdateStatus({ billingId, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('refresh', () => {
    const form: DwsBillingStatementsApi.RefreshForm = {
      ids: [1, 2, 3, 4, 5]
    }
    const billingId = 10
    const url = `/api/dws-billings/${billingId}/statement-refresh`

    it('should post /api/dws-billings/:billingId/statement-refresh', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted)
      })

      await dwsBillingStatements.refresh({ billingId, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsBillingStatements.refresh({ billingId, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
