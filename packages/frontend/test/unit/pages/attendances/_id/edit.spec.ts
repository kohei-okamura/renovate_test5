/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { datetime } from '~/composables/datetime'
import { AttendanceStore, attendanceStoreKey } from '~/composables/stores/use-attendance-store'
import { time } from '~/composables/time'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { Attendance } from '~/models/attendance'
import { ISO_DATE_FORMAT, ISO_TIME_FORMAT } from '~/models/date'
import { HttpStatusCode } from '~/models/http-status-code'
import AttendancesEditPage from '~/pages/attendances/_id/edit.vue'
import { AttendancesApi } from '~/services/api/attendances-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createAttendanceResponseStub } from '~~/stubs/create-attendance-response-stub'
import { createAttendanceStoreStub } from '~~/stubs/create-attendance-store-stub'
import { createAttendanceStub, createAttendanceStubs } from '~~/stubs/create-attendance-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')
jest.mock('~/composables/use-users')

describe('pages/attendances/_id/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const createStubForm = (stub: Attendance): AttendancesApi.Form => ({
    task: stub.task,
    serviceCode: stub.serviceCode,
    officeId: stub.officeId,
    userId: stub.userId,
    contractId: stub.contractId,
    assignerId: stub.assignerId,
    assignees: [
      { ...(stub.assignees[0] ?? {}) },
      { ...(stub.assignees[1] ?? {}) }
    ],
    headcount: stub.headcount,
    schedule: {
      date: datetime(stub.schedule.date, ISO_DATE_FORMAT),
      start: time(stub.schedule.start, ISO_TIME_FORMAT),
      end: time(stub.schedule.end, ISO_TIME_FORMAT)
    },
    durations: [...stub.durations],
    options: [...stub.options],
    note: stub.note
  })

  // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
  // 参考:https://github.com/jsdom/jsdom/issues/1695
  Element.prototype.scrollIntoView = noop

  let wrapper: Wrapper<Vue & any>

  function mountComponent (attendanceStore: AttendanceStore) {
    wrapper = mount(AttendancesEditPage, {
      ...provides([attendanceStoreKey, attendanceStore]),
      mocks
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
    mocked(useUsers).mockRestore()
    mocked(useStaffs).mockRestore()
    mocked(useOffices).mockRestore()
  })

  describe('submit(ltcsPhysicalCareAndHousework)', () => {
    const stub = createAttendanceStub(1, createContractStub())
    const form = createStubForm(stub)
    const attendanceStore = createAttendanceStoreStub(createAttendanceResponseStub(stub.id))

    beforeAll(() => {
      jest.spyOn(attendanceStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      mountComponent(attendanceStore)
    })

    afterAll(() => {
      unmountComponent()
      mocked($snackbar.success).mockRestore()
      mocked(attendanceStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked(attendanceStore.update).mockClear()
    })

    /**
     * TODO submit 関連のテストではないので厳密にはここにおくべきではないけど、現状1ケースしかないのでここで済ませる
     */
    it('should be rendered correctly', () => {
      expect(wrapper).toMatchSnapshot()
    })

    it('should call attendanceStore.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect(attendanceStore.update).toHaveBeenCalledTimes(1)
      expect(attendanceStore.update).toHaveBeenCalledWith({ form, id: stub.id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('勤務実績を編集しました。')
    })
  })

  describe('error response from server(ltcsPhysicalCareAndHousework)', () => {
    // TODO できるだけ情報量の多いデータを使いたい。とりあえず Id: 1722 （介保：身体・生活）を使用
    const stub = createAttendanceStubs(111)[110]
    const form = createStubForm(stub)
    const attendanceStore = createAttendanceStoreStub(createAttendanceResponseStub(stub.id))

    beforeAll(() => {
      jest.spyOn(attendanceStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mountComponent(attendanceStore)
    })

    afterAll(() => {
      unmountComponent()
      mocked($snackbar.error).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked(attendanceStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.error).mockClear()
      mocked($snackbar.success).mockClear()
      mocked(attendanceStore.update).mockClear()
    })

    it.each([
      ['task', '勤務実績区分を入力してください。'],
      ['serviceCode', 'サービスコードを入力してください。'],
      ['officeId', '事業所を入力してください。', 'office'],
      ['userId', '利用者を入力してください。', 'user'],
      ['assignerId', '管理スタッフを入力してください。', 'assigner'],
      ['assignee.0.staffId', '担当スタッフ（1人目）を入力してください。', 'first-assignee'],
      ['assignee.1.staffId', '担当スタッフ（2人目）を入力してください。', 'second-assignee'],
      ['note', '備考を入力してください。'],
      ['schedule.date', '勤務日を入力してください。', 'schedule-date'],
      ['schedule.start', '勤務日（開始時間）を入力してください。', 'schedule-start'],
      ['schedule.end', '勤務日（終了時間）を入力してください。', 'schedule-end']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        mocked(attendanceStore.update)
          .mockReset()
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { [key]: [message] }
          }))

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )

    /**
     * TODO こちらの項目はエラーメッセージがテキストに展開されないため別ケースにしている
     * FYI: <v-messages-stub color="error" value="身体介護を入力してください。"></v-messages-stub>
     */
    it.each([
      ['durationPhysicalCare', '身体介護を入力してください。'],
      ['durationHousework', '生活援助を入力してください。'],
      ['durationResting', '休憩を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(attendanceStore.update)
          .mockReset()
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { [key]: [message] }
          }))

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.html()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })

  /*
   * 重度訪問介護にしか表示されない項目があるので、別ケースで補完する
   * 介保：身体・生活と同じ項目については省略する
   */
  describe('submit(dwsVisitingCareForPwsd)', () => {
    const stub = createAttendanceStubs(36)[35]
    const form = createStubForm(stub)
    const attendanceStore = createAttendanceStoreStub(createAttendanceResponseStub(stub.id))

    beforeAll(() => {
      jest.spyOn(attendanceStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      mountComponent(attendanceStore)
    })

    afterAll(() => {
      unmountComponent()
      mocked(attendanceStore.update).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
    })

    afterEach(() => {
      mocked(attendanceStore.update).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
    })

    it.each([
      ['durationDwsOutingSupportForPwsd', '移動加算を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(attendanceStore.update)
          .mockReset()
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { [key]: [message] }
          }))

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.html()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
