/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { Wrapper } from '@vue/test-utils'
import {
  HomeVisitLongTermCareSpecifiedOfficeAddition
} from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { LtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { LtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import {
  LtcsProvisionReportStatus,
  resolveLtcsProvisionReportStatus
} from '@zinger/enums/lib/ltcs-provision-report-status'
import {
  LtcsSpecifiedTreatmentImprovementAddition
} from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import { LtcsTreatmentImprovementAddition } from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { noop } from 'lodash'
import { DateTime } from 'luxon'
import Vue from 'vue'
import { colors } from '~/colors'
import {
  LtcsProvisionReportData,
  ltcsProvisionReportStateKey,
  LtcsProvisionReportStore,
  ltcsProvisionReportStoreKey
} from '~/composables/stores/use-ltcs-provision-report-store'
import { ltcsProvisionReportsStateKey } from '~/composables/stores/use-ltcs-provision-reports-store'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { Execute, useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { ISO_MONTH_FORMAT, OLDEST_DATE } from '~/models/date'
import { HttpStatusCode } from '~/models/http-status-code'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import LtcsProvisionReportPage from '~/pages/ltcs-provision-reports/_officeId/_userId/_providedIn/index.vue'
import { AlertService } from '~/services/alert-service'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { DownloadService } from '~/services/download-service'
import { SnackbarService } from '~/services/snackbar-service'
import { RouteQuery } from '~/support/router/types'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createHomeVisitLongTermCareCalcSpecStub } from '~~/stubs/create-home-visit-long-term-care-calc-spec-stub'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createLtcsProvisionReportDigestStubs } from '~~/stubs/create-ltcs-provision-report-digest-stub'
import { createLtcsProvisionReportEntryStub } from '~~/stubs/create-ltcs-provision-report-entry-stub'
import { createLtcsProvisionReportStoreStub } from '~~/stubs/create-ltcs-provision-report-store-stub'
import { createLtcsProvisionReportStub } from '~~/stubs/create-ltcs-provision-report-stub'
import { createLtcsProvisionReportsStoreStub } from '~~/stubs/create-ltcs-provision-reports-store-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import {
  createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub
} from '~~/stubs/ltcs-home-visit-long-term-care-dictionary-entry-response-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

type RouteParams = {
  officeId?: string
  userId?: string
  providedIn?: string
}

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-job-with-notification')

describe('pages/ltcs-provision-reports/_officeId/_userId/_providedIn/index.vue', () => {
  const defaultRouteParams: Required<RouteParams> = {
    officeId: '10',
    userId: '20',
    providedIn: '2021-02'
  }
  const { mount, shallowMount } = setupComponentTest()
  const $alert = createMock<AlertService>()
  const $api = createMockedApi(
    'homeVisitLongTermCareCalcSpecs',
    'ltcsHomeVisitLongTermCareDictionary',
    'ltcsProvisionReports'
  )
  const $back = createMockedBack()
  const $confirm = createMock<ConfirmDialogService>()
  const $download = createMock<DownloadService>()
  const $form = createMockedFormService()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const entry = createLtcsProvisionReportEntryStub(defaultRouteParams.providedIn)
  const ltcsProvisionReport = {
    ...createLtcsProvisionReportStub(),
    plan: {
      maxBenefitQuotaExcessScore: 1000,
      maxBenefitExcessScore: 1000
    },
    result: {
      maxBenefitQuotaExcessScore: 1000,
      maxBenefitExcessScore: 1000
    }
  }
  const ltcsProvisionReports = createLtcsProvisionReportDigestStubs(20)
  const ltcsProvisionReportsStore = createLtcsProvisionReportsStoreStub({ ltcsProvisionReports })
  const userIds = ltcsProvisionReports.map(x => x.userId.toString())
  const userStore = createUserStoreStub(createUserResponseStub(+userIds[0]))
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const execute = jest.fn<ReturnType<Execute>, Parameters<Execute>>()

  let wrapper: Wrapper<Vue & any>
  let store: LtcsProvisionReportStore

  type MountComponentParams = {
    query?: RouteQuery
    auth?: Partial<Auth>
    reportData?: Partial<LtcsProvisionReportData['ltcsProvisionReport']>
    params?: RouteParams
  }

  async function mountComponent ({ query, auth, reportData, params }: MountComponentParams = {}, shallow = true) {
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    mocked(useJobWithNotification).mockReturnValue({ execute })
    const fn = shallow ? shallowMount : mount
    const $routes = createMockedRoutes({ query: query ?? {} })
    const $route = createMockedRoute({ params: { ...defaultRouteParams, ...params } })
    const noReport = reportData && Object.keys(reportData).length === 0
    store = createLtcsProvisionReportStoreStub({
      ltcsProvisionReport: noReport
        ? undefined
        : {
          ...ltcsProvisionReport,
          ...reportData
        }
    })
    wrapper = fn(LtcsProvisionReportPage, {
      ...provides(
        [ltcsProvisionReportsStateKey, ltcsProvisionReportsStore.state],
        [ltcsProvisionReportStateKey, store.state],
        [ltcsProvisionReportStoreKey, store],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })],
        [userStateKey, userStore.state],
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore]
      ),
      mocks: {
        $alert,
        $api,
        $back,
        $confirm,
        $form,
        $download,
        $router,
        $routes,
        $route,
        $snackbar
      },
      stubs: [
        'z-ltcs-provision-report-entry-browsing-dialog',
        'z-ltcs-provision-report-entry-form-dialog',
        'z-promised',
        'z-user-card'
      ]
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'getOne').mockResolvedValue({
      homeVisitLongTermCareCalcSpec: createHomeVisitLongTermCareCalcSpecStub()
    })
    jest.spyOn($api.ltcsHomeVisitLongTermCareDictionary, 'get').mockImplementation(params => {
      return Promise.resolve(createLtcsHomeVisitLongTermCareDictionaryEntryResponseStub(params.serviceCode))
    })
    jest.spyOn($api.ltcsProvisionReports, 'getScoreSummary').mockResolvedValue({
      plan: {
        managedScore: 6000,
        unmanagedScore: 4000
      },
      result: {
        managedScore: 5800,
        unmanagedScore: 6200
      }
    })
    // このファイルのテストでは verifyBeforeLeaving の処理は重要ではないため、何もせずに関数を実行する
    jest.spyOn($form, 'verifyBeforeLeaving').mockImplementation(async next => await next())
  })

  afterAll(() => {
    mocked($form.verifyBeforeLeaving).mockRestore()
    mocked($api.ltcsProvisionReports.getScoreSummary).mockRestore()
    mocked($api.ltcsHomeVisitLongTermCareDictionary.get).mockRestore()
    mocked($api.homeVisitLongTermCareCalcSpecs.getOne).mockRestore()
    mocked(useOffices).mockRestore()
  })

  afterEach(() => {
    mocked($form.verifyBeforeLeaving).mockClear()
    mocked($api.ltcsProvisionReports.getScoreSummary).mockClear()
    mocked($api.ltcsHomeVisitLongTermCareDictionary.get).mockClear()
    mocked($api.homeVisitLongTermCareCalcSpecs.getOne).mockClear()
    mocked(useOffices).mockClear()
  })

  describe('initial view', () => {
    it('should be rendered correctly', () => {
      mountComponent()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered office link if having permission', () => {
      mountComponent()
      expect(wrapper.find('[data-office-abbr] a').exists()).toBeTrue()
      unmountComponent()
    })

    it('should not be rendered office link if have not permission', () => {
      mountComponent({ auth: {} })
      expect(wrapper.find('[data-office-abbr] a').exists()).toBeFalse()
      unmountComponent()
    })

    it('should be disabled previous month button if the current is the oldest month', () => {
      const params = {
        providedIn: DateTime.fromISO(OLDEST_DATE).toFormat(ISO_MONTH_FORMAT)
      }

      mountComponent({ params })

      const button = wrapper.find('[data-prev-month]')
      expect(button).toBeDisabled()
    })

    it('should be disabled next month if the current is 3 months from now', () => {
      const params = {
        providedIn: DateTime.local().plus({ months: 3 }).toFormat(ISO_MONTH_FORMAT)
      }

      mountComponent({ params })

      const button = wrapper.find('[data-next-month]')
      expect(button).toBeDisabled()
    })

    it('should be disabled previous user if the current is the first user', () => {
      const params = {
        userId: userIds[0]
      }

      mountComponent({ params })

      const button = wrapper.find('[data-prev-user]')
      expect(button).toBeDisabled()
    })

    it('should be disabled next user if the current is the last user', () => {
      const params = {
        userId: userIds[userIds.length - 1]
      }

      mountComponent({ params })

      const button = wrapper.find('[data-next-user]')
      expect(button).toBeDisabled()
    })

    it('should be disabled save and confirm if it does not have entry', () => {
      const reportData = {
        entries: [],
        status: LtcsProvisionReportStatus.inProgress
      }
      mountComponent({ reportData })
      expect(wrapper.find('[data-save]')).toBeDisabled()
      expect(wrapper.find('[data-confirm]')).toBeDisabled()
      unmountComponent()
    })

    it('should not be able to edit if it is already fixed', () => {
      const reportData = {
        entries: [entry],
        status: LtcsProvisionReportStatus.fixed
      }
      mountComponent({ reportData }, false)
      expect(wrapper.find('[data-add-service]')).toBeDisabled()
      expect(wrapper.find('[data-copy-plans]')).toBeDisabled()
      expect(wrapper.find('[data-save]')).toBeDisabled()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('routing', () => {
    beforeEach(() => {
      jest.spyOn($router, 'push')
    })

    afterEach(() => {
      mocked($router.push).mockReset()
    })

    const createPath = ({ userId, providedIn }: Omit<RouteParams, 'officeId'>) => {
      const toUserId = userId ?? defaultRouteParams.userId
      const toProvidedIn = providedIn ?? defaultRouteParams.providedIn
      return `/ltcs-provision-reports/${defaultRouteParams.officeId}/${toUserId}/${toProvidedIn}`
    }

    it('should move to selected month when month changed', async () => {
      const params = {
        providedIn: '2021-03'
      }
      const providedIn = '2020-12'

      await mountComponent({ params })
      await wrapper.findComponent({ ref: 'selectMonth' }).vm.$emit('input', providedIn)

      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ providedIn }))
    })

    it('should move to previous month when previous month button clicked', async () => {
      const params = {
        providedIn: '2021-03'
      }

      await mountComponent({ params })
      await click(() => wrapper.find('[data-prev-month]'))

      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ providedIn: '2021-02' }))
    })

    it('should move to next month when next month button clicked', async () => {
      const params = {
        providedIn: '2021-03'
      }

      await mountComponent({ params })
      await click(() => wrapper.find('[data-next-month]'))

      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ providedIn: '2021-04' }))
    })

    it('should move to selected user when user changed', async () => {
      const params = {
        userId: userIds[1]
      }
      const userId = userIds[5]

      await mountComponent({ params })
      await wrapper.findComponent({ ref: 'selectUser' }).vm.$emit('input', userId)

      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ userId }))
    })

    it('should move to previous user when previous user button clicked', async () => {
      const params = {
        userId: userIds[1]
      }

      await mountComponent({ params })
      await click(() => wrapper.find('[data-prev-user]'))

      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ userId: userIds[0] }))
    })

    it('should move to next user when next user button clicked', async () => {
      const params = {
        userId: userIds[1]
      }

      await mountComponent({ params })
      await click(() => wrapper.find('[data-next-user]'))

      expect($form.verifyBeforeLeaving).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(createPath({ userId: userIds[2] }))
    })
  })

  describe('validation', () => {
    // TODO: 単位数周りのテストを追加する
    const providedIn = defaultRouteParams.providedIn
    const baseEntry1: LtcsProvisionReportEntry = {
      ...createLtcsProvisionReportEntryStub(providedIn),
      category: LtcsProjectServiceCategory.physicalCare
    }
    const baseEntry2: LtcsProvisionReportEntry = {
      ...createLtcsProvisionReportEntryStub(providedIn, 2),
      category: LtcsProjectServiceCategory.housework
    }

    async function localMount (reportData: Partial<LtcsProvisionReportData['ltcsProvisionReport']>) {
      await mountComponent({ reportData }, false)
      jest.spyOn(store, 'update')
    }

    function localUnmount () {
      mocked(store.update).mockRestore()
      unmountComponent()
    }

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

    describe('empty entry', () => {
      it('should fail if there is an entry that both plans and results are empty', async () => {
        const entry = { ...baseEntry1, slot: { start: '09:00', end: '11:00' }, plans: [], results: [] }

        await localMount({ entries: [entry] })
        const button = wrapper.find('[data-save]')

        await click(() => button)

        expect(store.update).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('予定、実績がひとつも存在しないサービス情報があるため保存できません。')
        localUnmount()
      })

      it.each([
        ['plan', 'plans'],
        ['result', 'results']
      ])('should not fail if the entry has at least one %s', async (_, key) => {
        const entry = {
          ...baseEntry1,
          ...{ slot: { start: '09:00', end: '11:00' }, plans: [], results: [] },
          [key]: ['2021-02-01']
        }

        await localMount({ entries: [entry] })
        const button = wrapper.find('[data-save]')

        await click(() => button)

        expect(store.update).toHaveBeenCalled()
        expect($snackbar.error).not.toHaveBeenCalled()
        localUnmount()
      })
    })

    describe('overlapping entry', () => {
      it('should fail if the start time overlaps with other entries', async () => {
        const entry1 = { ...baseEntry1, slot: { start: '09:00', end: '11:00' }, plans: ['2021-02-01'] }
        const entry2 = { ...baseEntry2, slot: { start: '10:00', end: '13:00' }, plans: ['2021-02-01'] }

        await localMount({ entries: [entry1, entry2] })
        const button = wrapper.find('[data-save]')

        await click(() => button)

        expect(store.update).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('提供時間が重複している予定、もしくは実績があるため保存できません。')
        localUnmount()
      })

      it('should fail if the end time overlaps with other entries', async () => {
        const entry1 = { ...baseEntry1, slot: { start: '09:00', end: '11:00' }, results: ['2021-02-04'] }
        const entry2 = {
          ...baseEntry2,
          slot: { start: '08:00', end: '09:30' },
          results: ['2021-02-01', '2021-02-02', '2021-02-04']
        }

        await localMount({ entries: [entry1, entry2] })
        const button = wrapper.find('[data-save]')

        await click(() => button)

        expect(store.update).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('提供時間が重複している予定、もしくは実績があるため保存できません。')
        localUnmount()
      })

      it('should fail if the time range contains other entries', async () => {
        const entry1 = { ...baseEntry2, slot: { start: '08:00', end: '08:30' }, plans: ['2021-02-01'] }
        const entry2 = { ...baseEntry1, slot: { start: '09:00', end: '11:00' }, plans: ['2021-02-08'] }
        const entry3 = { ...baseEntry2, slot: { start: '08:00', end: '12:00' }, plans: ['2021-02-08'] }

        await localMount({ entries: [entry1, entry2, entry3] })
        const button = wrapper.find('[data-save]')

        await click(() => button)

        expect(store.update).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('提供時間が重複している予定、もしくは実績があるため保存できません。')
        localUnmount()
      })

      it('should not fail if the end time overlaps with other own expense entries', async () => {
        const entry1: LtcsProvisionReportEntry = {
          ...baseEntry1,
          slot: { start: '09:00', end: '11:00' },
          results: ['2021-02-04'],
          category: LtcsProjectServiceCategory.ownExpense
        }
        const entry2: LtcsProvisionReportEntry = {
          ...baseEntry2,
          slot: { start: '08:00', end: '09:30' },
          results: ['2021-02-01', '2021-02-02', '2021-02-04'],
          category: LtcsProjectServiceCategory.physicalCare
        }

        await localMount({ entries: [entry1, entry2] })
        const button = wrapper.find('[data-save]')

        await click(() => button)

        expect(store.update).toHaveBeenCalled()
        expect($snackbar.error).not.toHaveBeenCalled()
        localUnmount()
      })
    })
  })

  describe('action', () => {
    let observer: ValidationObserverInstance
    const officeId = parseInt(defaultRouteParams.officeId)
    const userId = parseInt(defaultRouteParams.userId)
    const providedIn = defaultRouteParams.providedIn
    const baseEntry = createLtcsProvisionReportEntryStub(providedIn)

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

    describe('update ltcs provision report', () => {
      function localMount (reportData: Partial<LtcsProvisionReportData['ltcsProvisionReport']>) {
        mountComponent({ reportData }, false)
        // FYI mountComponent で store を作っているため、mount 後に行う必要がある
        jest.spyOn(store, 'update')
      }

      function localUnmount () {
        mocked(store.update).mockRestore()
        unmountComponent()
      }

      it('should call store.update when save button clicked', async () => {
        const baseEntry2 = createLtcsProvisionReportEntryStub(providedIn, 2)
        const entry1 = {
          ...baseEntry,
          slot: { start: '09:00', end: '11:00' },
          plans: ['2021-02-01'],
          results: ['2021-02-01']
        }
        const entry2 = {
          ...baseEntry2,
          slot: { start: '09:00', end: '11:00' },
          plans: ['2021-02-03'],
          results: ['2021-02-03']
        }
        const entries = [entry1, entry2]

        const form: LtcsProvisionReportsApi.UpdateForm = {
          entries,
          specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition.addition1,
          treatmentImprovementAddition: LtcsTreatmentImprovementAddition.addition2,
          specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition.none,
          baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition.addition1,
          locationAddition: LtcsOfficeLocationAddition.specifiedArea,
          plan: ltcsProvisionReport.plan,
          result: ltcsProvisionReport.result
        }

        localMount(form)

        await click(() => wrapper.find('[data-save]'))

        expect(store.update).toHaveBeenCalledTimes(1)
        expect(store.update).toHaveBeenCalledWith({ officeId, userId, providedIn, form })
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('介護保険サービス予実を保存しました。')

        localUnmount()
      })

      it('should display error if server response is 400 Bad Request when called store.update', async () => {
        localMount({ status: LtcsProvisionReportStatus.fixed, entries: [] })
        // 失敗の確認のため、validateはtrueにする
        observer = getValidationObserver(wrapper)
        jest.spyOn(observer, 'validate').mockResolvedValue(true)

        mocked(store.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            entries: '入力してください。'
          }
        }))

        // isEditingをtrueにしたいので、適当に編集する.
        wrapper.vm.entries = [baseEntry]

        await click(() => wrapper.find('[data-save]'))

        expect(store.update).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('介護保険サービス予実の保存に失敗しました。')

        localUnmount()
        mocked(observer.validate).mockClear()
      })
    })

    describe('update ltcs provision report status', () => {
      function localMount (reportData: Partial<LtcsProvisionReportData['ltcsProvisionReport']>) {
        mountComponent({ reportData })
        jest.spyOn(store, 'updateStatus')
      }

      function localUnmount () {
        mocked(store.updateStatus).mockRestore()
        unmountComponent()
      }

      it('should call store.updateStatus when remand button clicked', async () => {
        const status = LtcsProvisionReportStatus.inProgress
        const label = resolveLtcsProvisionReportStatus(status)
        const form = { status }

        localMount({ status: LtcsProvisionReportStatus.fixed, entries: [] })

        await click(() => wrapper.find('[data-remand]'))

        expect(store.updateStatus).toHaveBeenCalledTimes(1)
        expect(store.updateStatus).toHaveBeenCalledWith({ officeId, userId, providedIn, form })
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith(`介護保険サービス予実の状態を${label}に変更しました。`)

        localUnmount()
      })

      it('should call store.updateStatus when confirm button clicked', async () => {
        const status = LtcsProvisionReportStatus.fixed
        const label = resolveLtcsProvisionReportStatus(status)
        const form = { status }

        localMount({ status: LtcsProvisionReportStatus.inProgress, entries: [] })

        await click(() => wrapper.find('[data-confirm]'))

        expect(store.updateStatus).toHaveBeenCalledTimes(1)
        expect(store.updateStatus).toHaveBeenCalledWith({ officeId, userId, providedIn, form })
        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith(`介護保険サービス予実の状態を${label}に変更しました。`)

        localUnmount()
      })

      it('should display error if server response is 400 Bad Request when called store.updateStatus', async () => {
        localMount({ status: LtcsProvisionReportStatus.fixed, entries: [] })

        mocked(store.updateStatus).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            userId: ['契約に初回サービス提供日が設定されていないため確定できません。']
          }
        }))

        await click(() => wrapper.find('[data-remand]'))

        const errorText = wrapper.find('[data-errors]').text()
        expect(errorText).toContain('契約に初回サービス提供日が設定されていないため確定できません。')
        expect(store.updateStatus).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('介護保険サービス予実の状態変更に失敗しました。')

        localUnmount()
      })
    })

    describe('copy plans from last month', () => {
      function localMount (reportData: Partial<LtcsProvisionReportData['ltcsProvisionReport']>) {
        mountComponent({ reportData })
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
        localMount({ status: LtcsProvisionReportStatus.notCreated })

        await click(() => wrapper.find('[data-copy-plans]'))

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          message: '前月から予定をコピーします。現在入力されている予定・実績はすべて消去されます。\n\nよろしいですか？',
          positive: 'コピー'
        })

        localUnmount()
      })

      it('should call store.getLastPlans when confirmed', async () => {
        localMount({ status: LtcsProvisionReportStatus.notCreated })

        await click(() => wrapper.find('[data-copy-plans]'))

        expect(store.getLastPlans).toHaveBeenCalledTimes(1)
        expect(store.getLastPlans).toHaveBeenCalledWith({ officeId, userId, providedIn })

        localUnmount()
      })

      it('should not call store.getLastPlans when not confirmed', async () => {
        localMount({ status: LtcsProvisionReportStatus.notCreated })

        jest.spyOn($confirm, 'show').mockResolvedValue(false)

        await click(() => wrapper.find('[data-copy-plans]'))

        expect(store.getLastPlans).not.toHaveBeenCalled()

        localUnmount()
      })

      it('should display error if last month\'s plans did not exist', async () => {
        localMount({ status: LtcsProvisionReportStatus.notCreated })

        jest.spyOn(store, 'getLastPlans').mockRejectedValueOnce(undefined)

        await click(() => wrapper.find('[data-copy-plans]'))

        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('前月の予定が登録されていないためコピーできません。')

        localUnmount()
      })
    })
  })

  describe('delete ltcsProvisionReport button', () => {
    function localMount (status: LtcsProvisionReportStatus = LtcsProvisionReportStatus.fixed) {
      const reportData = {
        entries: [],
        status
      }
      mountComponent({ reportData })
    }

    beforeEach(() => {
      jest.spyOn($alert, 'error').mockReturnValue()
      jest.spyOn($api.ltcsProvisionReports, 'delete').mockResolvedValue()
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
    })

    afterEach(() => {
      mocked($alert.error).mockReset()
      mocked($api.ltcsProvisionReports.delete).mockReset()
      mocked($confirm.show).mockClear()
      mocked($snackbar.success).mockReset()
      $back.mockReset()
    })

    it.each([
      ['fixed', LtcsProvisionReportStatus.fixed],
      ['inProgress', LtcsProvisionReportStatus.inProgress]
    ])('should be rendered when reportData.status is LtcsProvisionReport.%s', (_, status) => {
      localMount(status)

      expect(wrapper).toContainElement('[data-delete-ltcs-provision-report-button]')
      expect(wrapper.find('[data-delete-ltcs-provision-report-button]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should show confirm dialog', async () => {
      localMount()
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($confirm.show).toHaveBeenCalledTimes(1)
      expect($confirm.show).toHaveBeenCalledWith({
        color: colors.critical,
        message: '予定・実績を削除します。一度削除した予定・実績は元に戻せません。\n\n本当によろしいですか？',
        positive: '削除'
      })
      unmountComponent()
    })

    it('should call $api.ltcsProvisionReports.delete when confirmed', async () => {
      const providedIn = DateTime.fromISO(ltcsProvisionReport.providedIn.toString()).toFormat(ISO_MONTH_FORMAT)
      localMount()
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($api.ltcsProvisionReports.delete).toHaveBeenCalledTimes(1)
      expect($api.ltcsProvisionReports.delete).toHaveBeenCalledWith({
        officeId: ltcsProvisionReport.officeId,
        userId: ltcsProvisionReport.userId,
        providedIn
      })
      unmountComponent()
    })

    it('should not call $api.ltcsProvisionReports.delete when not confirmed', async () => {
      localMount()
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($api.ltcsProvisionReports.delete).not.toHaveBeenCalled()
      unmountComponent()
    })

    it('should display snackbar when ltcsProvisionReport deleted', async () => {
      localMount()
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('予定・実績を削除しました。')
      unmountComponent()
    })

    it('should not display snackbar when not confirmed', async () => {
      localMount()
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($snackbar.success).not.toHaveBeenCalled()
      unmountComponent()
    })

    it('should not display snackbar when failed to delete ltcsProvisionReport', async () => {
      localMount()
      jest.spyOn($api.ltcsProvisionReports, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
      unmountComponent()
    })

    it('should call $back when ltcsProvisionReport deleted', async () => {
      localMount()
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($back).toHaveBeenCalledTimes(1)
      expect($back).toHaveBeenCalledWith('/ltcs-provision-reports')
      unmountComponent()
    })

    it('should not call $back when ltcsProvisionReport deleted', async () => {
      localMount()
      jest.spyOn($confirm, 'show').mockResolvedValue(false)
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($back).not.toHaveBeenCalled()
      unmountComponent()
    })

    it('should not call $back when failed to delete ltcsProvisionReport', async () => {
      localMount()
      jest.spyOn($api.ltcsProvisionReports, 'delete').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      const button = wrapper.find('[data-delete-ltcs-provision-report-button]')

      await click(() => button)

      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($back).not.toHaveBeenCalled()
      unmountComponent()
    })

    it('should be not rendered when reportData.status is LtcsProvisionReport.notCreated', () => {
      localMount(LtcsProvisionReportStatus.notCreated)

      expect(wrapper).not.toContainElement('[data-delete-ltcs-provision-report-button]')
      unmountComponent()
    })

    it('should be not rendered when reportData.status is undefined', () => {
      const reportData = {
        entries: [],
        status: undefined
      }
      mountComponent({ reportData })

      expect(wrapper).not.toContainElement('[data-delete-dws-provision-report-btn]')
      unmountComponent()
    })
  })

  describe('download a sheet', () => {
    beforeAll(async () => {
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn($download, 'uri').mockResolvedValue()
      const token = '10'
      const job = createJobStub(token, JobStatus.waiting)
      jest.spyOn($api.ltcsProvisionReports, 'downloadSheets').mockResolvedValue({ job })
      await mountComponent()
    })
    afterEach(() => {
      mocked($snackbar.error).mockClear()
    })
    it('should display the dialog when the download button is clicked', async () => {
      const button = wrapper.find('[data-download-sheet]')
      expect(wrapper.vm.fileDownloadDialog.isActive.value).toBeFalse()
      await click(() => button)
      expect(wrapper.vm.fileDownloadDialog.isActive.value).toBeTrue()
    })
    it('should not display the dialog if the provision report is not saved', async () => {
      await mountComponent()
      expect(wrapper.vm.fileDownloadDialog.isActive.value).toBeFalse()
      wrapper.vm.fileDownloadDialog.show(false)
      expect(wrapper.vm.fileDownloadDialog.isActive.value).toBeFalse()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
    })
    it('should display the snackbar with the message if the provision report is not saved', () => {
      wrapper.vm.fileDownloadDialog.show(false)
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('先に予実を保存してください。')
    })
    it('should not display the snackbar if the provision report is saved', () => {
      wrapper.vm.fileDownloadDialog.show(true)
      expect($snackbar.error).not.toHaveBeenCalled()
    })
    it('should download a sheet', () => {
      wrapper.vm.fileDownloadDialog.run('2021-10-10')
      expect(execute).toHaveBeenCalledTimes(1)
      expect(execute).toHaveBeenCalledWith(expect.objectContaining({
        notificationProps: expect.objectContaining({
          text: expect.objectContaining({
            progress: 'サービス提供票のダウンロードを準備中です...',
            success: 'サービス提供票のダウンロードを開始します',
            failure: 'サービス提供票のダウンロードに失敗しました'
          })
        }),
        process: expect.any(Function),
        success: expect.any(Function)
      }))
    })
    it('should call $api.ltcsProvisionReports.downloadSheets', async () => {
      mocked(execute).mockImplementation(async ({ process }) => {
        await process()
      })
      const issuedOn = '2021-10-10'
      const needsMaskingInsNumber = false
      const needsMaskingInsName = true
      const form: LtcsProvisionReportsApi.DownloadForm = {
        officeId: +defaultRouteParams.officeId,
        userId: +defaultRouteParams.userId,
        providedIn: defaultRouteParams.providedIn,
        issuedOn,
        needsMaskingInsNumber,
        needsMaskingInsName
      }

      await setData(wrapper, {
        fileDownloadDialog: {
          needsMaskingInsNumber: ref(needsMaskingInsNumber),
          needsMaskingInsName: ref(needsMaskingInsName)
        }
      })
      wrapper.vm.fileDownloadDialog.run(issuedOn)

      expect($api.ltcsProvisionReports.downloadSheets).toHaveBeenCalledTimes(1)
      expect($api.ltcsProvisionReports.downloadSheets).toHaveBeenCalledWith({ form })
      mocked(execute).mockReset()
    })
    it('should start downloading when process completed successfully', () => {
      const token = '10'
      const job = createJobStub(token, JobStatus.success)
      mocked(execute).mockImplementation(async ({ success }) => {
        await (success ?? noop)(job)
      })

      const issuedOn = '2021-10-10'
      wrapper.vm.fileDownloadDialog.run(issuedOn)

      expect($download.uri).toHaveBeenCalledTimes(1)
      expect($download.uri).toHaveBeenCalledWith(job.data.uri, job.data.filename)
      mocked(execute).mockReset()
    })
  })

  describe('homeVisitLongTermCareCalcSpecs.getOne', () => {
    const params: HomeVisitLongTermCareCalcSpecsApi.GetOneParams = {
      officeId: +defaultRouteParams.officeId,
      passthroughErrors: true,
      providedIn: defaultRouteParams.providedIn
    }
    it('should be called when ltcsProvisionReport is not registered.', async () => {
      await mountComponent({ reportData: {} })

      expect($api.homeVisitLongTermCareCalcSpecs.getOne).toHaveBeenCalledTimes(1)
      expect($api.homeVisitLongTermCareCalcSpecs.getOne).toHaveBeenCalledWith(params)

      unmountComponent()
    })

    it('should not be called when ltcsProvisionReport is registered.', async () => {
      await mountComponent()

      expect($api.homeVisitLongTermCareCalcSpecs.getOne).not.toHaveBeenCalled()

      unmountComponent()
    })
  })

  describe('update calc specs button', () => {
    function localMount (
      {
        entries,
        status,
        shallow
      }: {
        entries?: LtcsProvisionReportEntry[]
        status?: LtcsProvisionReportStatus
        shallow?: boolean
      } = {}
    ) {
      const reportData = {
        entries: entries ?? [],
        status: status ?? LtcsProvisionReportStatus.inProgress
      }
      mountComponent({ reportData }, shallow ?? false)
    }

    it.each([
      ['notCreated', LtcsProvisionReportStatus.notCreated],
      ['inProgress', LtcsProvisionReportStatus.inProgress]
    ])('should be enabled if LtcsProvisionReportStatus is "%s"', (_, status) => {
      localMount({ status })

      const button = wrapper.find('[data-update-additions-button]')

      expect(button).not.toBeDisabled()

      unmountComponent()
    })

    it('should be disabled if LtcsProvisionReportStatus is "fixed"', () => {
      localMount({ status: LtcsProvisionReportStatus.fixed })

      const button = wrapper.find('[data-update-additions-button]')

      expect(button).toBeDisabled()

      unmountComponent()
    })

    it('should call $api.homeVisitLongTermCareCalcSpecs.getOne when it clicked', async () => {
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'getOne').mockResolvedValue({
        homeVisitLongTermCareCalcSpec: createHomeVisitLongTermCareCalcSpecStub()
      })
      localMount()

      const params = {
        officeId: parseInt(defaultRouteParams.officeId),
        passthroughErrors: true,
        providedIn: DateTime.fromISO(ltcsProvisionReport.providedIn.toString()).toFormat(ISO_MONTH_FORMAT)
      }
      const button = wrapper.find('[data-update-additions-button]')

      await click(() => button)

      expect($api.homeVisitLongTermCareCalcSpecs.getOne).toHaveBeenCalledTimes(1)
      expect($api.homeVisitLongTermCareCalcSpecs.getOne).toHaveBeenCalledWith(params)

      mocked($api.homeVisitLongTermCareCalcSpecs.getOne).mockReset()
      unmountComponent()
    })

    it('should not be disabled save button when call $api.homeVisitLongTermCareCalcSpecs.getOne', async () => {
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'getOne').mockResolvedValue({
        homeVisitLongTermCareCalcSpec: createHomeVisitLongTermCareCalcSpecStub()
      })
      localMount({ entries: [entry] })

      const button = wrapper.find('[data-update-additions-button]')

      await click(() => button)

      expect($api.homeVisitLongTermCareCalcSpecs.getOne).toHaveBeenCalledTimes(1)

      const saveButton = wrapper.find('[data-save]')

      expect(saveButton).not.toBeDisabled()

      mocked($api.homeVisitLongTermCareCalcSpecs.getOne).mockReset()
      unmountComponent()
    })

    it('should update the additional table\'s information when it clicked', async () => {
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'getOne').mockResolvedValueOnce({
        homeVisitLongTermCareCalcSpec: {
          ...createHomeVisitLongTermCareCalcSpecStub(),
          specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition.addition5,
          treatmentImprovementAddition: LtcsTreatmentImprovementAddition.addition5,
          specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition.addition2,
          baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition.addition1,
          locationAddition: LtcsOfficeLocationAddition.mountainousArea
        }
      })
      localMount({ shallow: false })

      const button = wrapper.find('[data-update-additions-button]')

      await click(() => button)

      expect(wrapper.find('[data-addition-table]')).toMatchSnapshot()

      mocked($api.homeVisitLongTermCareCalcSpecs.getOne).mockReset()
      unmountComponent()
    })
  })

  describe('add entry action', () => {
    function localMount () {
      const reportData = {
        status: LtcsProvisionReportStatus.inProgress
      }
      mountComponent({ reportData })
    }

    it('should show the form dialog when add button clicked', async () => {
      await localMount()

      const dialog = wrapper.findComponent({ ref: 'ltcsProvisionReportEntryFormDialog' })

      expect(dialog.vm.$props.show).toBeFalse()

      await click(() => wrapper.find('[data-add-service]'))

      expect(dialog.vm.$props.show).toBeTrue()

      unmountComponent()
    })

    it('should add the entry when the form dialog emitted save', async () => {
      await localMount()

      // 追加前の長さを保持しておく
      const before = wrapper.vm.entries.length

      await click(() => wrapper.find('[data-add-service]'))

      const edited = { ...wrapper.vm.entryBeingEdited, entry }
      const dialog = wrapper.findComponent({ ref: 'ltcsProvisionReportEntryFormDialog' })

      dialog.vm.$emit('click:save', edited)

      expect(wrapper.vm.entries.length).toEqual(before + 1)

      unmountComponent()
    })
  })

  describe('edit entry action', () => {
    function localMount () {
      const reportData = {
        status: LtcsProvisionReportStatus.inProgress
      }
      mountComponent({ reportData })
    }

    it('should show the form dialog when editEntry called', async () => {
      await localMount()

      const index = 0
      const dialog = wrapper.findComponent({ ref: 'ltcsProvisionReportEntryFormDialog' })

      expect(dialog.vm.$props.show).toBeFalse()

      // テーブルの行を押すのが面倒なので、editEntry を呼ぶ
      const entry = wrapper.vm.entries[index]
      await wrapper.vm.editEntry(entry, index)

      expect(dialog.vm.$props.show).toBeTrue()

      unmountComponent()
    })

    it('should add the entry when the form dialog emitted save', async () => {
      await localMount()

      const index = 3
      // 編集前の長さを保持しておく
      const before = wrapper.vm.entries.length

      const entry = wrapper.vm.entries[index]
      await wrapper.vm.editEntry(entry, index)

      const newEntry = { ...createLtcsProvisionReportEntryStub(defaultRouteParams.providedIn), key: entry.key }
      const edited = { ...wrapper.vm.entryBeingEdited, entry: newEntry }
      const dialog = wrapper.findComponent({ ref: 'ltcsProvisionReportEntryFormDialog' })

      dialog.vm.$emit('click:save', edited)

      const updated = wrapper.vm.entries[index]

      expect(wrapper.vm.entries.length).toEqual(before)
      expect(updated).not.toEqual(entry)
      expect(updated).toEqual(newEntry)

      unmountComponent()
    })
  })

  describe('multiple selection action', () => {
    function localMount () {
      const reportData = {
        status: LtcsProvisionReportStatus.inProgress
      }
      mountComponent({ reportData })
    }

    describe('entry copy button', () => {
      it('should not be rendered when no checkboxes are checked', async () => {
        await localMount()

        expect(wrapper.find('[data-copy-service]')).not.toExist()

        unmountComponent()
      })

      it('should be rendered and enabled when only one checkbox is checked', async () => {
        await localMount()

        const selections = Object.entries(wrapper.vm.selections)
          .reduce((acc, [key, _], i) => ({ ...acc, [key]: i === 0 }), {})
        await setData(wrapper, { selections })

        const button = wrapper.find('[data-copy-service]')

        expect(button).toExist()
        expect(button).not.toBeDisabled()

        unmountComponent()
      })

      it('should be rendered but disabled when more than two checkboxes are checked', async () => {
        await localMount()

        const selections = Object.entries(wrapper.vm.selections)
          .reduce((acc, [key, _], i) => ({ ...acc, [key]: i < 2 }), {})
        await setData(wrapper, { selections })

        const button = wrapper.find('[data-copy-service]')

        expect(button).toExist()
        expect(button).toBeDisabled()

        unmountComponent()
      })

      it('should show the form dialog when clicked', async () => {
        await localMount()

        // サービス情報を選択する
        const selections = Object.entries(wrapper.vm.selections)
          .reduce((acc, [key, _], i) => ({ ...acc, [key]: i === 0 }), {})
        await setData(wrapper, { selections })

        const dialog = wrapper.findComponent({ ref: 'ltcsProvisionReportEntryFormDialog' })

        expect(dialog.vm.$props.show).toBeFalse()

        await click(() => wrapper.find('[data-copy-service]'))

        expect(dialog.vm.$props.show).toBeTrue()

        unmountComponent()
      })

      it('should copy the entry when the form dialog emitted save', async () => {
        await localMount()

        // コピー前の長さを保持しておく
        const before = wrapper.vm.entries.length

        const selections = Object.entries(wrapper.vm.selections)
          .reduce((acc, [key, _], i) => ({ ...acc, [key]: i === 0 }), {})
        await setData(wrapper, { selections })

        await click(() => wrapper.find('[data-copy-service]'))

        const edited = wrapper.vm.entryBeingEdited
        const dialog = wrapper.findComponent({ ref: 'ltcsProvisionReportEntryFormDialog' })

        dialog.vm.$emit('click:save', edited)

        expect(wrapper.vm.entries.length).toEqual(before + 1)

        unmountComponent()
      })
    })

    describe('entry delete button', () => {
      beforeEach(() => {
        jest.spyOn($confirm, 'show').mockResolvedValue(true)
      })

      afterEach(() => {
        mocked($confirm.show).mockRestore()
      })

      it('should not be rendered when no checkboxes are checked', async () => {
        await mountComponent()

        expect(wrapper.find('[data-delete-service]')).not.toExist()

        unmountComponent()
      })

      it('should be rendered when some checkboxes are checked', async () => {
        await mountComponent()

        const selections = Object.entries(wrapper.vm.selections)
          .reduce((acc, [key, _]) => ({ ...acc, [key]: true }), {})
        await setData(wrapper, { selections })
        expect(wrapper.find('[data-delete-service]')).toExist()

        unmountComponent()
      })

      it('should show confirm dialog when clicked', async () => {
        await mountComponent()

        const selections = Object.entries(wrapper.vm.selections)
          .reduce((acc, [key, _]) => ({ ...acc, [key]: true }), {})
        await setData(wrapper, { selections })
        const button = wrapper.find('[data-delete-service]')
        await click(() => button)

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          color: 'danger',
          message: 'サービス情報を削除します。\n\n本当によろしいですか？',
          positive: '削除'
        })

        unmountComponent()
      })

      it('should delete the entries when positive clicked', async () => {
        mocked($confirm.show).mockResolvedValue(true)

        await mountComponent()

        expect(wrapper.vm.entries).not.toStrictEqual([])

        const selections = Object.entries(wrapper.vm.selections)
          .reduce((acc, [key, _]) => ({ ...acc, [key]: true }), {})
        await setData(wrapper, { selections })
        const button = wrapper.find('[data-delete-service]')
        await click(() => button)

        expect(wrapper.vm.entries).toStrictEqual([])

        unmountComponent()
      })
    })
  })

  describe('dialog to navigate to calc-specs', () => {
    function localMount () {
      const reportData = {
        status: LtcsProvisionReportStatus.inProgress
      }
      mountComponent({ reportData }, false)
    }

    it('should show $api.homeVisitLongTermCareCalcSpecs.getOne throw error', async () => {
      const error = createAxiosError(HttpStatusCode.NotFound)
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'getOne').mockRejectedValueOnce(error)

      await localMount()

      const dialog = wrapper.findComponent({ ref: 'navigationDialog' })

      expect(dialog.vm.$props.active).toBeFalse()

      await click(() => wrapper.find('[data-update-additions-button]'))

      expect(dialog.vm.$props.active).toBeTrue()

      mocked($api.homeVisitLongTermCareCalcSpecs.getOne).mockReset()
      unmountComponent()
    })

    it('should call router.push when the it emitted click:positive', async () => {
      const error = createAxiosError(HttpStatusCode.NotFound)
      jest.spyOn($api.homeVisitLongTermCareCalcSpecs, 'getOne').mockRejectedValueOnce(error)
      jest.spyOn($router, 'push')

      await localMount()

      const dialog = wrapper.findComponent({ ref: 'navigationDialog' })

      expect(dialog.vm.$props.active).toBeFalse()

      await click(() => wrapper.find('[data-update-additions-button]'))

      dialog.vm.$emit('click:positive')

      expect($router.push).toHaveBeenCalledTimes(1)
      expect($router.push).toHaveBeenCalledWith(`/offices/${defaultRouteParams.officeId}#calc-specs`)

      mocked($router.push).mockReset()
      mocked($api.homeVisitLongTermCareCalcSpecs.getOne).mockReset()
      unmountComponent()
    })
  })
})
