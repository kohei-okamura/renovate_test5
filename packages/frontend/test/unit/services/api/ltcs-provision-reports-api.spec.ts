/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HomeHelpServiceSpecifiedOfficeAddition } from '@zinger/enums/lib/home-help-service-specified-office-addition'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import {
  LtcsSpecifiedTreatmentImprovementAddition
} from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import { LtcsTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import Axios from 'axios'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { createLtcsProvisionReportIndexResponseStub } from '~~/stubs/create-ltcs-provision-report-index-response-stub'
import { createLtcsProvisionReportResponseStub } from '~~/stubs/create-ltcs-provision-report-response-stub'
import { createLtcsProvisionReportStub } from '~~/stubs/create-ltcs-provision-report-stub'
import { createAxiosMockAdapter } from '~~/test/helpers/create-axios-mock-adapter'
import { deleteUndefinedProperties } from '~~/test/helpers/delete-undefined-properties'

describe('api/ltcs-provision-reports-api', () => {
  const axios = Axios.create()
  const adapter = createAxiosMockAdapter(axios)
  const baseUrl = '/api/ltcs-provision-reports'
  let ltcsProvisionReports: LtcsProvisionReportsApi.Definition

  const createUrl = ({ officeId, userId, providedIn }: LtcsProvisionReportsApi.GetParams) => {
    return `${baseUrl}/${officeId}/${userId}/${providedIn}`
  }

  beforeEach(() => {
    ltcsProvisionReports = LtcsProvisionReportsApi.create(axios)
  })

  describe('getIndex', () => {
    const params: LtcsProvisionReportsApi.GetIndexParams = {
      officeId: 1,
      providedIn: '2021-04',
      status: LtcsProvisionReportStatus.fixed,
      q: 'キーワード'
    }

    it(`should get ${baseUrl}`, async () => {
      adapter.setup(x => {
        x.onGet(baseUrl).replyOnce(HttpStatusCode.OK, createLtcsProvisionReportIndexResponseStub(params))
      })

      await ltcsProvisionReports.getIndex(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url: baseUrl })
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onGet(baseUrl).replyOnce(HttpStatusCode.OK, createLtcsProvisionReportIndexResponseStub(params))
      })

      const result = await ltcsProvisionReports.getIndex(params)

      expect(result).toMatchSnapshot()
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(baseUrl).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProvisionReports.getIndex(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('get', () => {
    const params = {
      officeId: 1,
      userId: 10,
      providedIn: '2021-01'
    }
    const url = createUrl(params)

    it(`should get ${baseUrl}/:officeId/:userId/:providedIn`, async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, createLtcsProvisionReportResponseStub())
      })

      await ltcsProvisionReports.get(params)

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'get', url })
    })

    it('should return response of the api', async () => {
      const expected = createLtcsProvisionReportResponseStub()

      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsProvisionReports.get(params)

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onGet(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProvisionReports.get(params)

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('update', () => {
    const params = {
      officeId: 1,
      userId: 10,
      providedIn: '2021-01'
    }
    const url = createUrl(params)
    const current = createLtcsProvisionReportResponseStub()
    const ltcsProvisionReport = createLtcsProvisionReportStub({ id: current.ltcsProvisionReport.id })
    const expected = { ltcsProvisionReport }
    const form: LtcsProvisionReportsApi.UpdateForm = {
      entries: ltcsProvisionReport.entries,
      specifiedOfficeAddition: HomeHelpServiceSpecifiedOfficeAddition.addition4,
      treatmentImprovementAddition: LtcsTreatmentImprovementAddition.none,
      specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition.addition2,
      baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition.addition1,
      locationAddition: LtcsOfficeLocationAddition.mountainousArea,
      plan: ltcsProvisionReport.plan,
      result: ltcsProvisionReport.result
    }

    it(`should put ${baseUrl}/:officeId/:userId/:providedIn`, async () => {
      adapter.setup(x => x.onPut(url).replyOnce(HttpStatusCode.NoContent))

      await ltcsProvisionReports.update({ ...params, form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'put', url })
      expect(JSON.parse(request.data)).toStrictEqual(deleteUndefinedProperties(form))
    })

    it('should return response of the api', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsProvisionReports.update({ ...params, form })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProvisionReports.update({ ...params, form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })

  describe('updateStatus', () => {
    const officeId = 2
    const userId = 30
    const providedIn = '2021-04-05'
    const params = {
      officeId,
      userId,
      providedIn
    }
    const url = `${baseUrl}/${officeId}/${userId}/${providedIn}/status`
    const form: LtcsProvisionReportsApi.UpdateStatusForm = {
      status: LtcsProvisionReportStatus.fixed
    }

    it('should put /api/ltcs-provision-reports/:officeId/:userId/:providedIn/status', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, createLtcsProvisionReportResponseStub({ providedIn }))
      })

      await ltcsProvisionReports.updateStatus({ ...params, form })

      const actual = adapter.getLastRequest()
      expect(actual).toMatchObject({ method: 'put', url })
      expect(JSON.parse(actual.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const actual = ltcsProvisionReports.updateStatus({ ...params, form })

      await expect(actual).rejects.toThrowErrorMatchingSnapshot()
    })

    it('should return response of the api', async () => {
      const response = createLtcsProvisionReportResponseStub({ providedIn })
      const expected = deleteUndefinedProperties(response)
      adapter.setup(x => {
        x.onPut(url).replyOnce(HttpStatusCode.OK, response)
      })

      const actual = await ltcsProvisionReports.updateStatus({ ...params, form })

      expect(actual).toStrictEqual(expected)
    })
  })

  describe('ltcs-provision-report-score-summary', () => {
    const url = '/api/ltcs-provision-report-score-summary'
    const stub = createLtcsProvisionReportResponseStub()
    const ltcsProvisionReport = createLtcsProvisionReportStub({ id: stub.ltcsProvisionReport.id })
    const form: LtcsProvisionReportsApi.GetScoreSummaryForm = {
      officeId: 1,
      userId: 10,
      providedIn: '2021-01',
      entries: ltcsProvisionReport.entries,
      specifiedOfficeAddition: HomeHelpServiceSpecifiedOfficeAddition.none,
      treatmentImprovementAddition: LtcsTreatmentImprovementAddition.addition1,
      specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition.none,
      baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition.none,
      locationAddition: LtcsOfficeLocationAddition.none,
      plan: ltcsProvisionReport.plan,
      result: ltcsProvisionReport.result
    }

    it('should post ltcs-provision-report-score-summary', async () => {
      adapter.setup(x => x.onPost(url).replyOnce(HttpStatusCode.NoContent))

      await ltcsProvisionReports.getScoreSummary({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(deleteUndefinedProperties(form))
    })

    it('should return response of the api', async () => {
      const expected = { plan: 10000, result: 10000 }

      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.OK, expected)
      })

      const response = await ltcsProvisionReports.getScoreSummary({ form })

      expect(response).toStrictEqual(deleteUndefinedProperties(expected))
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProvisionReports.getScoreSummary({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
  describe('download sheet', () => {
    const url = '/api/ltcs-provision-report-sheets'
    const form: LtcsProvisionReportsApi.DownloadForm = {
      officeId: 1,
      userId: 10,
      providedIn: '2021-01',
      issuedOn: '2021-02-01',
      needsMaskingInsNumber: true,
      needsMaskingInsName: true
    }
    it('should post ltcs-provision-report-sheets', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.Created)
      })

      await ltcsProvisionReports.downloadSheets({ form })

      const request = adapter.getLastRequest()
      expect(request).toMatchObject({ method: 'post', url })
      expect(JSON.parse(request.data)).toStrictEqual(form)
    })

    it('should throw an AxiosError when failed to request', async () => {
      adapter.setup(x => {
        x.onPost(url).replyOnce(HttpStatusCode.BadRequest)
      })

      const promise = ltcsProvisionReports.downloadSheets({ form })

      await expect(promise).rejects.toThrowErrorMatchingSnapshot()
    })
  })
})
