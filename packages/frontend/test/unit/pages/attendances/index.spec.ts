/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty } from '@zinger/helpers/index'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import { attendancesStoreKey } from '~/composables/stores/use-attendances-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { Attendance } from '~/models/attendance'
import { Auth } from '~/models/auth'
import { createDateRange } from '~/models/date-range'
import { VSelectOption } from '~/models/vuetify'
import AttendancesIndexPage from '~/pages/attendances/index.vue'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { RouteQuery } from '~/support/router/types'
import { mapValues } from '~/support/utils/map-values'
import { createAttendanceStubs } from '~~/stubs/create-attendance-stub'
import { createAttendancesStoreStub } from '~~/stubs/create-attendances-store-stub'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click, submit } from '~~/test/helpers/trigger'

jest.mock('~/composables/use-job-polling')
jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')
jest.mock('~/composables/use-users')

describe('pages/attendances/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const $api = createMockedApi('attendances')
  const $confirm = createMock<ConfirmDialogService>()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const attendances = createAttendanceStubs(10)
  const attendancesStore = createAttendancesStoreStub({ attendances })
  const cancelJobPolling: CancelJobPolling = jest.fn()
  const startJobPolling: StartJobPolling = jest.fn()
  const thisWeekRange = createDateRange(TEST_NOW, 'week')
  const nextWeekRange = {
    start: TEST_NOW.plus({ weeks: 1 }).startOf('week').toISODate(),
    end: TEST_NOW.plus({ weeks: 1 }).endOf('week').toISODate()
  }
  const initParams: Record<string, unknown> = {
    assigneeId: '',
    assignerId: '',
    officeId: '',
    dateRangeType: 2,
    isConfirmed: '',
    task: '',
    userId: '',
    start: TEST_NOW.startOf('week').toISODate(),
    end: TEST_NOW.endOf('week').toISODate()
  }

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    auth?: Partial<Auth>
    isShallow?: true
    query?: RouteQuery
  }

  function mountComponent ({ auth, isShallow, query, ...options }: MountComponentArguments = {}) {
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    const fn = isShallow ? shallowMount : mount
    const $routes = createMockedRoutes({ query: query ?? {} })
    wrapper = fn(AttendancesIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })],
        [attendancesStoreKey, attendancesStore]
      ),
      ...options,
      mocks: {
        ...options?.mocks,
        $api,
        $confirm,
        $router,
        $routes,
        $snackbar
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useStaffs).mockReturnValue(createUseStaffsStub())
    mocked(useUsers).mockReturnValue(createUseUsersStub())
  })

  afterAll(() => {
    mocked(useUsers).mockReset()
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
  })

  beforeEach(() => {
    mocked(attendancesStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it.each([
    [{}, { ...initParams }],
    [{ officeId: 2, isConfirmed: undefined }],
    [{ officeId: 2, isConfirmed: false }],
    [{ dateRangeType: 7, start: '', end: '' }, { dateRangeType: 7, ...thisWeekRange }],
    [{ ...nextWeekRange, isConfirmed: true, task: 101103, userId: 1, assigneeId: 2, assignerId: 2, dateRangeType: 3 }],
    [{ ...nextWeekRange, dateRangeType: 7 }]
  ])('should call attendancesStore.getIndex correct query with %s', (params: Record<string, unknown>, expected = params) => {
    const query = mapValues(params, x => isEmpty(x) ? '' : String(x))
    mountComponent({ query, isShallow: true })

    expect(attendancesStore.getIndex).toHaveBeenCalledTimes(1)
    expect(attendancesStore.getIndex).toHaveBeenCalledWith(createFormData({ ...initParams, ...expected }))

    unmountComponent()
  })

  describe('action button', () => {
    let checkboxWrapper: Wrapper<Vue>

    beforeAll(() => {
      mountComponent({ stubs: ['v-row', 'z-data-table-footer', 'z-fab', 'z-progress', 'z-cancel-confirm-dialog'] })
      checkboxWrapper = wrapper.find('.v-data-table-header .v-data-table__checkbox')
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should display actions menu when item selected', async () => {
      await click(() => checkboxWrapper)
      expect(wrapper).toMatchSnapshot()
      await click(() => checkboxWrapper)
    })

    it('should not display when no item selected', () => {
      const button = wrapper.find('[data-action-button]')
      expect(button).not.toExist()
    })

    it('should display when item selected', async () => {
      await click(() => checkboxWrapper)
      const button = wrapper.find('[data-action-button]')
      expect(button).toExist()
      await click(() => checkboxWrapper)
    })

    it('should be disabled when no action selected', async () => {
      await click(() => checkboxWrapper)
      const button = wrapper.find('[data-action-button]')
      expect(button).toBeDisabled()
      await click(() => checkboxWrapper)
    })

    it('should not be disabled when action selected', async () => {
      await click(() => checkboxWrapper)
      await setData(wrapper, { action: 'confirm' })
      const button = wrapper.find('[data-action-button]')
      expect(button).not.toBeDisabled()
      await click(() => checkboxWrapper)
    })

    it('should call method "doAction" when clicked', async () => {
      jest.spyOn(wrapper.vm, 'doAction').mockImplementation()
      await click(() => checkboxWrapper)
      await setData(wrapper, { action: 'delete' })

      await submit(() => wrapper.find('[data-action-form]'))

      expect(wrapper.vm.doAction).toHaveBeenCalledTimes(1)
      await click(() => checkboxWrapper)
      mocked(wrapper.vm.doAction).mockRestore()
    })
  })

  describe('doAction', () => {
    const token = '10'
    const ids = attendances.map(x => x.id)

    beforeAll(() => {
      const job = createJobStub(token, JobStatus.waiting)
      jest.spyOn($api.attendances, 'confirm').mockResolvedValue({ job })
      jest.spyOn($api.attendances, 'batchCancel').mockResolvedValue({ job })
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mocked(startJobPolling).mockImplementation(async init => await init())
      mountComponent({ isShallow: true })
    })

    afterAll(() => {
      unmountComponent()
      mocked(startJobPolling).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($confirm.show).mockRestore()
      mocked($api.attendances.batchCancel).mockRestore()
      mocked($api.attendances.confirm).mockRestore()
    })

    afterEach(() => {
      mocked(startJobPolling).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($confirm.show).mockClear()
      mocked($api.attendances.batchCancel).mockClear()
      mocked($api.attendances.confirm).mockClear()
    })

    describe('confirm attendance', () => {
      beforeEach(async () => {
        await setData(wrapper, { action: 'confirm', selected: attendances })
      })

      it('should display confirmation dialog', async () => {
        mocked($confirm.show).mockResolvedValueOnce(false)

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          color: colors.critical,
          message: '選択した勤務実績を確定します。\n\n本当によろしいですか？',
          positive: '確定'
        })
      })

      describe('not confirmed', () => {
        beforeEach(() => {
          mocked($confirm.show).mockResolvedValueOnce(false)
        })

        it('should not call any api when not confirmed', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($api.attendances.confirm).not.toHaveBeenCalled()
        })

        it('should not display snackbar when not confirmed', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).not.toHaveBeenCalled()
        })
      })

      describe('confirmed', () => {
        it('should call $api.attendances.confirm when the action is confirm', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($api.attendances.confirm).toHaveBeenCalledTimes(1)
          expect($api.attendances.confirm).toHaveBeenCalledWith({ ids })
        })

        it('should display snackbar when confirmation was successful', async () => {
          const job = createJobStub(token, JobStatus.success)
          mocked($api.attendances.confirm).mockResolvedValueOnce({ job })

          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).toHaveBeenCalledTimes(1)
          expect($snackbar.success).toHaveBeenCalledWith('勤務実績を確定しました。')
        })

        it('should display snackbar when confirmation was failure', async () => {
          const job = createJobStub(token, JobStatus.failure)
          mocked($api.attendances.confirm).mockResolvedValueOnce({ job })

          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.error).toHaveBeenCalledTimes(1)
          expect($snackbar.error).toHaveBeenCalledWith('勤務実績の確定に失敗しました。')
        })
      })
    })

    describe('cancel attendance', () => {
      beforeEach(async () => {
        await setData(wrapper, { action: 'cancel', selected: attendances })
      })

      it('should show cancel dialog', async () => {
        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })
        expect(dialog.props().active).toBeFalse()

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it('should call $api.attendances.batchCancel when positive clicked', async () => {
        const reason = 'キャンセルします'

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })

        await dialog.vm.$emit('click:positive', reason)

        expect($api.attendances.batchCancel).toHaveBeenCalledTimes(1)
        expect($api.attendances.batchCancel).toHaveBeenCalledWith({ ids, reason })
      })

      it('should not call $api.attendances.batchCancel when negative clicked', async () => {
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })

        await dialog.vm.$emit('click:negative')

        expect($api.attendances.batchCancel).not.toHaveBeenCalled()
      })

      it('should display snackbar when cancellation was successful', async () => {
        const job = createJobStub(token, JobStatus.success)
        mocked($api.attendances.batchCancel).mockResolvedValueOnce({ job })

        await wrapper.vm.onClickPositive('キャンセルします')

        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('勤務実績をキャンセルしました。')
      })

      it('should display snackbar when cancellation was failure', async () => {
        const job = createJobStub(token, JobStatus.failure)
        mocked($api.attendances.batchCancel).mockResolvedValueOnce({ job })

        await wrapper.vm.onClickPositive('キャンセルします')

        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('勤務実績のキャンセルに失敗しました。')
      })
    })
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [Permission.createAttendances]

    it('should be rendered when session auth is system admin', () => {
      mountComponent({ isShallow: true })
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should be rendered when the staff has permission: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent({ auth: { permissions }, isShallow: true })
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permission: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, isShallow: true })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })

  describe('actions form select items', () => {
    const expectedText = {
      default: 'アクションを選択...',
      update: '選択した勤務実績を確定する',
      cancel: '選択した勤務実績をキャンセルする'
    }
    const getActionsTexts = () => wrapper.vm.actions.map((x: VSelectOption) => x.text)
    const requiredPermissions: Permission[] = [Permission.updateAttendances]

    it('should be all rendered when staff is system admin', () => {
      mountComponent({ isShallow: true })
      expect(getActionsTexts()).toEqual([expectedText.default, expectedText.update, expectedText.cancel])
      unmountComponent()
    })

    it.each([
      [
        [expectedText.default, expectedText.update, expectedText.cancel],
        requiredPermissions
      ],
      [
        [expectedText.default],
        []
      ]
    ])('%s should be rendered when the staff has permission(s): %s', (texts, permissions) => {
      mountComponent({ auth: { permissions }, isShallow: true })
      expect(getActionsTexts()).toEqual(texts)
      unmountComponent()
    })

    it(`should be rendered only ${expectedText.default} when the staff does not have permissions: ${requiredPermissions.join(', ')}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, isShallow: true })
      expect(getActionsTexts()).toEqual([expectedText.default])
      unmountComponent()
    })
  })

  describe('check box in z-data-table', () => {
    const requiredPermissions: Permission[] = [Permission.updateAttendances]
    const stubs = ['v-row', 'z-data-table-footer', 'z-fab', 'z-progress', 'z-cancel-confirm-dialog']

    it('should be rendered when staff is system admin', () => {
      mountComponent({ stubs })
      expect(wrapper).toContainElement('[data-data-table] .v-data-table__checkbox')
      unmountComponent()
    })

    it('should be rendered when the staff has permission(s)', () => {
      mountComponent({ auth: { permissions: requiredPermissions }, stubs })
      expect(wrapper).toContainElement('[data-data-table] .v-data-table__checkbox')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions.join(', ')}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, stubs })
      expect(wrapper).not.toContainElement('[data-data-table] .v-data-table__checkbox')
      unmountComponent()
    })

    it('should not contain confirmed attendance when click checkbox', async () => {
      mountComponent()
      const checkboxWrapper = wrapper.find('[data-data-table] .v-data-table__checkbox')

      await click(() => checkboxWrapper)

      const selectedAttendance = wrapper.vm.selected.map((x: Attendance) => x.isConfirmed || x.isCanceled)
      expect(selectedAttendance).not.toContain(true)
      unmountComponent()
    })

    it('should not check when click on a confirmed attendance', async () => {
      mountComponent()
      const disabledCheckboxWrapper = wrapper.find('[data-data-table] .v-data-table__checkbox.v-simple-checkbox--disabled')

      await click(() => disabledCheckboxWrapper)

      const selectedAttendance = wrapper.vm.selected.map((x: Attendance) => x.isConfirmed || x.isCanceled)
      expect(selectedAttendance).toBeEmptyArray()
      unmountComponent()
    })
  })
})
