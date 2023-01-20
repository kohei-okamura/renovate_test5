/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZLtcsProjectForm from '~/components/domain/project/z-ltcs-project-form.vue'
import {
  ltcsProjectServiceMenuResolverStateKey
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { ltcsProjectStateKey } from '~/composables/stores/use-ltcs-project-store'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { LtcsProjectProgram } from '~/models/ltcs-project-program'
import { Plugins } from '~/plugins'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'
import { ConfirmDialogService } from '~/services/confirm-dialog-service'
import { SnackbarService } from '~/services/snackbar-service'
import { ValidationObserverInstance } from '~/support/validation/types'
import {
  createLtcsHomeVisitLongTermCareDictionaryIndexResponseStub
} from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-index-response-stub'
import { createLtcsProjectResponseStub } from '~~/stubs/create-ltcs-project-response-stub'
import { createLtcsProjectServiceMenuStubs } from '~~/stubs/create-ltcs-project-service-menu-stub'
import {
  createLtcsProjectServiceMenusResolverStoreStub
} from '~~/stubs/create-ltcs-project-service-menus-resolver-store-stub'
import { createLtcsProjectStoreStub } from '~~/stubs/create-ltcs-project-store-stub'
import { createLtcsProjectStub } from '~~/stubs/create-ltcs-project-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createUserStub, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('z-ltcs-project-form.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsHomeVisitLongTermCareDictionary')
  const $confirm = createMock<ConfirmDialogService>()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: LtcsProjectsApi.Form = {
    effectivatedOn: '1971-03-24',
    longTermObjective: {
      term: { start: '1984-11-21T13:38:31+0900', end: '2006-04-16T02:28:45+0900' },
      text: 'ネギがわかめがとんこつが好きだ。'
    },
    contractId: 2,
    officeId: 22,
    problem: '醤油は太麺が醤油が、背脂がタマネギに油そばの担々麺のネギにしよう。',
    programs: [
      {
        programIndex: 1,
        category: 11,
        recurrence: 23,
        timeframe: 9,
        dayOfWeeks: [1, 2, 5],
        slot: { start: '01:46', end: '03:13' },
        amounts: [{ category: 11, amount: 87 }],
        headcount: 1,
        serviceCode: '111211',
        options: [100002, 300002, 301104],
        contents: [
          {
            menuId: 49,
            duration: 37,
            content: '',
            memo: 'もやしが好きだ。'
          },
          {
            menuId: 62,
            duration: 50,
            content: 'ワンタンメンだった。',
            memo: 'ちぢれ麺が好きだ。'
          }
        ]
      }
    ],
    requestFromFamily: 'ニンニクと冷やし中華だった。高菜のワンタンメンのこってりマシマシで。冷やし中華が好きだ。味玉が塩と醤油と、ネギともやしと油そばはちぢれ麺の鶏白湯につけめんはチャーシューメンは、こってりにしよう。',
    requestFromUser: '中太麺が食べたい。つけめんが冷やし中華と細麺が塩のチャーシューの野菜だった。ニンニクに焼豚も好きだ。焼豚は焼豚は、タンメンマシマシで。担々麺がワンタンだった。チャーシューメンの中太麺マシマシで。',
    shortTermObjective: {
      term: { start: '1992-06-28T03:58:00+0900', end: '2018-06-29T02:54:16+0900' },
      text: '味噌マシマシで。ワンタンがラーメンに雲呑の醤油が替え玉にしよう。背脂と油そばの、海苔は、雲呑が味玉と、担々麺がチャーシューと冷やし中華にこってりも好きだ。味噌はワンタンマシマシで。'
    },
    staffId: 97,
    writtenOn: '1979-04-06'
  }
  const mocks: Partial<Plugins> = {
    $api,
    $confirm,
    $form,
    $snackbar
  }
  const propsData = {
    buttonText: '登録',
    errors: {},
    permission: Permission.createLtcsProjects,
    progress: false,
    user: createUserStub(),
    value: { ...form }
  }
  const userId = USER_ID_MIN
  const stub = createLtcsProjectStub()
  const ltcsProjectStore = createLtcsProjectStoreStub(createLtcsProjectResponseStub(stub.id))
  const menuResolverStore = createLtcsProjectServiceMenusResolverStoreStub({
    menus: createLtcsProjectServiceMenuStubs()
  })
  const userStore = createUserStoreStub(createUserResponseStub(userId))
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const dictionaryIndexResponse = createLtcsHomeVisitLongTermCareDictionaryIndexResponseStub(1)

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(ZLtcsProjectForm, {
      ...options,
      mocks,
      ...provides(
        [ltcsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [ltcsProjectStateKey, ltcsProjectStore.state],
        [ownExpenseProgramResolverStateKey, ownExpenseProgramResolverStore.state],
        [userStateKey, userStore.state]
      ),
      propsData
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  // テスト関数終了後に呼びだされることがあるため `beforeEach` ではなく `beforeAll` でモックする
  // そもそもテスト関数終了後に呼びだされないようにテストを改善する必要がある
  beforeAll(() => {
    jest.spyOn($api.ltcsHomeVisitLongTermCareDictionary, 'getIndex').mockResolvedValue(dictionaryIndexResponse)
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
    mocked($api.ltcsHomeVisitLongTermCareDictionary.getIndex).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  const initProgram: Partial<LtcsProjectProgram> = {
    programIndex: 1,
    category: 11,
    recurrence: 23,
    timeframe: 9,
    dayOfWeeks: [1],
    slot: { start: '21:03', end: '21:51' },
    headcount: 1,
    serviceCode: '8458797416',
    options: [100002, 300002, 301104],
    amounts: [{ category: 11, amount: 48 }],
    contents: [
      { menuId: 49, duration: 37, content: '', memo: 'もやしが好きだ。' }
    ]
  }

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values?: Partial<LtcsProjectsApi.Form>) {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent({
        stubs: {
          'v-card': true,
          'z-form-action-button': true
        }
      })
      observer = getValidationObserver(wrapper)
      jest.runOnlyPendingTimers()
    })

    afterAll(() => {
      unmountComponent()
    })

    afterEach(() => {
      observer.reset()
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

    it('should fail when problem is empty', async () => {
      await validate({
        problem: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-problem] .v-messages').text()).toBe('入力してください。')
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

    it('should fail when longTermObjective start is empty', async () => {
      await validate({
        longTermObjective: { term: { start: '' } }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-long-term-objective-term-start] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when longTermObjective end is empty', async () => {
      await validate({
        longTermObjective: { term: { end: '' } }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-long-term-objective-term-end] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when longTermObjective end is before longTermObjective start', async () => {
      await validate({
        longTermObjective: {
          term: {
            start: '1976-09-13',
            end: '1976-09-12'
          }
        }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-long-term-objective-term-start] .v-messages').text()).toBe('開始日より終了日の日付を後にしてください。')
      expect(wrapper.find('[data-long-term-objective-term-end] .v-messages').text()).toBe('開始日より終了日の日付を後にしてください。')
    })

    it('should fail when longTermObjective text is empty', async () => {
      await validate({
        longTermObjective: { text: '' }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-long-term-objective-text] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when shortTermObjective start is empty', async () => {
      await validate({
        shortTermObjective: { term: { start: '' } }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-short-term-objective-term-start] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when shortTermObjective end is empty', async () => {
      await validate({
        shortTermObjective: { term: { end: '' } }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-short-term-objective-term-end] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when shortTermObjective end is before shortTermObjective start', async () => {
      await validate({
        shortTermObjective: {
          term: {
            start: '1976-09-13',
            end: '1976-09-12'
          }
        }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-short-term-objective-term-start] .v-messages').text()).toBe('開始日より終了日の日付を後にしてください。')
      expect(wrapper.find('[data-short-term-objective-term-end] .v-messages').text()).toBe('開始日より終了日の日付を後にしてください。')
    })

    it('should fail when shortTermObjective text is empty', async () => {
      await validate({
        shortTermObjective: { text: '' }
      })

      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-short-term-objective-text] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when programs is empty', async () => {
      await validate({
        programs: []
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-programs] .v-alert__content').text()).toBe('週間サービス計画表を1つ以上追加してください。')
    })

    // TODO z-ltcs-project-weekly-services-edit-card.vue の詳細についてテストしているため、ここにあるべきではない
    describe('z-ltcs-project-weekly-services-edit-card', () => {
      it('should fail when programs category is empty', async () => {
        await validate({
          programs: [{
            ...initProgram,
            category: undefined
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-category] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail when programs recurrence is empty', async () => {
        await validate({
          programs: [{
            ...initProgram,
            recurrence: undefined
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-recurrence] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail when programs timeframe is empty', async () => {
        await validate({
          programs: [{
            ...initProgram,
            timeframe: undefined
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-timeframe] .v-messages').text()).toBe('入力してください。')
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

      it('should fail when programs amount is empty', async () => {
        await validate({
          programs: [{
            ...initProgram,
            amounts: [{ category: 11 }]
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-amount] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail when programs amount is 0', async () => {
        await validate({
          programs: [{
            ...initProgram,
            amounts: [{ category: 11, amount: 0 }]
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-amount] .v-messages').text())
          .toBe('1以上、1440以下の半角数字で入力してください。')
      })

      it('should fail when programs amount is 1441', async () => {
        await validate({
          programs: [{
            ...initProgram,
            amounts: [{ category: 11, amount: 1441 }]
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-amount] .v-messages').text())
          .toBe('1以上、1440以下の半角数字で入力してください。')
      })

      it('should fail when programs amount is mismatch amounts and slots time', async () => {
        const amount = 100
        const slotTimeDiff = 48
        await validate({
          programs: [{
            ...initProgram,
            amounts: [{ category: 11, amount }]
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-invalid-amount-times].v-messages').text())
          .toBe(`サービス時間の合計（${amount}分）と時間（${slotTimeDiff}分）を一致させてください。`)
      })

      it('should fail when programs headcount is empty', async () => {
        await validate({
          programs: [{
            ...initProgram,
            headcount: undefined
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-headcount] .v-messages').text()).toBe('入力してください。')
      })

      it('should fail when programs serviceCode is empty', async () => {
        await validate({
          programs: [{
            ...initProgram,
            serviceCode: undefined
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-service-code] .v-messages').text()).toBe('入力してください。')
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

      it('should fail when programs contents menuId is empty', async () => {
        await validate({
          programs: [{
            ...initProgram,
            contents: [
              { duration: 25, memo: '' },
              { menuId: 6, duration: 23, memo: '' }
            ]
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-program-contents-menu-id] .error--text')).toExist()
        expect(wrapper.find('[data-empty-service-content]').text())
          .toBe('サービス内容を入力してください。')
      })

      it('should fail when programs contents duration is lower amounts', async () => {
        const contents = [
          { menuId: 3, duration: 1, content: '', memo: '' },
          { menuId: 6, duration: 23, content: '', memo: '' }
        ]
        const sum = contents.reduce((sum, x) => sum + x.duration, 0)
        await validate({
          programs: [{
            ...initProgram,
            contents
          }]
        })
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-invalid-duration-times]').text())
          .toBe(`所要時間の合計（${sum}分）と時間（${initProgram.amounts![0].amount}分）を一致させてください。`)
      })
    })
  })
})
