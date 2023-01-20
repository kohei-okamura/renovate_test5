/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { datetime } from '~/composables/datetime'
import { shiftStateKey, ShiftStore, shiftStoreKey } from '~/composables/stores/use-shift-store'
import { time } from '~/composables/time'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { ISO_DATE_FORMAT, ISO_TIME_FORMAT } from '~/models/date'
import { HttpStatusCode } from '~/models/http-status-code'
import { Shift } from '~/models/shift'
import ShiftsEditPage from '~/pages/shifts/_id/edit.vue'
import { ShiftsApi } from '~/services/api/shifts-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createShiftResponseStub } from '~~/stubs/create-shift-response-stub'
import { createShiftStoreStub } from '~~/stubs/create-shift-store-stub'
import { createShiftStub, createShiftStubs } from '~~/stubs/create-shift-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')
jest.mock('~/composables/use-users')

describe('pages/shifts/_id/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('shifts')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }

  const createStubForm = (stub: Shift): ShiftsApi.Form => ({
    task: stub.task,
    serviceCode: stub.serviceCode,
    officeId: stub.officeId,
    userId: stub.userId,
    contractId: stub.contractId,
    assignerId: stub.assignerId,
    assignees: stub.assignees,
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

  let wrapper: Wrapper<Vue>

  function mountComponent (shiftStore: ShiftStore) {
    wrapper = mount(ShiftsEditPage, {
      ...provides(
        [shiftStateKey, shiftStore.state],
        [shiftStoreKey, shiftStore]
      ),
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
    const stub = createShiftStub(1, createContractStub())
    const form = createStubForm(stub)
    const shiftStore = createShiftStoreStub(createShiftResponseStub(stub.id))

    beforeAll(() => {
      jest.spyOn(shiftStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      mountComponent(shiftStore)
    })

    afterAll(() => {
      unmountComponent()
      mocked($snackbar.success).mockRestore()
      mocked(shiftStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked(shiftStore.update).mockClear()
    })

    /**
     * TODO submit 関連のテストではないので厳密にはここにおくべきではないけど、現状1ケースしかないのでここで済ませる
     */
    it('should be rendered correctly', () => {
      expect(wrapper).toMatchSnapshot()
    })

    it('should call shiftStore.update when pass the validation', async () => {
      await wrapper.vm.$data.submit(form)

      expect(shiftStore.update).toHaveBeenCalledTimes(1)
      expect(shiftStore.update).toHaveBeenCalledWith({ form, id: stub.id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.$data.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('勤務シフトを編集しました。')
    })
  })

  describe('error response from server(ltcsPhysicalCareAndHousework)', () => {
    // TODO できるだけ情報量の多いデータを使いたい。とりあえず ShiftId: 1722 （介保：身体・生活）を使用
    const stub = createShiftStubs(111)[110]
    const form = createStubForm(stub)
    const shiftStore = createShiftStoreStub(createShiftResponseStub(stub.id))

    beforeAll(() => {
      jest.spyOn(shiftStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      mountComponent(shiftStore)
    })

    afterAll(() => {
      unmountComponent()
      mocked($snackbar.success).mockRestore()
      mocked(shiftStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked(shiftStore.update).mockClear()
    })

    it.each([
      ['task', '勤務シフト区分を入力してください。'],
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
        mocked(shiftStore.update)
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { [key]: [message] }
          }))

        await wrapper.vm.$data.submit(form)
        await wrapper.vm.$nextTick()

        const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
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
        mocked(shiftStore.update)
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { [key]: [message] }
          }))

        await wrapper.vm.$data.submit(form)
        await wrapper.vm.$nextTick()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
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
    const stub = createShiftStubs(36)[35]
    const form = createStubForm(stub)
    const shiftStore = createShiftStoreStub(createShiftResponseStub(stub.id))

    beforeAll(() => {
      jest.spyOn(shiftStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      mountComponent(shiftStore)
    })

    afterAll(() => {
      unmountComponent()
      mocked($snackbar.success).mockRestore()
      mocked(shiftStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked(shiftStore.update).mockClear()
    })

    it.each([
      ['durationDwsOutingSupportForPwsd', '移動加算を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(shiftStore.update)
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: { [key]: [message] }
          }))

        await wrapper.vm.$data.submit(form)
        await wrapper.vm.$nextTick()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect(targetWrapper.html()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
