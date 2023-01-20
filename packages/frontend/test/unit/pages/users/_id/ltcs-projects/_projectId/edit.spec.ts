/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  ltcsProjectServiceMenuResolverStateKey
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { ltcsProjectStateKey, ltcsProjectStoreKey } from '~/composables/stores/use-ltcs-project-store'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsProjectEditPage from '~/pages/users/_id/ltcs-projects/_projectId/edit.vue'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
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
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('pages/users/_id/ltcs-project/_projectId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsHomeVisitLongTermCareDictionary')
  const $back = createMockedBack()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const userId = 1
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
        serviceCode: '8458797416',
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
  const mocks = {
    $api,
    $router,
    $back,
    $form,
    $snackbar
  }
  const stub = createLtcsProjectStub()
  const ltcsProjectStore = createLtcsProjectStoreStub(createLtcsProjectResponseStub(stub.id))
  const menuResolverStore = createLtcsProjectServiceMenusResolverStoreStub({
    menus: createLtcsProjectServiceMenuStubs()
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const userStore = createUserStoreStub(createUserResponseStub(userId))
  const dictionaryIndexResponse = createLtcsHomeVisitLongTermCareDictionaryIndexResponseStub()

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(LtcsProjectEditPage, {
      ...provides(
        [ltcsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [ltcsProjectStateKey, ltcsProjectStore.state],
        [ltcsProjectStoreKey, ltcsProjectStore],
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore],
        [userStateKey, userStore.state],
        [userStoreKey, userStore]
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
    jest.spyOn($api.ltcsHomeVisitLongTermCareDictionary, 'getIndex').mockResolvedValue(dictionaryIndexResponse)
  })

  afterAll(() => {
    mocked($api.ltcsHomeVisitLongTermCareDictionary.getIndex).mockReset()
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('submit', () => {
    const stub = createLtcsProjectStub(1, createContractStub())

    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeAll(() => {
      jest.spyOn(ltcsProjectStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked(ltcsProjectStore.update).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked(ltcsProjectStore.update).mockClear()
    })

    it('should call ltcsProjectStore.update when pass the validation', async () => {
      const expected = {
        form,
        id: stub.id,
        userId
      }
      await wrapper.vm.$data.submit(form)

      expect(ltcsProjectStore.update).toHaveBeenCalledTimes(1)
      expect(ltcsProjectStore.update).toHaveBeenCalledWith(expected)
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.$data.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('介護保険サービス計画情報を編集しました。')
    })

    it('should be add program card when click add button', async () => {
      const beforeCardsLength = wrapper.findAll('[data-weekly-card]').length
      await wrapper.find('[data-add-program]').trigger('click')
      expect(wrapper.findAll('[data-weekly-card]').length).toBe(beforeCardsLength + 1)
    })

    it.each([
      ['officeId', '事業所を入力してください。'],
      ['writtenOn', '作成日を入力してください。'],
      ['effectivatedOn', '適用日を入力してください。'],
      ['problem', '解決すべき課題を入力してください。'],
      ['requestFromUser', 'ご本人の希望を入力してください。'],
      ['requestFromFamily', 'ご家族の希望を入力してください。'],
      ['longTermObjectiveTermStart', '長期目標期間を入力してください。'],
      ['longTermObjectiveTermEnd', '長期目標期間を入力してください。'],
      ['shortTermObjectiveTermStart', '短期目標期間を入力してください。'],
      ['shortTermObjectiveTermEnd', '短期目標期間を入力してください。'],
      ['shortTermObjectiveText', '短期目標を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        mocked(ltcsProjectStore.update)
          .mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [key]: [message]
            }
          }))

        await wrapper.vm.$data.submit(form)
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${testId ?? camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect($snackbar.error).toHaveBeenCalledTimes(1)
        expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
