/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import { DateTime } from 'luxon'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { shiftStateKey, ShiftStore } from '~/composables/stores/use-shift-store'
import { useOffices } from '~/composables/use-offices'
import { useUsers } from '~/composables/use-users'
import { Auth } from '~/models/auth'
import { ISO_DATETIME_FORMAT } from '~/models/date'
import { HttpStatusCode } from '~/models/http-status-code'
import { Schedule } from '~/models/schedule'
import ShiftViewPage from '~/pages/shifts/_id/index.vue'
import { AlertService } from '~/services/alert-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createShiftResponseStub } from '~~/stubs/create-shift-response-stub'
import { createShiftStoreStub } from '~~/stubs/create-shift-store-stub'
import { createShiftStub, SHIFT_ID_MIN } from '~~/stubs/create-shift-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-users')

describe('pages/shifts/_id/index.vue', () => {
  const { shallowMount } = setupComponentTest()
  const $api = createMockedApi('shifts')
  const $router = createMockedRouter()
  const $alert = createMock<AlertService>()
  const $snackbar = createMock<SnackbarService>()
  const mocks = {
    $alert,
    $api,
    $router,
    $snackbar
  }
  const id = SHIFT_ID_MIN
  const response = createShiftResponseStub(id)
  const shiftStore = createShiftStoreStub(response)

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = {
    options?: MountOptions<Vue>
    auth?: Partial<Auth>
    localShiftStore?: ShiftStore
  }

  function mountComponent ({ options, auth, localShiftStore }: MountComponentArguments = {}) {
    wrapper = shallowMount(ShiftViewPage, {
      ...options,
      ...provides(
        [shiftStateKey, (localShiftStore ?? shiftStore).state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useUsers).mockReturnValue(createUseUsersStub())
  })

  afterAll(() => {
    mocked(useUsers).mockReset()
    mocked(useOffices).mockReset()
  })

  beforeEach(() => {
    jest.spyOn($alert, 'error').mockReturnValue()
    jest.spyOn($api.shifts, 'cancel').mockResolvedValue()
    jest.spyOn($snackbar, 'success').mockReturnValue()
  })

  afterEach(() => {
    mocked($snackbar.success).mockReset()
    mocked($api.shifts.cancel).mockReset()
    mocked($alert.error).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('FAB (speed dial)', () => {
    const baseShift = createShiftStub(1, createContractStub())
    const shift = { ...baseShift, ...{ isCanceled: false } }
    const localShiftStore = createShiftStoreStub({ shift })
    const requiredPermissions: Permission[] = [Permission.updateShifts]

    it('should be rendered when session auth is system admin', () => {
      mountComponent({ localShiftStore })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered when the staff has permission(s)', () => {
      mountComponent({ auth: { permissions: requiredPermissions }, localShiftStore })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, localShiftStore })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })

    it('should not be rendered when shift has been canceled', () => {
      const localShift = { ...baseShift, ...{ isCanceled: true } }
      const data = { shift: localShift }
      mountComponent({ localShiftStore: createShiftStoreStub(data) })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })

  describe('action', () => {
    const now = DateTime.fromISO('2018-05-01T09:00:00+0900', { locale: 'ja' })
    const baseShift = createShiftStub(id, createContractStub())
    const createLocalStore = (schedule?: Schedule) => {
      const shift = { ...baseShift, ...{ isCanceled: false }, ...(schedule ? { schedule } : {}) }
      return createShiftStoreStub({ shift })
    }

    beforeEach(() => {
      jest.spyOn($alert, 'error').mockReturnValue()
      jest.spyOn($api.shifts, 'cancel').mockResolvedValue()
      jest.spyOn($router, 'push')
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn(DateTime, 'local').mockReturnValue(now)
    })

    afterEach(() => {
      mocked($alert.error).mockReset()
      mocked($api.shifts.cancel).mockReset()
      mocked($router.push).mockReset()
      mocked($snackbar.error).mockReset()
      mocked($snackbar.success).mockReset()
      mocked(DateTime.local).mockReset()
    })

    describe('past shift', () => {
      const past = now.minus({ minutes: 1 })
      beforeAll(() => {
        const schedule = {
          date: past.toISODate(),
          start: past.toFormat(ISO_DATETIME_FORMAT),
          end: past.plus({ hours: 1 }).toFormat(ISO_DATETIME_FORMAT)
        }
        const localShiftStore = createLocalStore(schedule)
        mountComponent({ localShiftStore })
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should not be editable if shift is in the past.', async () => {
        await wrapper.vm.onClickEdit()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('過去の勤務シフトは編集できません。')
      })

      it('should not be able to cancel if shift is in the past.', async () => {
        await wrapper.vm.onClickCancel()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('過去の勤務シフトはキャンセルできません。')
      })

      it('should not be able to cancel if shift is in the past when confirm.', async () => {
        await wrapper.vm.onClickPositive('キャンセルします')
        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('過去の勤務シフトはキャンセルできません。')
      })
    })

    describe('future shift', () => {
      const future = now.plus({ minutes: 1 })

      beforeAll(() => {
        const schedule = {
          date: future.toISODate(),
          start: future.toFormat(ISO_DATETIME_FORMAT),
          end: future.plus({ hours: 1 }).toFormat(ISO_DATETIME_FORMAT)
        }
        const localShiftStore = createLocalStore(schedule)
        mountComponent({ localShiftStore })
      })

      afterAll(() => {
        unmountComponent()
      })

      it('should be editable if shift is in the past.', async () => {
        await wrapper.vm.onClickEdit()
        expect($router.push).toHaveBeenCalledTimes(1)
        expect($router.push).toHaveBeenCalledWith(`/shifts/${baseShift.id}/edit`)
      })

      it('should be able to cancel if shift is in the past.', async () => {
        await wrapper.vm.onClickCancel()
        // ダイアログの表示は 'cancel shift' で確認しているので、エラーが出ていないことのみ確認する
        expect($snackbar.error).not.toHaveBeenCalled()
      })

      it('should be able to cancel if shift is in the past when confirm.', async () => {
        await wrapper.vm.onClickPositive('キャンセルします')
        // キャンセル後の処理は 'cancel shift' で確認しているので、エラーが出ていないことのみ確認する
        expect($snackbar.error).not.toHaveBeenCalled()
      })
    })

    describe('cancel shift', () => {
      beforeAll(() => {
        const localShiftStore = createLocalStore()
        mountComponent({ localShiftStore })
      })

      afterAll(() => {
        unmountComponent()
      })

      beforeEach(() => {
        mocked($router.replace).mockClear()
      })

      it('should show cancel dialog', async () => {
        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })
        expect(dialog.props().active).toBeFalse()
        await wrapper.vm.onClickCancel()
        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it('should call $api.shifts.cancel when positive clicked', async () => {
        const reason = 'キャンセルします'
        await wrapper.vm.onClickCancel()
        const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })

        await dialog.vm.$emit('click:positive', reason)
        await wrapper.vm.$nextTick()

        expect($api.shifts.cancel).toHaveBeenCalledTimes(1)
        expect($api.shifts.cancel).toHaveBeenCalledWith({ id, reason })
      })

      it('should not call $api.shifts.cancel when negative clicked', async () => {
        await wrapper.vm.onClickCancel()
        await wrapper.findComponent({ name: 'z-cancel-confirm-dialog' }).vm.$emit('click:negative')
        await flushPromises()

        expect($api.shifts.cancel).not.toHaveBeenCalled()
      })

      it('should display snackbar when shift canceled', async () => {
        await wrapper.vm.onClickPositive('キャンセルします')
        await flushPromises()

        expect($snackbar.success).toHaveBeenCalledTimes(1)
        expect($snackbar.success).toHaveBeenCalledWith('勤務シフトをキャンセルしました。')
      })

      it('should not display snackbar when failed to cancel shift', async () => {
        // withAxios を使用している場合、BadRequest では onError に来ないため InternalServerError を使用する
        jest.spyOn($api.shifts, 'cancel').mockRejectedValue(createAxiosError(HttpStatusCode.InternalServerError))

        await wrapper.vm.onClickPositive('キャンセルします')
        await flushPromises()

        expect($alert.error).toHaveBeenCalledTimes(1)
        expect($snackbar.success).not.toHaveBeenCalled()
      })

      it('should call $router when shift canceled', async () => {
        await wrapper.vm.onClickPositive('キャンセルします')
        await flushPromises()

        expect($router.replace).toHaveBeenCalledTimes(1)
        expect($router.replace).toHaveBeenCalledWith('/shifts?restore=1')
      })

      it('should not call $router when failed to cancel shift', async () => {
        jest.spyOn($api.shifts, 'cancel').mockRejectedValue(createAxiosError(HttpStatusCode.InternalServerError))

        await wrapper.vm.onClickPositive('キャンセルします')
        await flushPromises()

        expect($router.replace).not.toHaveBeenCalled()
      })
    })
  })
})
