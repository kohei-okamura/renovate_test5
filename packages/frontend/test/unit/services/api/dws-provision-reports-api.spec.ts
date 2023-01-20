/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { JobStatus } from '@zinger/enums/lib/job-status'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { createDwsProvisionReportIndexResponseStub } from '~~/stubs/create-dws-provision-report-index-response-stub'
import { createDwsProvisionReportResponseStub } from '~~/stubs/create-dws-provision-report-response-stub'
import { createDwsProvisionReportStub } from '~~/stubs/create-dws-provision-report-stub'
import { createJobResponseStub } from '~~/stubs/create-job-response-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/dws-provision-reports-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  const baseUrl = '/api/dws-provision-reports'
  let dwsProvisionReports: DwsProvisionReportsApi.Definition

  beforeEach(() => {
    dwsProvisionReports = DwsProvisionReportsApi.create(axios)
  })

  describe('getIndex', () => {
    const params: DwsProvisionReportsApi.GetIndexParams = {
      officeId: 1,
      providedIn: '2021-04',
      status: DwsProvisionReportStatus.fixed,
      q: 'キーワード'
    }

    it(`should get ${baseUrl}`, async () => {
      adapter.setup(x => {
        x.onGet(baseUrl).replyOnce(HttpStatusCode.OK, createDwsProvisionReportIndexResponseStub(params))
      })

      await dwsProvisionReports.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url: baseUrl })
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onGet(baseUrl).replyOnce(HttpStatusCode.OK, createDwsProvisionReportIndexResponseStub(params))
      })

      const result = await dwsProvisionReports.getIndex(params)

      expect(result).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(baseUrl).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsProvisionReports.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    const officeId = 2
    const userId = 30
    const providedIn = '2021-04-05'
    const url = `${baseUrl}/${officeId}/${userId}/${providedIn}`
    it('should get /api/dws-provision-reports/:officeId/:userId/:providedIn', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createDwsProvisionReportResponseStub({ providedIn }))
      })

      await dwsProvisionReports.get({ officeId, userId, providedIn })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const response = createDwsProvisionReportResponseStub({ providedIn })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsProvisionReports.get({ officeId, userId, providedIn })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsProvisionReports.get({ officeId, userId, providedIn })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const officeId = 2
    const userId = 30
    const providedIn = '2021-04-05'
    const url = `${baseUrl}/${officeId}/${userId}/${providedIn}`
    const form: DwsProvisionReportsApi.UpdateForm = {
      plans: [],
      results: []
    }

    it('should put /api/dws-provision-reports/:officeId/:userId/:providedIn', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createDwsProvisionReportResponseStub({ providedIn }))
      })

      await dwsProvisionReports.update({ officeId, userId, providedIn, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsProvisionReports.update({ officeId, userId, providedIn, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const response = createDwsProvisionReportResponseStub({ providedIn })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsProvisionReports.update({ officeId, userId, providedIn, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('updateStatus', () => {
    const officeId = 2
    const userId = 30
    const providedIn = '2021-04-05'
    const url = `${baseUrl}/${officeId}/${userId}/${providedIn}/status`
    const form: DwsProvisionReportsApi.UpdateStatusForm = {
      status: DwsProvisionReportStatus.fixed
    }

    it('should put /api/dws-provision-reports/:officeId/:userId/:providedIn/status', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createDwsProvisionReportResponseStub({ providedIn }))
      })

      await dwsProvisionReports.updateStatus({ officeId, userId, providedIn, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = dwsProvisionReports.updateStatus({ officeId, userId, providedIn, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const response = createDwsProvisionReportResponseStub({ providedIn })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await dwsProvisionReports.updateStatus({ officeId, userId, providedIn, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('dws-provision-report-time-summary', () => {
    const url = '/api/dws-provision-report-time-summary'
    const stub = createDwsProvisionReportResponseStub()
    const dwsProvisionReport = createDwsProvisionReportStub({ id: stub.dwsProvisionReport.id })
    const form: DwsProvisionReportsApi.GetTimeSummaryForm = {
      officeId: 1,
      userId: 10,
      providedIn: '2021-01',
      plans: dwsProvisionReport.plans,
      results: dwsProvisionReport.results
    }

    it('should post dws-provision-report-time-summary', async () => {
      adapter.setup(x => x.onPost(url).replyOnce(HttpStatusCode.NoContent))

      await dwsProvisionReports.getTimeSummary({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(deleteUndefinedProperties(form))
    })

    it('should return response of the api', async () => {
      const values = DwsBillingServiceReportAggregateGroup.values
        .filter(x => x !== DwsBillingServiceReportAggregateGroup.accessibleTaxi)
        .map(x => ({ [x]: 1000000 }))
        .reduce((acc, cur) => ({ ...acc, ...cur }), {})
      const expected = {
        plan: values,
        result: values
      }

      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await dwsProvisionReports.getTimeSummary({ form })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsProvisionReports.getTimeSummary({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('dws-service-report-previews', () => {
    const url = '/api/dws-service-report-previews'
    const stub = createDwsProvisionReportResponseStub()
    const dwsProvisionReport = createDwsProvisionReportStub({ id: stub.dwsProvisionReport.id })
    const form: DwsProvisionReportsApi.GetTimeSummaryForm = {
      officeId: 1,
      userId: 10,
      providedIn: '2021-01',
      plans: dwsProvisionReport.plans,
      results: dwsProvisionReport.results
    }

    it('should post dws-service-report-previews', async () => {
      adapter.setup(x => x.onPost(url).replyOnce(HttpStatusCode.Accepted))

      await dwsProvisionReports.downloadPreviews({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
    })

    it('should return response of the api', async () => {
      const response = createJobResponseStub('token', JobStatus.waiting)
      const expected = deleteUndefinedProperties(response)

      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Accepted, response)
      })

      const actual = await dwsProvisionReports.downloadPreviews({ form })

      expect(actual).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = dwsProvisionReports.downloadPreviews({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
