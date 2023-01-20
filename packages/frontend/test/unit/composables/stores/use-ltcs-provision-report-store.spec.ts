/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProvisionReportStatus } from '@zinger/enums/lib/ltcs-provision-report-status'
import { keys } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import {
  LtcsProvisionReportStore,
  useLtcsProvisionReportStore
} from '~/composables/stores/use-ltcs-provision-report-store'
import { usePlugins } from '~/composables/use-plugins'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { LtcsProvisionReport } from '~/models/ltcs-provision-report'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { $datetime } from '~/services/datetime-service'
import { createLtcsProvisionReportEntryStubs } from '~~/stubs/create-ltcs-provision-report-entry-stub'
import { createLtcsProvisionReportResponseStub } from '~~/stubs/create-ltcs-provision-report-response-stub'
import { createLtcsProvisionReportStub } from '~~/stubs/create-ltcs-provision-report-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-provision-report-store', () => {
  const $api = createMockedApi('ltcsProvisionReports')
  const plugins = createMockedPlugins({ $api, $datetime })
  const officeId = 10
  const userId = 78
  const providedIn = '2021-02'
  const params = { officeId, userId, providedIn }
  let store: LtcsProvisionReportStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsProvisionReportStore()
    })

    it('should have a value', () => {
      mocked(usePlugins).mockReturnValue(plugins)
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('ltcsProvisionReport', () => {
      it('should be ref to undefined', () => {
        expect(store.state.ltcsProvisionReport).toBeRef()
        expect(store.state.ltcsProvisionReport.value).toBeUndefined()
      })
    })
  })

  describe('getLastPlans', () => {
    const lastMonth = $datetime.parse(providedIn).minus({ months: 1 }).toFormat(ISO_MONTH_FORMAT)
    const baseReport = createLtcsProvisionReportStub({ providedIn: lastMonth })
    const report: LtcsProvisionReport = {
      ...baseReport,
      entries: [
        {
          ...baseReport.entries[0],
          plans: ['01', '05', '07', '10', '18', '21', '30'].map(x => `${lastMonth}-${x}`)
        },
        {
          ...baseReport.entries[1],
          plans: []
        },
        {
          ...baseReport.entries[2],
          plans: ['04', '06', '08', '12', '13', '16', '18', '27', '28'].map(x => `${lastMonth}-${x}`)
        }
      ]
    }
    const response: LtcsProvisionReportsApi.GetResponse = { ltcsProvisionReport: report }

    beforeEach(() => {
      jest.spyOn($api.ltcsProvisionReports, 'get').mockResolvedValue(response)
      store = useLtcsProvisionReportStore()
    })

    afterEach(() => {
      mocked($api.ltcsProvisionReports.get).mockReset()
    })

    it('should call $api.ltcsProvisionReports.get', async () => {
      await store.getLastPlans(params)
      expect($api.ltcsProvisionReports.get).toHaveBeenCalledTimes(1)
      expect($api.ltcsProvisionReports.get).toHaveBeenCalledWith({ officeId, userId, providedIn: lastMonth })
    })

    it('should return new entries based on last month\'s report', async () => {
      const expected: LtcsProvisionReportEntry[] = [
        {
          ...baseReport.entries[0],
          plans: ['05', '02', '04', '14', '15', '18'].map(x => `${providedIn}-${x}`).sort(),
          results: []
        },
        {
          ...baseReport.entries[2],
          plans: ['01', '03', '12', '09', '10', '20', '15', '24', '25'].map(x => `${providedIn}-${x}`).sort(),
          results: []
        }
      ]
      const actual = await store.getLastPlans(params)
      expect(expected).toStrictEqual(actual)
    })

    it('should throw error if last month\'s report is nothing', async () => {
      jest.spyOn($api.ltcsProvisionReports, 'get').mockResolvedValueOnce(undefined!)
      await expect(store.getLastPlans(params)).rejects.toThrow()
    })
  })

  describe('update', () => {
    const current = createLtcsProvisionReportResponseStub({ providedIn })
    const ltcsProvisionReport = createLtcsProvisionReportStub({ id: current.ltcsProvisionReport.id })
    const updated = { ltcsProvisionReport }
    const form: Parameters<LtcsProvisionReportStore['update']>[0]['form'] = {
      entries: createLtcsProvisionReportEntryStubs(providedIn)
    }

    beforeAll(() => {
      store = useLtcsProvisionReportStore()
      jest.spyOn($api.ltcsProvisionReports, 'get').mockResolvedValue(current)
      jest.spyOn($api.ltcsProvisionReports, 'update').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get(params)
    })

    it('should call $api.ltcsProvisionReports.update', async () => {
      await store.update({ form, ...params })
      expect($api.ltcsProvisionReports.update).toHaveBeenCalledTimes(1)
      expect($api.ltcsProvisionReports.update).toHaveBeenCalledWith({ form, ...params })
    })

    it('should update state.ltcsProvisionReport', async () => {
      expect(store.state.ltcsProvisionReport.value).toStrictEqual(current.ltcsProvisionReport)
      await store.update({ form, ...params })
      expect(store.state.ltcsProvisionReport.value).toStrictEqual(updated.ltcsProvisionReport)
    })
  })

  describe('updateStatus', () => {
    const current = createLtcsProvisionReportResponseStub({ providedIn })
    const ltcsProvisionReport = createLtcsProvisionReportStub({ id: current.ltcsProvisionReport.id })
    const updated = { ltcsProvisionReport }
    const form: Parameters<LtcsProvisionReportStore['updateStatus']>[0]['form'] = {
      status: LtcsProvisionReportStatus.fixed
    }

    beforeAll(() => {
      store = useLtcsProvisionReportStore()
      jest.spyOn($api.ltcsProvisionReports, 'get').mockResolvedValue(current)
      jest.spyOn($api.ltcsProvisionReports, 'updateStatus').mockResolvedValue(updated)
    })

    beforeEach(async () => {
      await store.get(params)
    })

    it('should call $api.ltcsProvisionReports.updateStatus', async () => {
      await store.updateStatus({ form, ...params })
      expect($api.ltcsProvisionReports.updateStatus).toHaveBeenCalledTimes(1)
      expect($api.ltcsProvisionReports.updateStatus).toHaveBeenCalledWith({ form, ...params })
    })

    it('should update state.ltcsProvisionReport', async () => {
      expect(store.state.ltcsProvisionReport.value).toStrictEqual(current.ltcsProvisionReport)
      await store.updateStatus({ form, ...params })
      expect(store.state.ltcsProvisionReport.value).toStrictEqual(updated.ltcsProvisionReport)
    })
  })
})
