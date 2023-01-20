/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsBillingServiceReportsApi } from '~/services/api/dws-billing-service-reports-api'
import { createDwsBillingServiceReportResponseStub } from '~~/stubs/create-dws-billing-service-report-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/dws-billing-service-reports-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  let dwsBillingServiceReports: DwsBillingServiceReportsApi.Definition

  beforeEach(() => {
    dwsBillingServiceReports = DwsBillingServiceReportsApi.create(axios)
  })

  describe('get', () => {
    it('should get /api/dws-billings/:billingId/bundles/:bundleId/reports/:id', async () => {
      const billingId = 1
      const bundleId = 2
      const id = 3
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/reports/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsBillingServiceReportResponseStub(billingId))
      })

      await dwsBillingServiceReports.get({ billingId, bundleId, id })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const billingId = 4
      const bundleId = 5
      const id = 6
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/reports/${id}`
      const response = createDwsBillingServiceReportResponseStub(billingId)
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingServiceReports.get({ billingId, bundleId, id })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 7
      const bundleId = 8
      const id = 9
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/reports/${id}`
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingServiceReports.get({ billingId, bundleId, id })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('updateStatus', () => {
    const form: DwsBillingServiceReportsApi.UpdateStatusForm = {
      status: DwsBillingStatus.ready
    }

    it('should put /api/dws-billings/:billingId/bundles/:bundleId/reports/:id/status', async () => {
      const billingId = 10
      const bundleId = 11
      const id = 12
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/reports/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createDwsBillingServiceReportResponseStub(billingId))
      })

      await dwsBillingServiceReports.updateStatus({ billingId, bundleId, id, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/reports/${id}/status`
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsBillingServiceReports.updateStatus({ billingId, bundleId, id, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const billingId = 13
      const bundleId = 14
      const id = 15
      const url = `/api/dws-billings/${billingId}/bundles/${bundleId}/reports/${id}/status`
      const response = createDwsBillingServiceReportResponseStub(billingId)
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsBillingServiceReports.updateStatus({ billingId, bundleId, id, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('bulkUpdateStatus', () => {
    const form: DwsBillingServiceReportsApi.BulkUpdateStatusForm = {
      ids: [1, 2, 3, 4, 5],
      status: DwsBillingStatus.fixed
    }
    const billingId = 10
    const url = `/api/dws-billings/${billingId}/service-report-status-update`

    it('should post /api/dws-billings/service-report-status-update', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted)
      })

      await dwsBillingServiceReports.bulkUpdateStatus({ billingId, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsBillingServiceReports.bulkUpdateStatus({ billingId, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
