/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'
import {
  DwsProvisionReportStatus,
  resolveDwsProvisionReportStatus
} from '@zinger/enums/lib/dws-provision-report-status'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { DateTime } from 'luxon'
import Vue from 'vue'
import { colors } from '~/colors'
import {
  DwsProvisionReportData,
  dwsProvisionReportStateKey,
  DwsProvisionReportStore,
  dwsProvisionReportStoreKey
} from '~/composables/stores/use-dws-provision-report-store'
import { dwsProvisionReportsStateKey } from '~/composables/stores/use-dws-provision-reports-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { ISO_DATETIME_FORMAT, ISO_MONTH_FORMAT, OLDEST_DATE } from '~/models/date'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsProvisionReportPage from '~/pages/dws-provision-reports/_officeId/_userId/_providedIn/index.vue'
import { AlertService } from '~/services/alert-service'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { $datetime } from '~/services/datetime-service'
import { DownloadService } from '~/services/download-service'
import { SnackbarService } from '~/services/snackbar-service'
import { RouteQuery } from '~/support/router/types'
import { createDwsProvisionReportDigestStubs } from '~~/stubs/create-dws-provision-report-digest-stub'
import {
  createDwsProvisionReportItemStub,
  createDwsProvisionReportItemStubs
} from '~~/stubs/create-dws-provision-report-item-stub'
import { createDwsProvisionReportStoreStub } from '~~/stubs/create-dws-provision-report-store-stub'
import { createDwsProvisionReportStub } from '~~/stubs/create-dws-provision-report-stub'
import { createDwsProvisionReportsStoreStub } from '~~/stubs/create-dws-provision-reports-store-stub'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

type RouteParams = {
  officeId?: string
  userId?: string
  providedIn?: string
}

jest.mock('~/composables/use-job-with-notification')
jest.mock('~/composables/use-offices')

describe('pages/dws-provision-reports/_officeId/_userId/_providedIn/index.vue', () => {
  const defaultRouteParams: Required<RouteParams> = {
    officeId: '10',
    userId: '20',
    providedIn: '2021-02'
  }
  const { mount, shallowMount } = setupComponentTest()
  const $api = createMockedApi('dwsProvisionReports')
  const $alert = createMock<AlertService>()
  const $confirm = createMock<ConfirmDialogService>()
  const $download = createMock<DownloadService>()
  const $form = createMockedFormService()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()
  const $back = createMockedBack()
  const dwsProvisionReport = createDwsProvisionReportStub()
  const dwsProvisionReports = createDwsProvisionReportDigestStubs(20)
  const dwsProvisionReportsStore = createDwsProvisionReportsStoreStub({ dwsProvisionReports })
  const userIds = dwsProvisionReports.map(x => x.userId.toString())
  const userStore = createUserStoreStub(createUserResponseStub(+userIds[0]))
  let wrapper: Wrapper<Vue & any>
  let store: DwsProvisionReportStore

  type MountComponentParams = {
    auth?: Partial<Auth>
    params?: RouteParams
    query?: RouteQuery
    reportData?: Partial<DwsProvisionReportData['dwsProvisionReport']>
  }

  async function mountComponent ({ auth, params, query, reportData }: MountComponentParams = {}, shallow = true) {
    const fn = shallow ? shallowMount : mount
    const $routes = createMockedRoutes({ query: query ?? {} })
    const $route = createMockedRoute({ params: { ...defaultRouteParams, ...params } })
    store = createDwsProvisionReportStoreStub({
      dwsProvisionReport: {
        ...dwsProvisionReport,
        ...(reportData ?? {})
      }
    })
    wrapper = fn(DwsProvisionReportPage, {
      ...provides(
        [dwsProvisionReportStoreKey, store],
        [dwsProvisionReportStateKey, store.state],
        [dwsProvisionReportsStateKey, dwsProvisionReportsStore.state],
        [userStateKey, userStore.state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      ),
      mocks: {
        $alert,
        $api,
        $back,
        $confirm,
        $download,
        $form,
        $router,
        $routes,
        $route,
        $snackbar
      },
      stubs: [
        'z-dws-provision-report-item-browsing-dialog',
        'z-dws-provision-report-item-form-dialog',
        'z-user-card'
      ]
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  const timeSummary = DwsBillingServiceReportAggregateGroup.values
    .filter(x => x !== DwsBillingServiceReportAggregateGroup.accessibleTaxi)
    .map(x => ({ [x]: 1000000 }))
    .reduce((acc, cur) => ({ ...acc, ...cur }), {})

  beforeAll(() => {
    mocked(execute).mockImplementation(async ({ process }) => {
      await process()
    })
    mocked(useJobWithNotification).mockReturnValue({ execute })
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    jest.spyOn($api.dwsProvisionReports, 'getTimeSummary').mockResolvedValue({
      plan: timeSummary,
      result: timeSummary
    })
    // このファイルのテストでは verifyBeforeLeaving の処理は重要ではないため、何もせずに関数を実行する
    jest.spyOn($form, 'verifyBeforeLeaving').mockImplementation(async next => await next())
  })

  afterAll(() => {
    mocked($form.verifyBeforeLeaving).mockRestore()
    mocked($api.dwsProvisionReports.getTimeSummary).mockRestore()
    mocked(useOffices).mockRestore()
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  describe('initial view', () => {
    it('should be rendered correctly', async () => {
      await mountComponent()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered office link if having permission', async () => {
      await mountComponent()
      expect(wrapper.find('[data-office-abbr] a').exists()).toBeTrue()
      unmountComponent()
    })

    it('should not be rendered office link if have not permission', async () => {
      await mountComponent({ auth: {} })
      expect(wrapper.find('[data-office-abbr] a').exists()).toBeFalse()
      unmountComponent()
    })

    it('should be disabled previous month button if the current is the oldest month', async () => {
      const providedIn = DateTime.fromISO(OLDEST_DATE).toFormat(ISO_MONTH_FORMAT)
      const reportData = createDwsProvisionReportStub({ providedIn })
      await mountComponent({
        reportData,
        params: { providedIn }
      })
      const button = wrapper.find('[data-prev-month]')
      expect(button).toBeDisabled()
    })

    it('should be disabled next month if the current is 3 months from now', async () => {
      const providedIn = DateTime.local().plus({ months: 3 }).toFormat(ISO_MONTH_FORMAT)
      const reportData = createDwsProvisionReportStub({ providedIn })
      await mountComponent({
        reportData,
        params: { providedIn }
      })
      const button = wrapper.find('[data-next-month]')
      expect(button).toBeDisabled()
      unmountComponent()
    })

    it('should be disabled previous user if the current is the first user', async () => {
      await mountComponent({
        params: {
          userId: userIds[0]
        }
      })
      const button = wrapper.find('[data-prev-user]')
      expect(button).toBeDisabled()
      unmountComponent()
    })

    it('should be disabled next user if the current is the last user', async () => {
      await mountComponent({
        params: {
          userId: userIds[userIds.length - 1]
        }
      })
      const button = wrapper.find('[data-next-user]')
      expect(button).toBeDisabled()
      unmountComponent()
    })

    it('should be disabled save and confirm if it does not have entry', async () => {
      const reportData = {
        plans: [],
        results: []
      }
      await mountComponent({ reportData })
      expect(wrapper.find('[data-save]')).toBeDisabled()
      expect(wrapper.find('[data-confirm]')).toBeDisabled()
      unmountComponent()
    })

    it('should be rendered "dwsProvisionReportItemFormDialog" if status is not fixed', async () => {
      await mountComponent()
      expect(wrapper.findComponent({ ref: 'dwsProvisionReportItemBrowsingDialog' })).not.toExist()
      expect(wrapper.findComponent({ ref: 'dwsProvisionReportItemFormDialog' })).toExist()
      unmountComponent()
    })

    describe('when provision report is fixed', () => {
      beforeAll(async () => {
        const providedIn = defaultRouteParams.providedIn
        const report = createDwsProvisionReportItemStub(providedIn)
        const reportData = {
          plans: [report],
          results: [report],
          status: DwsProvisionReportStatus.fixed
        }
        await mountComponent({ reportData }, false)
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should not be able to edit', () => {
        expect(wrapper.find('[data-add-plan]')).toBeDisabled()
        expect(wrapper.find('[data-add-result]')).toBeDisabled()
        expect(wrapper.find('[data-copy-plans]')).toBeDisabled()
        expect(wrapper.find('[data-save]')).toBeDisabled()
        expect(wrapper).toMatchSnapshot()
      })

      it('should be rendered "dwsProvisionReportItemBrowsingDialog"', () => {
        expect(wrapper.findComponent({ ref: 'dwsProvisionReportItemBrowsingDialog' })).toExist()
        expect(wrapper.findComponent({ ref: 'dwsProvisionReportItemFormDialog' })).not.toExist()
      })
    })
  })

  describe('routing', () => {
    beforeAll(() => {
      jest.spyOn($router, 'push')
    })

    afterAll(() => {
      mocked($router.push).mockRestore()
    })

    const createPath = ({ userId, providedIn }: Omit<RouteParams, 'officeId'>) => {
      const toUserId = userId ?? defaultRouteParams.userId
      const toProvidedIn = providedIn ?? defaultRouteParams.providedIn
      return `/dws-provision-reports/${defaultRouteParams.officeId}/${toUserId}/${toProvidedIn}`
    }

    it('should move to selected month when month changed', async () => {
      const currentMonth = '2021-03'
      const reportData = createDwsProvisionReportStub({ providedIn: currentMonth })
      await mountComponent({
        reportData,
        params: {
          providedIn: currentMonth
        }
      })
      const providedIn = '2020-12'
      const select = wrapper.findComponent({ ref: 'selectMonth' })
      await select.vm.$emit('input', providedIn)
      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ providedIn }))
    })

    it('should move to previous month when previous month button clicked', async () => {
      const currentMonth = '2021-03'
      const reportData = createDwsProvisionReportStub({ providedIn: currentMonth })
      await mountComponent({
        reportData,
        params: {
          providedIn: currentMonth
        }
      })
      const button = wrapper.find('[data-prev-month]')
      await click(() => button)
      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ providedIn: '2021-02' }))
    })

    it('should move to next month when next month button clicked', async () => {
      const currentMonth = '2021-03'
      const reportData = createDwsProvisionReportStub({ providedIn: currentMonth })
      await mountComponent({
        reportData,
        params: {
          providedIn: currentMonth
        }
      })
      const button = wrapper.find('[data-next-month]')
      await click(() => button)
      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ providedIn: '2021-04' }))
    })

    it('should move to selected user when user changed', async () => {
      await mountComponent({
        params: {
          userId: userIds[1]
        }
      })
      const userId = userIds[5]
      const select = wrapper.findComponent({ ref: 'selectUser' })
      await select.vm.$emit('input', userId)
      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ userId }))
    })

    it('should move to previous user when previous user button clicked', async () => {
      await mountComponent({
        params: {
          userId: userIds[1]
        }
      })
      const button = wrapper.find('[data-prev-user]')
      await click(() => button)
      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ userId: userIds[0] }))
    })

    it('should move to next user when next user button clicked', async () => {
      await mountComponent({
        params: {
          userId: userIds[1]
        }
      })
      const button = wrapper.find('[data-next-user]')
      await click(() => button)
      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ userId: userIds[2] }))
    })
  })

  describe('validation', () => {
    const providedIn = defaultRouteParams.providedIn
    const date = `${providedIn}-01`
    const datetime = $datetime.parse(date)
    const start = datetime.plus({ hours: 2 })
    const end = start.plus({ hours: 2 })
    const report = {
      ...createDwsProvisionReportItemStub(providedIn),
      schedule: {
        date,
        start: start.toFormat(ISO_DATETIME_FORMAT),
        end: end.toFormat(ISO_DATETIME_FORMAT)
      }
    }
    const baseReport2 = createDwsProvisionReportItemStub(providedIn, 2)

    const createKey = (item: DwsProvisionReportItem) => {
      return JSON.stringify({ schedule: item.schedule })
    }

    async function localMount (reportData: Partial<DwsProvisionReportData['dwsProvisionReport']>) {
      await mountComponent({ reportData })
    }

    beforeAll(() => {
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn($snackbar, 'warning').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.warning).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.error).mockClear()
      mocked($snackbar.warning).mockClear()
    })

    describe('overlapped plan', () => {
      it('should not fail when edit itself', async () => {
        const updated = {
          ...report,
          schedule: {
            date,
            start: start.plus({ minutes: 30 }).toFormat(ISO_DATETIME_FORMAT),
            end: end.plus({ minutes: 30 }).toFormat(ISO_DATETIME_FORMAT)
          }
        }
        const key = createKey(report)
        await localMount({ plans: [report] })

        await wrapper.vm.editPlan(key)
        await wrapper.vm.storeItem({ key, item: updated })

        expect($snackbar.warning).not.toHaveBeenCalled()
        unmountComponent()
      })

      it('should fail if the new plan\'s schedule is overlapped with other plans', async () => {
        const report2 = {
          ...baseReport2,
          schedule: {
            date,
            start: start.plus({ hours: 1 }).toFormat(ISO_DATETIME_FORMAT),
            end: end.toFormat(ISO_DATETIME_FORMAT)
          }
        }
        await localMount({ plans: [report] })

        await wrapper.vm.addPlan()
        await wrapper.vm.storeItem({ item: report2 })

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('提供時間が重複している予定があるため追加できません。')
        unmountComponent()
      })

      it('should fail if the new plan\'s schedule is duplicated with other plans', async () => {
        const report2 = {
          ...baseReport2,
          schedule: {
            date,
            start: start.toFormat(ISO_DATETIME_FORMAT),
            end: end.toFormat(ISO_DATETIME_FORMAT)
          }
        }
        await localMount({ plans: [report] })

        await wrapper.vm.addPlan()
        await wrapper.vm.storeItem({ item: report2 })

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('提供時間が完全に一致する予定があるため追加できません。')
        unmountComponent()
      })

      it('should fail if the new result\'s schedule is overlapped with other results', async () => {
        const report2 = {
          ...baseReport2,
          schedule: {
            date,
            start: start.plus({ hours: 1 }).toFormat(ISO_DATETIME_FORMAT),
            end: end.toFormat(ISO_DATETIME_FORMAT)
          }
        }
        await localMount({ results: [report] })

        await wrapper.vm.addResult()
        await wrapper.vm.storeItem({ item: report2 })

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('提供時間が重複している実績があるため追加できません。')
        unmountComponent()
      })

      it('should fail if the new result\'s schedule is duplicated with other results', async () => {
        const report2 = {
          ...baseReport2,
          schedule: {
            date,
            start: start.toFormat(ISO_DATETIME_FORMAT),
            end: end.toFormat(ISO_DATETIME_FORMAT)
          }
        }
        await localMount({ results: [report] })

        await wrapper.vm.addResult()
        await wrapper.vm.storeItem({ item: report2 })

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('提供時間が完全に一致する実績があるため追加できません。')
        unmountComponent()
      })

      it('should fail if the edited plan\'s schedule is overlapped with other plans', async () => {
        const report2 = {
          ...baseReport2,
          schedule: {
            date,
            start: start.plus({ hours: 4 }).toFormat(ISO_DATETIME_FORMAT),
            end: end.plus({ hours: 30 }).toFormat(ISO_DATETIME_FORMAT)
          }
        }
        const updated = {
          ...report2,
          schedule: {
            ...report2.schedule,
            start: start.minus({ hours: 3 }).toFormat(ISO_DATETIME_FORMAT)
          }
        }
        const key = createKey(report2)
        await localMount({ plans: [report, report2] })

        await wrapper.vm.editPlan(key)
        await wrapper.vm.storeItem({ key, item: updated })

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('提供時間が重複している予定があるため更新できません。')
        unmountComponent()
      })

      it('should fail if the edited result\'s schedule is overlapped with other results', async () => {
        const report2 = {
          ...baseReport2,
          schedule: {
            date,
            start: start.plus({ hours: 4 }).toFormat(ISO_DATETIME_FORMAT),
            end: end.plus({ hours: 30 }).toFormat(ISO_DATETIME_FORMAT)
          }
        }
        const updated = {
          ...report2,
          schedule: {
            ...report2.schedule,
            start: start.minus({ hours: 3 }).toFormat(ISO_DATETIME_FORMAT)
          }
        }
        const key = createKey(report2)
        await localMount({ results: [report, report2] })

        await wrapper.vm.editResult(key)
        await wrapper.vm.storeItem({ key, item: updated })

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('提供時間が重複している実績があるため更新できません。')
        unmountComponent()
      })

      it('should fail if the results have data that is overlapped with the plan to copy', async () => {
        const report2 = {
          ...baseReport2,
          schedule: {
            date,
            start: start.toFormat(ISO_DATETIME_FORMAT),
            end: end.plus({ hours: 1 }).toFormat(ISO_DATETIME_FORMAT)
          }
        }
        await localMount({ plans: [report], results: [report2] })

        await wrapper.vm.copyPlanToResult(createKey(report))

        expect($snackbar.warning).toHaveBeenCalledTimes(1)
        expect($snackbar.warning).toHaveBeenCalledWith('提供時間が重複している実績があるためコピーできません。')
        unmountComponent()
      })
    })
  })

  describe('action', () => {
    const officeId = parseInt(defaultRouteParams.officeId)
    const userId = parseInt(defaultRouteParams.userId)
    const providedIn = defaultRouteParams.providedIn

    beforeAll(() => {
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
    })

    describe('update dws provision report', () => {
      async function localMount (reportData: Partial<DwsProvisionReportData['dwsProvisionReport']>) {
        await mountComponent({ reportData })
        // FYI mountComponent で store を作っているため、mount 後に行う必要がある
        jest.spyOn(store, 'update')
      }

      function localUnmount () {
        mocked(store.update).mockRestore()
        unmountComponent()
      }

      it('should call store.update when save button clicked', async () => {
        const items = createDwsProvisionReportItemStubs(providedIn)
        const form = {
          plans: items,
          results: items
        }
        await localMount({ plans: items, results: items })

        await click(() => wrapper.find('[data-save]'))

        expect(store.update).toHaveBeenCalledTimes(1)
        expect(store.update).toHaveBeenCalledWith({ officeId, userId, providedIn, form })
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('障害福祉サービス予実を保存しました。')
        localUnmount()
      })

      it('should display error if server response is 400 Bad Request when called store.update', async () => {
        const items = createDwsProvisionReportItemStubs(providedIn)
        await localMount({ plans: items, results: items })
        mocked(store.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            plans: '入力してください。',
            results: '入力してください。'
          }
        }))

        // isEditingをtrueにしたいので、適当に編集する.
        wrapper.vm.rows[0].plan.movingDurationMinutes = 999
        await click(() => wrapper.find('[data-save]'))
        expect(store.update).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('障害福祉サービス予実の保存に失敗しました。')
        localUnmount()
      })
    })

    describe('update dws provision report status', () => {
      async function localMount (reportData: Partial<DwsProvisionReportData['dwsProvisionReport']>) {
        await mountComponent({ reportData })
        jest.spyOn(store, 'updateStatus')
      }

      function localUnmount () {
        mocked(store.updateStatus).mockRestore()
        unmountComponent()
      }

      it('should call store.updateStatus when remand button clicked', async () => {
        const status = DwsProvisionReportStatus.inProgress
        const label = resolveDwsProvisionReportStatus(status)
        const form = { status }
        await localMount({ status: DwsProvisionReportStatus.fixed, plans: [], results: [] })

        await click(() => wrapper.find('[data-remand]'))

        expect(store.updateStatus).toHaveBeenCalledTimes(1)
        expect(store.updateStatus).toHaveBeenCalledWith({ officeId, userId, providedIn, form })
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith(`障害福祉サービス予実の状態を${label}に変更しました。`)
        localUnmount()
      })

      it('should call store.updateStatus when confirm button clicked', async () => {
        const status = DwsProvisionReportStatus.fixed
        const label = resolveDwsProvisionReportStatus(status)
        const form = { status }
        await localMount({ status: DwsProvisionReportStatus.inProgress, plans: [], results: [] })

        await click(() => wrapper.find('[data-confirm]'))

        expect(store.updateStatus).toHaveBeenCalledTimes(1)
        expect(store.updateStatus).toHaveBeenCalledWith({ officeId, userId, providedIn, form })
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith(`障害福祉サービス予実の状態を${label}に変更しました。`)
        localUnmount()
      })

      it('should display error if server response is 400 Bad Request when called store.updateStatus', async () => {
        await localMount({ status: DwsProvisionReportStatus.fixed, plans: [], results: [] })
        mocked(store.updateStatus).mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            userId: ['契約に初回サービス提供日が設定されていないため確定できません。']
          }
        }))

        await click(() => wrapper.find('[data-remand]'))

        const errorText = wrapper.find('[data-errors]').text()
        expect(errorText).toContain('契約に初回サービス提供日が設定されていないため確定できません。')
        expect(store.updateStatus).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('障害福祉サービス予実の状態変更に失敗しました。')
        localUnmount()
      })
    })

    describe('copy plans from last month', () => {
      async function localMount (reportData: Partial<DwsProvisionReportData['dwsProvisionReport']>) {
        await mountComponent({ reportData })
        jest.spyOn(store, 'getLastPlans')
      }

      function localUnmount () {
        mocked(store.getLastPlans).mockRestore()
        unmountComponent()
      }

      beforeEach(() => {
        jest.spyOn($confirm, 'show').mockResolvedValue(true)
      })

      afterEach(() => {
        mocked($confirm.show).mockRestore()
      })

      it('should show confirm dialog', async () => {
        await localMount({ status: DwsProvisionReportStatus.notCreated })

        await click(() => wrapper.find('[data-copy-plans]'))

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          message: '前月から予定をコピーします。現在入力されている予定・実績はすべて消去されます。\n\nよろしいですか？',
          positive: 'コピー'
        })

        localUnmount()
      })

      it('should call store.getLastPlans when confirmed', async () => {
        await localMount({ status: DwsProvisionReportStatus.notCreated })

        await click(() => wrapper.find('[data-copy-plans]'))

        expect(store.getLastPlans).toHaveBeenCalledTimes(1)
        expect(store.getLastPlans).toHaveBeenCalledWith({ officeId, userId, providedIn })

        localUnmount()
      })

      it('should not call store.getLastPlans when not confirmed', async () => {
        await localMount({ status: DwsProvisionReportStatus.notCreated })

        jest.spyOn($confirm, 'show').mockResolvedValue(false)

        await click(() => wrapper.find('[data-copy-plans]'))

        expect(store.getLastPlans).not.toHaveBeenCalled()

        localUnmount()
      })

      it('should display error if last month\'s plans did not exist', async () => {
        await localMount({ status: DwsProvisionReportStatus.notCreated })

        jest.spyOn(store, 'getLastPlans').mockRejectedValueOnce(undefined)

        await click(() => wrapper.find('[data-copy-plans]'))

        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('前月の予定が登録されていないためコピーできません。')

        localUnmount()
      })
    })
  })

  describe('delete dwsProvisionReport button', () => {
    async function localMount (status: DwsProvisionReportStatus | undefined = DwsProvisionReportStatus.fixed) {
      const reportData = {
        entries: [],
        status
      }
      await mountComponent({ reportData })
    }

    function localUnmount () {
      unmountComponent()
    }

    beforeEach(() => {
      jest.spyOn($alert, 'error').mockReturnValue()
      jest.spyOn($api.dwsProvisionReports, 'delete').mockResolvedValue()
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
    })

    afterEach(() => {
      mocked($alert.error).mockReset()
      mocked($api.dwsProvisionReports.delete).mockReset()
      mocked($confirm.show).mockClear()
      mocked($snackbar.success).mockReset()
      $back.mockReset()
    })

    it.each([
      ['fixed', DwsProvisionReportStatus.fixed],
      ['inProgress', DwsProvisionReportStatus.inProgress]
    ])('should be rendered when reportData.status is DwsProvisionReportStatus.%s', async (_, status) => {
      await localMount(status)
      expect(wrapper).toContainElement('[data-delete-dws-provision-report-button]')
      expect(wrapper.find('[data-delete-dws-provision-report-button]')).toMatchSnapshot()
      localUnmount()
    })

    it('should show confirm dialog', async () => {
      await localMount()
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: '予定・実績を削除します。一度削除した予定・実績は元に戻せません。\n\n本当によろしいですか？',
        positive: '削除'
      })
      localUnmount()
    })

    it('should call $api.dwsProvisionReports.delete when confirmed', async () => {
      const providedIn = DateTime.fromISO(dwsProvisionReport.providedIn.toString()).toFormat(ISO_MONTH_FORMAT)
      await localMount()
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($api.dwsProvisionReports.delete).toHaveBeenCalledTimes(1)
      expect($api.dwsProvisionReports.delete).toHaveBeenCalledWith({
        officeId: dwsProvisionReport.officeId,
        userId: dwsProvisionReport.userId,
        providedIn
      })
      localUnmount()
    })

    it('should not call $api.dwsProvisionReports.delete when not confirmed', async () => {
      await localMount()
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($api.dwsProvisionReports.delete).not.toHaveBeenCalled()
      localUnmount()
    })

    it('should display snackbar when dwsProvisionReport deleted', async () => {
      await localMount()
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('予定・実績を削除しました。')
      localUnmount()
    })

    it('should not display snackbar when not confirmed', async () => {
      await localMount()
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($snackbar.success).not.toHaveBeenCalled()
      localUnmount()
    })

    it('should not display snackbar when failed to delete dwsProvisionReport', async () => {
      await localMount()
      jest.spyOn($api.dwsProvisionReports, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
      localUnmount()
    })

    it('should call $back when dwsProvisionReport deleted', async () => {
      await localMount()
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($back).toHaveBeenCalledTimes(1)
      expect($back).toHaveBeenCalledWith('/dws-provision-reports')
      localUnmount()
    })

    it('should not call $back when dwsProvisionReport deleted', async () => {
      await localMount()
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($back).not.toHaveBeenCalled()
      localUnmount()
    })

    it('should not call $back when failed to delete dwsProvisionReport', async () => {
      await localMount()
      jest.spyOn($api.dwsProvisionReports, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      const button = wrapper.find('[data-delete-dws-provision-report-button]')

      await click(() => button)

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($back).not.toHaveBeenCalled()
      localUnmount()
    })

    it('should be not rendered when reportData.status is DwsProvisionReportStatus.notCreated', async () => {
      await localMount(DwsProvisionReportStatus.notCreated)

      expect(wrapper).not.toContainElement('[data-delete-dws-provision-report-button]')
      localUnmount()
    })

    it('should be not rendered when reportData.status is undefined', async () => {
      const reportData = {
        entries: [],
        status: undefined
      }
      await mountComponent({ reportData })

      expect(wrapper).not.toContainElement('[data-delete-dws-provision-report-button]')
      unmountComponent()
    })
  })

  describe('dws service report preview downloader', () => {
    beforeAll(async () => {
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn($api.dwsProvisionReports, 'downloadPreviews')
        .mockResolvedValue({ job: createJobStub('token', JobStatus.waiting) })
      jest.spyOn($download, 'uri').mockResolvedValue()
      await mountComponent()
    })

    beforeEach(() => {
      jest.clearAllMocks()
    })

    it('should not start to download preview if provision report is not saved', async () => {
      const isSaved = false
      await wrapper.vm.previewDownloader.download(isSaved)

      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('先に予実を保存してください。')
    })

    it('should start to download preview if provision report is saved', async () => {
      const officeId = parseInt(defaultRouteParams.officeId)
      const userId = parseInt(defaultRouteParams.userId)
      const providedIn = defaultRouteParams.providedIn
      const form: DwsProvisionReportsApi.DownloadForm = {
        officeId,
        userId,
        providedIn
      }
      mocked(execute).mockImplementationOnce(async ({ process }) => {
        await process()
      })
      const isSaved = true
      await wrapper.vm.previewDownloader.download(isSaved)

      expect($snackbar.error).not.toHaveBeenCalled()
      expect($api.dwsProvisionReports.downloadPreviews).toHaveBeenCalledTimes(1)
      expect($api.dwsProvisionReports.downloadPreviews).toHaveBeenCalledWith({ form })
    })
  })

  it('should download preview if provision report is saved', async () => {
    const job = createJobStub('token', JobStatus.success)
    mocked(execute).mockImplementationOnce(async ({ success }) => {
      await (success ?? noop)(job)
    })
    const isSaved = true
    await wrapper.vm.previewDownloader.download(isSaved)

    expect($snackbar.error).not.toHaveBeenCalled()
    expect($download.uri).toHaveBeenCalledTimes(1)
    expect($download.uri).toHaveBeenCalledWith(job.data.uri, job.data.filename)
  })
})
