/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsProjectServiceMenuResolverStateKey } from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { dwsProjectStateKey, dwsProjectStoreKey } from '~/composables/stores/use-dws-project-store'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsProjectEditPage from '~/pages/users/_id/dws-projects/_projectId/edit.vue'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createDwsProjectResponseStub, DWS_PROJECT_ID_MIN } from '~~/stubs/create-dws-project-response-stub'
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
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('pages/users/_id/dws-project/_projectId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $back = createMockedBack()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const userId = 1
  const form: DwsProjectsApi.Form = {
    officeId: 1,
    staffId: 34,
    writtenOn: '2005-02-19',
    effectivatedOn: '1977-05-12',
    requestFromUser: 'サンマーメンと背脂と鶏白湯に中太麺にしよう。高菜だった。味玉のとんこつが焼豚の紅ショウガにワンタンマシマシで。タンメンがこってりがとんこつがちぢれ麺は野菜のワンタンは塩にしよう。',
    requestFromFamily: 'こってりに、塩の、わかめが好きだ。魚介系はわかめの焼きラーメンが冷やし中華の担々麺は焼きラーメンを注文する。野菜はわかめマシマシで。ほうれん草にとんこつだった。替え玉も好きだ。',
    objective: 'ワンタンにとんこつも好きだ。油そばの背脂だった。ちぢれ麺が細麺の、細麺の背脂も好きだ。替え玉が担々麺はネギの魚粉は、ラーメンに担々麺とタマネギと担々麺の鶏白湯のネギを注文する。',
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
          { menuId: 85, content: '', memo: '味玉マシマシで。' }
        ]
      }
    ]
  }
  const mocks = {
    $router,
    $back,
    $form,
    $snackbar
  }
  const dwsProjectStore = createDwsProjectStoreStub(createDwsProjectResponseStub())
  const menuResolverStore = createDwsProjectServiceMenuResolverStoreStub({
    menus: createDwsProjectServiceMenuStubs()
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const userResponse = createUserResponseStub(userId)
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(DwsProjectEditPage, {
      ...provides(
        [dwsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [dwsProjectStateKey, dwsProjectStore.state],
        [dwsProjectStoreKey, dwsProjectStore],
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
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    const stub = createDwsProjectStub(DWS_PROJECT_ID_MIN, createContractStub())

    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeEach(() => {
      jest.spyOn(dwsProjectStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(dwsProjectStore.update).mockReset()
    })

    it('should call dwsProjectStore.update when pass the validation', async () => {
      const expected = {
        form,
        id: stub.id,
        userId
      }

      await wrapper.vm.$data.submit(form)

      expect(dwsProjectStore.update).toHaveBeenCalledTimes(1)
      expect(dwsProjectStore.update).toHaveBeenCalledWith(expected)
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.$data.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('障害福祉サービス計画情報を編集しました。')
    })

    it('should be add program card when click add button', async () => {
      const beforeCardsLength = wrapper.findAll('[data-weekly-card]').length
      await wrapper.find('[data-add-program]').trigger('click')
      expect(wrapper.findAll('[data-weekly-card]').length).toBe(beforeCardsLength + 1)
    })

    it.each([
      ['requestFromUser', 'ご本人の希望を入力してください。'],
      ['requestFromFamily', 'ご家族の希望を入力してください。'],
      ['objective', '援助目標を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        mocked(dwsProjectStore.update)
          .mockReset()
          .mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
      }
    )
  })
})
