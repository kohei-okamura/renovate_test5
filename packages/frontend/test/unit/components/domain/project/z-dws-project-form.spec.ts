/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZDwsProjectForm from '~/components/domain/project/z-dws-project-form.vue'
import { dwsProjectServiceMenuResolverStateKey } from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { dwsProjectStateKey } from '~/composables/stores/use-dws-project-store'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { DwsProjectProgram } from '~/models/dws-project-program'
import { Plugins } from '~/plugins'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createDwsProjectResponseStub } from '~~/stubs/create-dws-project-response-stub'
import {
  createDwsProjectServiceMenuResolverStoreStub
} from '~~/stubs/create-dws-project-service-menu-resolver-store-stub'
import { createDwsProjectServiceMenuStubs } from '~~/stubs/create-dws-project-service-menu-stub'
import { createDwsProjectStoreStub } from '~~/stubs/create-dws-project-store-stub'
import { createDwsProjectStub } from '~~/stubs/create-dws-project-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createUserStub, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('z-dws-project-form.vue', () => {
  const { mount } = setupComponentTest()
  const $confirm = createMock<ConfirmDialogService>()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: DwsProjectsApi.Form = {
    effectivatedOn: '2001-01-31',
    objective: 'チャーシューマシマシで。味噌に野菜が魚介系がつけめんにチャーシューは細麺とラーメンに雲呑を注文する。ネギの替え玉とメンマも好きだ。メンマがラーメンと魚粉と海苔が油そばの替え玉がつけめんはわかめのもやしの高菜にしよう。焼豚だった。担々麺の太麺がこってりがこってりのネギの魚粉にしよう。チャーシューマシマシで。ネギの、中太麺は担々麺ともやしに海苔を注文する。太麺が替え玉マシマシで。',
    officeId: 8,
    staffId: 6,
    programs: [
      {
        summaryIndex: 1,
        category: 11,
        recurrence: 24,
        dayOfWeeks: [1, 3, 4, 5, 6, 7],
        slot: { start: '08: 48', end: '08: 57' },
        headcount: 1,
        options: [300003, 301102, 301104, 301105, 301106],
        contents: [
          { menuId: 54, content: '味噌も好きだ。', memo: '紅ショウガだった。' },
          { menuId: 85, content: '雲呑も好きだ。', memo: '味玉マシマシで。' }
        ]
      }
    ],
    requestFromFamily: '海苔にちぢれ麺が太麺とタマネギの焼きラーメンと、塩が好きだ。ちぢれ麺とあっさりにほうれん草が食べたい。味噌のワンタンメンマシマシで。味噌とサンマーメンにもやしも好きだ。魚粉と魚粉とちぢれ麺に焼豚がほうれん草も好きだ。野菜とワンタンがワンタンと海苔が、もやしが紅ショウガと、魚粉に醤油がニンニクも好きだ。とんこつは魚粉は油そばにちぢれ麺とチャーシューメンが紅ショウガのワンタンも好きだ。',
    requestFromUser: '味玉はこってりに醤油があっさりに海苔がメンマに、タマネギが食べたい。冷やし中華にワンタンメンに、味噌を注文する。雲呑のメンマにタンメンが魚介系がつけめんのつけめんに魚介系は、つけめんと高菜を注文する。太麺は魚介系がメンマがメンマに替え玉が好きだ。チャーシューはワンタンがわかめのワンタンとあっさりも好きだ。こってりの替え玉はもやしが中太麺が好きだ。背脂が食べたい。太麺が食べたい。',
    writtenOn: '1980-03-21'
  }
  const mocks: Partial<Plugins> = {
    $confirm,
    $form,
    $snackbar
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    permission: Permission.updateDwsProjects,
    progress: false,
    user: createUserStub(),
    value: { ...form }
  }
  const userId = USER_ID_MIN
  const stub = createDwsProjectStub()
  const dwsProjectResponse = createDwsProjectResponseStub(stub.id)
  const dwsProjectStore = createDwsProjectStoreStub(dwsProjectResponse)
  const menuResolverStore = createDwsProjectServiceMenuResolverStoreStub({
    menus: createDwsProjectServiceMenuStubs()
  })
  const userResponse = createUserResponseStub(userId)
  const userStore = createUserStoreStub(userResponse)
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZDwsProjectForm, {
      ...options,
      mocks,
      ...provides(
        [dwsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [dwsProjectStateKey, dwsProjectStore.state],
        [ownExpenseProgramResolverStateKey, ownExpenseProgramResolverStore.state],
        [userStateKey, userStore.state]
      ),
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    jest.spyOn($confirm, 'show').mockResolvedValue(true)
    jest.spyOn($snackbar, 'success').mockReturnValue()
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useStaffs).mockReturnValue(createUseStaffsStub())
  })

  afterAll(() => {
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
    mocked($snackbar.success).mockReset()
    mocked($confirm.show).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  const initProgram: DeepPartial<DwsProjectProgram> = {
    summaryIndex: 1,
    category: 11,
    recurrence: 24,
    dayOfWeeks: [1, 3, 4, 5, 6, 7],
    slot: {
      start: '08: 48',
      end: '09: 59'
    },
    headcount: 1,
    options: [300003, 301102, 301104, 301105, 301106],
    contents: [
      { menuId: 54, content: '味噌も好きだ。', duration: 39, memo: '紅ショウガだった。' },
      { menuId: 85, content: '雲呑も好きだ。', duration: 32, memo: '味玉マシマシで。' }
    ]
  }

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values?: Partial<DwsProjectsApi.Form>) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
      jest.runOnlyPendingTimers()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when officeId is empty', async () => {
      await validate({
        officeId: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-office-id] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when staffId is empty', async () => {
      await validate({
        staffId: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-staff-id] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when writtenOn is empty', async () => {
      await validate({
        writtenOn: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-written-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when effectivatedOn is empty', async () => {
      await validate({
        effectivatedOn: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-effectivated-on] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when requestFromUser is empty', async () => {
      await validate({
        requestFromUser: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-request-from-user] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when requestFromFamily is empty', async () => {
      await validate({
        requestFromFamily: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-request-from-family] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when objective is empty', async () => {
      await validate({
        objective: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-objective] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when programs dayOfWeeks is empty', async () => {
      await validate({
        programs: [{
          ...initProgram,
          dayOfWeeks: []
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-program-day-of-week] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when programs slotStart is empty', async () => {
      await validate({
        programs: [{
          ...initProgram,
          slot: { start: '', end: '21:51' }
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-program-slot-start] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when programs slotEnd is empty', async () => {
      await validate({
        programs: [{
          ...initProgram,
          slot: { start: '21:03', end: '' }
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-program-slot-end] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when programs contents is 0', async () => {
      await validate({
        programs: [{
          ...initProgram,
          contents: [{ content: '', duration: 10, memo: 'asdf' }]
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-empty-service-content]').text())
        .toBe('サービス内容を入力してください。')
      expect(wrapper.find('[data-program-contents-menu-id] .error--text')).toExist()
    })

    it('should fail when programs note is over 255 characters', async () => {
      await validate({
        programs: [{
          ...initProgram,
          note: 'a'.repeat(256)
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-note] .v-messages__message').text())
        .toBe('255文字以内で入力してください。')
    })

    it('should fail when programs contents content is over 255 characters', async () => {
      await validate({
        programs: [{
          ...initProgram,
          contents: [{
            menuId: 16,
            content: 'a'.repeat(256),
            duration: 10,
            memo: ''
          }]
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-program-contents-content] .v-messages__message').text())
        .toBe('255文字以内で入力してください。')
    })

    it('should fail when programs contents content is empty', async () => {
      await validate({
        programs: [{
          ...initProgram,
          contents: [{ content: '' }]
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-program-contents-content] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when programs contents memo is over 255 characters', async () => {
      await validate({
        programs: [{
          ...initProgram,
          contents: [{
            menuId: 16,
            content: '',
            duration: 10,
            memo: 'a'.repeat(256)
          }]
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-program-contents-memo] .v-messages__message').text())
        .toBe('255文字以内で入力してください。')
    })

    it('should fail when programs contents menuId is empty', async () => {
      await validate({
        programs: [{
          ...initProgram,
          contents: [
            { duration: 25, memo: '' },
            { menuId: 16, duration: 23, memo: '' }
          ]
        }]
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-empty-service-content]').text())
        .toBe('サービス内容を入力してください。')
    })

    it('should fail when programs is empty', async () => {
      await validate({
        programs: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-programs] .v-alert__content').text()).toBe('週間サービス計画表を1つ以上追加してください。')
    })
  })
})
