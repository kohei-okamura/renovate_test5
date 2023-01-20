/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { keys } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import {
  DwsProvisionReportStore,
  useDwsProvisionReportStore
} from '~/composables/stores/use-dws-provision-report-store'
import { usePlugins } from '~/composables/use-plugins'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { DwsProvisionReport } from '~/models/dws-provision-report'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { $datetime } from '~/services/datetime-service'
import { createDwsProvisionReportItemStubs } from '~~/stubs/create-dws-provision-report-item-stub'
import { createDwsProvisionReportResponseStub } from '~~/stubs/create-dws-provision-report-response-stub'
import { createDwsProvisionReportStub } from '~~/stubs/create-dws-provision-report-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-provision-report-store', () => {
  const $api = createMockedApi('dwsProvisionReports')
  const plugins = createMockedPlugins({ $api, $datetime })
  const officeId = 10
  const userId = 78
  const providedIn = '2021-02'
  const params = { officeId, userId, providedIn }
  let store: DwsProvisionReportStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsProvisionReportStore()
    })

    it('should have a value', () => {
      mocked(usePlugins).mockReturnValue(plugins)
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('dwsProvisionReport', () => {
      it('should be ref to undefined', () => {
        expect(store.state.dwsProvisionReport).toBeRef()
        expect(store.state.dwsProvisionReport.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const response = createDwsProvisionReportResponseStub({ providedIn })

    beforeEach(() => {
      jest.spyOn($api.dwsProvisionReports, 'get').mockResolvedValue(response)
      store = useDwsProvisionReportStore()
    })

    afterEach(() => {
      mocked($api.dwsProvisionReports.get).mockReset()
    })

    it('should call $api.dwsProvisionReports.get', async () => {
      await store.get(params)
      expect($api.dwsProvisionReports.get).toHaveBeenCalledTimes(1)
      expect($api.dwsProvisionReports.get).toHaveBeenCalledWith(params)
    })

    it('should update state.dwsProvisionReport', async () => {
      expect(store.state.dwsProvisionReport.value).toBeUndefined()
      await store.get(params)
      expect(store.state.dwsProvisionReport.value).toStrictEqual(response.dwsProvisionReport)
    })
  })

  describe('getLastPlans', () => {
    const lastMonth = $datetime.parse(providedIn).minus({ months: 1 }).toFormat(ISO_MONTH_FORMAT)
    const baseReport = createDwsProvisionReportStub({ providedIn: lastMonth })
    const report: DwsProvisionReport = {
      ...baseReport,
      plans: [1, 4, 5, 6, 8, 12, 13, 16, 18, 27, 28]
        .map(x => {
          const date = `${lastMonth}-${String(x).padStart(2, '0')}`
          return {
            ...baseReport.plans[0],
            schedule: {
              date,
              start: `${date}T22:00:00+0900`,
              end: `${lastMonth}-${String(x + 1).padStart(2, '0')}T03:00:00+0900`
            }
          }
        })
    }
    const response: DwsProvisionReportsApi.GetResponse = { dwsProvisionReport: report }

    beforeEach(() => {
      jest.spyOn($api.dwsProvisionReports, 'get').mockResolvedValue(response)
      store = useDwsProvisionReportStore()
    })

    afterEach(() => {
      mocked($api.dwsProvisionReports.get).mockReset()
    })

    it('should call $api.dwsProvisionReports.get', async () => {
      await store.getLastPlans(params)
      expect($api.dwsProvisionReports.get).toHaveBeenCalledTimes(1)
      expect($api.dwsProvisionReports.get).toHaveBeenCalledWith({ officeId, userId, providedIn: lastMonth })
    })

    it('should return new plans based on last month\'s report', async () => {
      const expected: DwsProvisionReportItem[] = [5, 2, 1, 3, 12, 9, 10, 20, 15, 24, 25]
        .sort((x, y) => x - y)
        .map(x => {
          const date = `${providedIn}-${String(x).padStart(2, '0')}`
          return {
            ...baseReport.plans[0],
            schedule: {
              date,
              start: `${date}T22:00:00+0900`,
              end: `${providedIn}-${String(x + 1).padStart(2, '0')}T03:00:00+0900`
            }
          }
        })
      const actual = await store.getLastPlans(params)
      expect(actual).toStrictEqual(expected)
    })

    it('should throw error if last month\'s report is nothing', async () => {
      jest.spyOn($api.dwsProvisionReports, 'get').mockResolvedValueOnce(undefined!)
      await expect(store.getLastPlans(params)).rejects.toThrow()
    })
  })

  describe('update', () => {
    const current = createDwsProvisionReportResponseStub({ providedIn })
    const dwsProvisionReport = createDwsProvisionReportStub({ id: current.dwsProvisionReport.id })
    const updated = { dwsProvisionReport }
    const form: Parameters<DwsProvisionReportStore['update']>[0]['form'] = {
      plans: createDwsProvisionReportItemStubs(providedIn),
      results: createDwsProvisionReportItemStubs(providedIn)
    }

    beforeAll(() => {
      store = useDwsProvisionReportStore()
      jest.spyOn($api.dwsProvisionReports, 'get').mockResolvedValue(current)
      jest.spyOn($api.dwsProvisionReports, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get(params)
    })

    it('should call $api.dwsProvisionReports.update', async () => {
      await store.update({ form, ...params })
      expect($api.dwsProvisionReports.update).toHaveBeenCalledTimes(1)
      expect($api.dwsProvisionReports.update).toHaveBeenCalledWith({ form, ...params })
    })

    it('should update state.dwsProvisionReport', async () => {
      expect(store.state.dwsProvisionReport.value).toStrictEqual(current.dwsProvisionReport)
      await store.update({ form, ...params })
      expect(store.state.dwsProvisionReport.value).toStrictEqual(updated.dwsProvisionReport)
    })
  })

  describe('updateStatus', () => {
    const current = createDwsProvisionReportResponseStub({ providedIn })
    const dwsProvisionReport = createDwsProvisionReportStub({ id: current.dwsProvisionReport.id })
    const updated = { dwsProvisionReport }
    const form: Parameters<DwsProvisionReportStore['updateStatus']>[0]['form'] = {
      status: DwsProvisionReportStatus.fixed
    }

    beforeAll(() => {
      store = useDwsProvisionReportStore()
      jest.spyOn($api.dwsProvisionReports, 'get').mockResolvedValue(current)
      jest.spyOn($api.dwsProvisionReports, 'updateStatus').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get(params)
    })

    it('should call $api.dwsProvisionReports.updateStatus', async () => {
      await store.updateStatus({ form, ...params })
      expect($api.dwsProvisionReports.updateStatus).toHaveBeenCalledTimes(1)
      expect($api.dwsProvisionReports.updateStatus).toHaveBeenCalledWith({ form, ...params })
    })

    it('should update state.dwsProvisionReport', async () => {
      expect(store.state.dwsProvisionReport.value).toStrictEqual(current.dwsProvisionReport)
      await store.updateStatus({ form, ...params })
      expect(store.state.dwsProvisionReport.value).toStrictEqual(updated.dwsProvisionReport)
    })
  })
})
