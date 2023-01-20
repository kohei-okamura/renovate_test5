/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue, { ComponentOptions } from 'vue'
import {
  ltcsProjectServiceMenuResolverStateKey
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { ltcsProjectStateKey } from '~/composables/stores/use-ltcs-project-store'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsProjectNewPage from '~/pages/users/_id/ltcs-projects/new.vue'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'
import { SnackbarService } from '~/services/snackbar-service'
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

describe('pages/users/_id/ltcs-project/_projectId/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsHomeVisitLongTermCareDictionary', 'ltcsProjects', 'users')
  const $back = createMockedBack()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const userId = 1
  const form: LtcsProjectsApi.Form = {
    contractId: 3,
    officeId: 16,
    staffId: 70,
    writtenOn: '1972-07-22',
    effectivatedOn: '2014-10-20',
    problem: '雲呑はもやしマシマシで。替え玉が食べたい。焼きラーメンの、味噌も好きだ。塩は背脂はサンマーメンが好きだ。紅ショウガは醤油はワンタンも好きだ。魚介系にしよう。細麺が好きだ。つけめんマシマシで。',
    requestFromUser: '中太麺だった。もやしに中太麺が好きだ。太麺も好きだ。細麺は高菜がほうれん草がちぢれ麺だった。細麺とサンマーメンにもやしの野菜が好きだ。魚粉がつけめんマシマシで。あっさりが好きだ。',
    requestFromFamily: 'わかめの替え玉の細麺も好きだ。太麺は味玉とこってりが好きだ。こってりはニンニクが味噌の味玉がちぢれ麺のわかめとチャーシューメンと細麺が好きだ。ネギが好きだ。高菜に太麺も好きだ。',
    longTermObjective: {
      term: {
        start: '1981-09-07T00:51:44+0900',
        end: '1997-03-13T05:59:56+0900'
      },
      text: '野菜が好きだ。鶏白湯マシマシで。細麺がわかめがチャーシューが食べたい。もやしが好きだ。あっさりはメンマに、野菜が好きだ。味玉にしよう。紅ショウガと細麺の醤油も好きだ。ニンニクが食べたい。'
    },
    shortTermObjective: {
      term: {
        start: '2019-05-28T02:08:12+0900',
        end: '2023-02-26T05:57:31+0900'
      },
      text: '味玉にちぢれ麺だった。冷やし中華は雲呑が食べたい。背脂が高菜マシマシで。ワンタンの野菜だった。高菜が紅ショウガマシマシで。高菜にチャーシューはサンマーメンは野菜とタマネギマシマシで。'
    },
    programs: [
      {
        programIndex: 1,
        category: 13,
        recurrence: 13,
        timeframe: 1,
        dayOfWeeks: [3, 5],
        slot: { start: '20:55', end: '22:15' },
        amounts: [
          { category: 11, amount: 55 },
          { category: 12, amount: 25 }
        ],
        headcount: 1,
        serviceCode: '2358979791',
        options: [100002, 401101],
        contents: [
          { menuId: 57, duration: 60, content: '焼豚マシマシで。', memo: '海苔が食べたい。' },
          { menuId: 96, duration: 45, content: '', memo: '' }
        ]
      }
    ]
  }
  const mocks = {
    $api,
    $form,
    $router,
    $back,
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

  function mountComponent (options: ComponentOptions<Vue> = {}) {
    wrapper = mount(LtcsProjectNewPage, {
      ...options,
      ...provides(
        [ltcsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [ltcsProjectStateKey, ltcsProjectStore.state],
        [ownExpenseProgramResolverStateKey, ownExpenseProgramResolverStore.state],
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
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked($api.ltcsHomeVisitLongTermCareDictionary.getIndex).mockRestore()
    mocked(useStaffs).mockRestore()
    mocked(useOffices).mockRestore()
  })

  afterEach(() => {
    mocked($api.ltcsHomeVisitLongTermCareDictionary.getIndex).mockClear()
    mocked(useStaffs).mockClear()
    mocked(useOffices).mockClear()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn($api.ltcsProjects, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($api.ltcsProjects.create).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
    })

    afterEach(() => {
      mocked($api.ltcsProjects.create).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
    })

    it('should call $api.ltcsProjects.create when pass the validation', async () => {
      await wrapper.vm.$data.submit(form)

      expect($api.ltcsProjects.create).toHaveBeenCalledTimes(1)
      expect($api.ltcsProjects.create).toHaveBeenCalledWith({ form, userId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.$data.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('介護保険サービス計画情報を登録しました。')
    })

    it.each([
      ['problem', '解決すべき課題を入力してください。'],
      ['requestFromUser', 'ご本人の希望を入力してください。'],
      ['longTermObjectiveTermStart', '長期目標期間を入力してください。'],
      ['longTermObjectiveTermEnd', '長期目標期間を入力してください。'],
      ['shortTermObjectiveTermStart', '短期目標期間を入力してください。'],
      ['shortTermObjectiveTermEnd', '短期目標期間を入力してください。'],
      ['shortTermObjectiveText', '短期目標を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.ltcsProjects, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.$data.submit(form)
        await wrapper.vm.$nextTick()
        await jest.runAllTimers()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
