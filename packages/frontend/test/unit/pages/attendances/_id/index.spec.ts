/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { attendanceStateKey, AttendanceStore } from '~/composables/stores/use-attendance-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import AttendanceViewPage from '~/pages/attendances/_id/index.vue'
import { AlertService } from '~/services/alert-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createAttendanceResponseStub } from '~~/stubs/create-attendance-response-stub'
import { createAttendanceStoreStub } from '~~/stubs/create-attendance-store-stub'
import { ATTENDANCE_ID_MIN, createAttendanceStub } from '~~/stubs/create-attendance-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/attendances/_id/index.vue', () => {
  const { shallowMount } = setupComponentTest()
  const $api = createMockedApi('attendances')
  const $router = createMockedRouter()
  const $alert = createMock<AlertService>()
  const $snackbar = createMock<SnackbarService>()
  const mocks = {
    $alert,
    $api,
    $router,
    $snackbar
  }
  const id = ATTENDANCE_ID_MIN
  const attendanceStore = createAttendanceStoreStub(createAttendanceResponseStub(id))

  let wrapper: Wrapper<Vue>

  type MountComponentArguments = {
    options?: MountOptions<Vue>
    auth?: Partial<Auth>
    localAttendanceStore?: AttendanceStore
  }

  function mountComponent ({ options, auth, localAttendanceStore }: MountComponentArguments = {}) {
    wrapper = shallowMount(AttendanceViewPage, {
      ...provides(
        [attendanceStateKey, (localAttendanceStore ?? attendanceStore).state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
      ),
      ...options,
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    jest.spyOn($alert, 'error').mockReturnValue()
    jest.spyOn($api.attendances, 'cancel').mockResolvedValue()
    jest.spyOn($snackbar, 'success').mockReturnValue()
  })

  afterEach(() => {
    mocked($snackbar.success).mockReset()
    mocked($api.attendances.cancel).mockReset()
    mocked($alert.error).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('cancel attendance', () => {
    beforeEach(async () => {
      mocked($router.replace).mockClear()
      await mountComponent()
    })

    afterEach(() => {
      unmountComponent()
    })

    it('should show cancel dialog', async () => {
      const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })
      expect(dialog.props().active).toBeFalse()
      await wrapper.vm.$data.onClickCancel()
      // props.active が true になっていることを確認する
      expect(dialog.props().active).toBeTrue()
    })

    it('should call $api.attendances.cancel when positive clicked', async () => {
      const reason = 'キャンセルします'
      await wrapper.vm.$data.onClickCancel()
      const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })
      await dialog.vm.$emit('click:positive', reason)
      expect($api.attendances.cancel).toHaveBeenCalledTimes(1)
      expect($api.attendances.cancel).toHaveBeenCalledWith({ id, reason })
    })

    it('should not call $api.attendances.cancel when negative clicked', async () => {
      await wrapper.vm.$data.onClickCancel()
      const dialog = wrapper.findComponent({ name: 'z-cancel-confirm-dialog' })
      await dialog.vm.$emit('click:negative')
      expect($api.attendances.cancel).not.toHaveBeenCalled()
    })

    it('should display snackbar when attendance canceled', async () => {
      await wrapper.vm.$data.onClickPositive('キャンセルします')
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('勤務実績をキャンセルしました。')
    })

    it('should not display snackbar when failed to cancel attendance', async () => {
      // withAxios を使用している場合、BadRequest では onError に来ないため InternalServerError を使用する
      jest.spyOn($api.attendances, 'cancel').mockRejectedValue(createAxiosError(HttpStatusCode.InternalServerError))
      await wrapper.vm.$data.onClickPositive('キャンセルします')
      expect($alert.error).toHaveBeenCalledTimes(1)
      expect($snackbar.success).not.toHaveBeenCalled()
    })

    it('should call $router when attendance canceled', async () => {
      await wrapper.vm.$data.onClickPositive('キャンセルします')
      expect($router.replace).toHaveBeenCalledTimes(1)
      expect($router.replace).toHaveBeenCalledWith('/attendances?restore=1')
    })

    it('should not call $router when failed to cancel attendance', async () => {
      jest.spyOn($api.attendances, 'cancel').mockRejectedValue(createAxiosError(HttpStatusCode.InternalServerError))
      await wrapper.vm.$data.onClickPositive('キャンセルします')
      expect($router.replace).not.toHaveBeenCalled()
    })
  })

  describe('FAB (speed dial)', () => {
    const baseAttendance = createAttendanceStub(1, createContractStub())
    const attendance = { ...baseAttendance, ...{ isCanceled: false } }
    const localAttendanceStore = createAttendanceStoreStub({ attendance })
    const requiredPermissions: Permission[] = [Permission.updateAttendances]

    it('should be rendered when session auth is system admin', () => {
      mountComponent({ localAttendanceStore })
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it('should be rendered when the staff has permission(s)', () => {
      mountComponent({ auth: { permissions: requiredPermissions }, localAttendanceStore })
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({ auth: { permissions }, localAttendanceStore })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })

    it('should not be rendered when attendance has been canceled', () => {
      const localAttendance = { ...baseAttendance, ...{ isCanceled: true } }
      const data = { attendance: localAttendance }
      mountComponent({ localAttendanceStore: createAttendanceStoreStub(data) })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })
})
