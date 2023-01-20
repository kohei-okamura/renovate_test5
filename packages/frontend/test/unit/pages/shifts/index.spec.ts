/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { colors } from '~/colors'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { shiftsStoreKey } from '~/composables/stores/use-shifts-store'
import { CancelJobPolling, StartJobPolling, useJobPolling } from '~/composables/use-job-polling'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { Auth } from '~/models/auth'
import { createDateRange } from '~/models/date-range'
import { Shift } from '~/models/shift'
import { VSelectOption } from '~/models/vuetify'
import ShiftsIndexPage from '~/pages/shifts/index.vue'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { RouteQuery } from '~/support/router/types'
import { mapValues } from '~/support/utils/map-values'
import { createJobStub } from '~~/stubs/create-job-stub'
import { createShiftStubs } from '~~/stubs/create-shift-stub'
import { createShiftsStoreStub } from '~~/stubs/create-shifts-store-stub'
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

describe('pages/shifts/index.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const { objectContaining } = expect
  const $api = createMockedApi('shifts')
  const $confirm = createMock<ConfirmDialogService>()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const shifts = createShiftStubs(10)
  const shiftsStore = createShiftsStoreStub({ shifts })
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

  type MountComponentParams = MountOptions<Vue> & {
    auth?: Partial<Auth>
    isShallow?: true
    query?: RouteQuery
  }

  function mountComponent ({ auth, isShallow, query, ...options }: MountComponentParams = {}) {
    mocked(useJobPolling).mockReturnValue({
      cancelJobPolling,
      startJobPolling
    })
    const fn = isShallow ? shallowMount : mount
    const $routes = createMockedRoutes({ query: query ?? {} })
    wrapper = fn(ShiftsIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })],
        [shiftsStoreKey, shiftsStore]
      ),
      ...options,
      mocks: {
        $api,
        $confirm,
        $router,
        $routes,
        $snackbar,
        ...options?.mocks
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
    mocked(shiftsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call shiftsStore.getIndex', () => {
    mountComponent({ isShallow: true })
    expect(shiftsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(shiftsStore.getIndex).toHaveBeenCalledWith(objectContaining({ page: 1 }))
    unmountComponent()
  })

  it.each([
    [{}, { ...initParams }],
    [{ officeId: 2, isConfirmed: undefined }],
    [{ officeId: 2, isConfirmed: false }],
    [{ dateRangeType: 7, start: '', end: '' }, { dateRangeType: 7, ...thisWeekRange }],
    [{ ...nextWeekRange, isConfirmed: true, task: 101103, userId: 1, assigneeId: 2, assignerId: 2, dateRangeType: 3 }],
    [{ ...nextWeekRange, dateRangeType: 7 }]
  ])('should call shiftsStore.getIndex correct query with %s', (params: Record<string, unknown>, expected = params) => {
    const query = mapValues(params, x => isEmpty(x) ? '' : String(x))
    mountComponent({ isShallow: true, query })

    expect(shiftsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(shiftsStore.getIndex).toHaveBeenCalledWith(createFormData({ ...initParams, ...expected }))

    unmountComponent()
  })

  describe('action button', () => {
    let button: Wrapper<any>
    let checkboxWrapper: Wrapper<any>

    beforeAll(() => {
      mountComponent({
        stubs: [
          'v-row',
          'v-col',
          'z-select-search-condition',
          'z-keyword-filter-autocomplete',
          'z-select',
          'v-fade-transition',
          'z-data-table-footer',
          'z-cancel-confirm-dialog'
        ]
      })
      checkboxWrapper = wrapper.find('.v-data-table-header .v-data-table__checkbox')
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should not display when no item selected', () => {
      button = wrapper.find('[data-action-button]')
      expect(button).not.toExist()
    })

    it('should display when item selected', async () => {
      await click(() => checkboxWrapper)
      button = wrapper.find('[data-action-button]')
      expect(button).toExist()
      await click(() => checkboxWrapper)
    })

    it('should be disabled when no action selected', async () => {
      await click(() => checkboxWrapper)
      button = wrapper.find('[data-action-button]')
      expect(button).toBeDisabled()
      await click(() => checkboxWrapper)
    })

    it('should not be disabled when action selected', async () => {
      await click(() => checkboxWrapper)
      await setData(wrapper, { action: 'confirm' })
      button = wrapper.find('[data-action-button]')
      expect(button).not.toBeDisabled()
      await click(() => checkboxWrapper)
    })

    it('should call method "doAction" when clicked', async () => {
      jest.spyOn(wrapper.vm, 'doAction').mockImplementation()
      await click(() => checkboxWrapper)
      await setData(wrapper, { action: 'cancel' })

      await submit(() => wrapper.find('[data-action-form]'))

      expect(wrapper.vm.doAction).toHaveBeenCalledTimes(1)
      await click(() => checkboxWrapper)
      mocked(wrapper.vm.doAction).mockRestore()
    })
  })

  describe('doAction', () => {
    const token = '10'
    const ids = shifts.map(x => x.id)

    beforeAll(() => {
      mountComponent({ isShallow: true })
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeAll(() => {
      const job = createJobStub(token, JobStatus.waiting)
      jest.spyOn($api.shifts, 'confirm').mockResolvedValue({ job })
      jest.spyOn($api.shifts, 'batchCancel').mockResolvedValue({ job })
      jest.spyOn($confirm, 'show').mockResolvedValue(true)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mocked(startJobPolling).mockImplementation(async init => await init())
    })

    afterAll(() => {
      mocked(startJobPolling).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($confirm.show).mockRestore()
      mocked($api.shifts.batchCancel).mockRestore()
      mocked($api.shifts.confirm).mockRestore()
    })

    afterEach(() => {
      mocked(startJobPolling).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($confirm.show).mockClear()
      mocked($api.shifts.batchCancel).mockClear()
      mocked($api.shifts.confirm).mockClear()
    })

    describe('confirm shift', () => {
      beforeEach(() => {
        setData(wrapper, { action: 'confirm', selected: shifts })
      })

      it('should display confirmation dialog', async () => {
        mocked($confirm.show).mockResolvedValueOnce(false)

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        expect($confirm.show).toHaveBeenCalledTimes(1)
        expect($confirm.show).toHaveBeenCalledWith({
          color: colors.critical,
          message: '選択した勤務シフトを確定します。\n\n本当によろしいですか？',
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

          expect($api.shifts.confirm).not.toHaveBeenCalled()
        })

        it('should not display snackbar when not confirmed', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).not.toHaveBeenCalled()
        })
      })

      describe('confirmed', () => {
        it('should call $api.shifts.confirm when the action is confirm', async () => {
          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($api.shifts.confirm).toHaveBeenCalledTimes(1)
          expect($api.shifts.confirm).toHaveBeenCalledWith({ ids })
        })

        it('should display snackbar when confirmation was successful', async () => {
          const job = createJobStub(token, JobStatus.success)
          jest.spyOn($api.shifts, 'confirm').mockResolvedValueOnce({ job })

          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.success).toHaveBeenCalledTimes(1)
          expect($snackbar.success).toHaveBeenCalledWith('勤務シフトを確定しました。')
        })

        it('should display snackbar when confirmation was failure', async () => {
          const job = createJobStub(token, JobStatus.failure)
          jest.spyOn($api.shifts, 'confirm').mockResolvedValueOnce({ job })

          await wrapper.vm.doAction()
          await wrapper.vm.$nextTick()

          expect($snackbar.error).toHaveBeenCalledTimes(1)
          expect($snackbar.error).toHaveBeenCalledWith('勤務シフトの確定に失敗しました。')
        })
      })
    })

    describe('cancel shift', () => {
      beforeEach(() => {
        setData(wrapper, { action: 'cancel', selected: shifts })
      })

      it('should show cancel dialog', async () => {
        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })
        expect(dialog.props().active).toBeFalse()

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it('should call $api.shifts.batchCancel when positive clicked', async () => {
        const reason = 'キャンセルします'

        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })

        await dialog.vm.$emit('click:positive', reason)

        expect($api.shifts.batchCancel).toHaveBeenCalledTimes(1)
        expect($api.shifts.batchCancel).toHaveBeenCalledWith({ ids, reason })
      })

      it('should not call $api.shifts.batchCancel when negative clicked', async () => {
        await wrapper.vm.doAction()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })

        await dialog.vm.$emit('click:negative')

        expect($api.shifts.batchCancel).not.toHaveBeenCalled()
      })

      it('should display snackbar when cancellation was successful', async () => {
        const job = createJobStub(token, JobStatus.success)
        jest.spyOn($api.shifts, 'batchCancel').mockResolvedValueOnce({ job })

        await wrapper.vm.onClickPositive('キャンセルします')

        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('勤務シフトをキャンセルしました。')
      })

      it('should display snackbar when cancellation was failure', async () => {
        const job = createJobStub(token, JobStatus.failure)
        jest.spyOn($api.shifts, 'batchCancel').mockResolvedValueOnce({ job })

        await wrapper.vm.onClickPositive('キャンセルします')

        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('勤務シフトのキャンセルに失敗しました。')
      })
    })
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [
      Permission.createShifts,
      Permission.importShifts
    ]

    it('should be rendered when session auth is system admin', () => {
      mountComponent({ isShallow: true })
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should be rendered when the staff has permission: ${requiredPermissions}`, () => {
      const auth = {
        permissions: requiredPermissions
      }
      mountComponent({ auth, isShallow: true })
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permission: ${requiredPermissions}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }
      mountComponent({ auth, isShallow: true })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })

  describe('actions form select items', () => {
    const expectedText = {
      default: 'アクションを選択...',
      update: '選択した勤務シフトを確定する',
      cancel: '選択した勤務シフトをキャンセルする'
    }
    const getActionsTexts = () => wrapper.vm.actions.map((x: VSelectOption) => x.text)
    const requiredPermissions: Permission[] = [Permission.updateShifts]

    it('should be all rendered when session auth is system admin', () => {
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
      const auth = { permissions }
      mountComponent({ auth, isShallow: true })
      expect(getActionsTexts()).toEqual(texts)
      unmountComponent()
    })

    it(`should be rendered only ${expectedText.default} when the staff does not have permissions: ${requiredPermissions.join(', ')}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }
      mountComponent({ auth, isShallow: true })
      expect(getActionsTexts()).toEqual([expectedText.default])
      unmountComponent()
    })
  })

  describe('check box in z-data-table', () => {
    const requiredPermissions: Permission[] = [Permission.updateShifts]

    function mountWithStubs (params?: MountComponentParams) {
      mountComponent({
        ...params,
        stubs: [
          'v-form',
          'v-row',
          'v-col',
          'z-select-search-condition',
          'z-keyword-filter-autocomplete',
          'z-select',
          'v-fade-transition',
          'z-data-table-footer',
          'z-cancel-confirm-dialog'
        ]
      })
    }

    it('should be all rendered when session auth is system admin', () => {
      mountWithStubs()
      expect(wrapper).toContainElement('[data-data-table] .v-data-table__checkbox')
      unmountComponent()
    })

    it('should be rendered when the staff has permission(s)', () => {
      const auth = {
        permissions: requiredPermissions
      }
      mountWithStubs({ auth })
      expect(wrapper).toContainElement('[data-data-table] .v-data-table__checkbox')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions.join(', ')}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }
      mountWithStubs({ auth })
      expect(wrapper).not.toContainElement('[data-data-table] .v-data-table__checkbox')
      unmountComponent()
    })

    it('should not contain confirmed shift when click checkbox', async () => {
      mountWithStubs()
      const checkboxWrapper = wrapper.find('[data-data-table] .v-data-table__checkbox')

      await click(() => checkboxWrapper)

      const selectedShift = wrapper.vm.selected.map((x: Shift) => x.isConfirmed || x.isCanceled)
      expect(selectedShift).not.toContain(true)
      unmountComponent()
    })

    it('should not check when click on a confirmed shift', async () => {
      mountWithStubs()
      const disabledCheckboxWrapper = wrapper.find('[data-data-table] .v-data-table__checkbox.v-simple-checkbox--disabled')

      await click(() => disabledCheckboxWrapper)

      const selectedShift = wrapper.vm.selected.map((x: Shift) => x.isConfirmed || x.isCanceled)
      expect(selectedShift).toBeEmptyArray()
      unmountComponent()
    })
  })
})
