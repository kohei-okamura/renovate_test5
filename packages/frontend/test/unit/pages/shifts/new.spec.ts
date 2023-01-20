/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Activity } from '@zinger/enums/lib/activity'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { Task } from '@zinger/enums/lib/task'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { duration } from '~/models/duration'
import { HttpStatusCode } from '~/models/http-status-code'
import ShiftNewPage from '~/pages/shifts/new.vue'
import { ShiftsApi } from '~/services/api/shifts-api'
import { SnackbarService } from '~/services/snackbar-service'
import { CONTRACT_ID_MIN } from '~~/stubs/create-contract-stub'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createStaffStubs } from '~~/stubs/create-staff-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')
jest.mock('~/composables/use-users')

describe('pages/shifts/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('shifts')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const faker = createFaker('STAR WARS')
  const ramen = ramenIpsum.factory('DARK SIDE')
  const officeId = OFFICE_ID_MIN
  const staffs = createStaffStubs()
  const assignerId = staffs.find(x => x.officeIds.includes(officeId))!.id
  const assigneeId = staffs.find(x => x.officeIds.includes(officeId) && x.id !== assignerId)!.id
  const form: Partial<ShiftsApi.Form> = {
    officeId,
    userId: USER_ID_MIN,
    contractId: CONTRACT_ID_MIN,
    assignerId,
    task: Task.dwsVisitingCareForPwsd,
    assignees: [
      {
        staffId: assigneeId,
        isTraining: false,
        isUndecided: false
      }
    ],
    headcount: 1,
    schedule: {
      date: '2008-05-17',
      end: '19:00',
      start: '11:00'
    },
    durations: [
      duration(Activity.dwsVisitingCareForPwsd, 480)
    ],
    options: faker.randomElements(ServiceOption.values, faker.intBetween(0, ServiceOption.size)),
    note: ramen.ipsum(40)
  }
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: Partial<MountOptions<Vue>> = {}) {
    wrapper = mount(ShiftNewPage, {
      ...options,
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
    mocked(useUsers).mockReset()
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
  })

  beforeAll(() => {
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn($api.shifts, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($api.shifts.create).mockRestore()
    })

    afterEach(() => {
      mocked($router.replace).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($api.shifts.create).mockClear()
    })

    it('should call $api.shifts.create', async () => {
      await wrapper.vm.submit(form)

      expect($api.shifts.create).toHaveBeenCalledTimes(1)
      expect($api.shifts.create).toHaveBeenCalledWith({ form })
    })

    it('should display message', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('勤務シフト情報を登録しました。')
    })

    it.each([
      ['task', '勤務シフト区分を入力してください。'],
      // @TODO 初期表示時点ではサービスコードは非表示ため一旦テスト対象から除外する
      // ['serviceCode', 'サービスコードを入力してください。'],
      ['officeId', '事業所を入力してください。', 'office'],
      // @TODO 初期表示時点では利用者は非表示ため一旦テスト対象から除外する
      // ['userId', '利用者を入力してください。', 'user'],
      ['assignerId', '管理スタッフを入力してください。', 'assigner'],
      ['assignee.0.staffId', '担当スタッフ（1人目）を入力してください。', 'first-assignee'],
      // @TODO 初期表示時点では担当スタッフ（2人目）は非表示ため一旦テスト対象から除外する
      // ['assignee.1.staffId', '担当スタッフ（2人目）を入力してください。', 'second-assignee'],
      ['note', '備考を入力してください。'],
      ['schedule.date', '勤務日を入力してください。', 'schedule-date'],
      ['schedule.start', '勤務日（開始時間）を入力してください。', 'schedule-start'],
      ['schedule.end', '勤務日（終了時間）を入力してください。', 'schedule-end']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        jest.spyOn($api.shifts, 'create').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
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
     * @TODO こちらの項目はエラーメッセージがテキストに展開されないため別ケースにしている
     * FYI: <v-messages-stub color="error" value="身体介護を入力してください。"></v-messages-stub>
     */
    it.each([
      // @TODO 初期表示時点では身体介護、生活援助は非表示ため一旦テスト対象から除外する
      // ['durationPhysicalCare', '身体介護を入力してください。'],
      // ['durationHousework', '生活援助を入力してください。'],
      ['durationResting', '休憩を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.shifts, 'create').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
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
