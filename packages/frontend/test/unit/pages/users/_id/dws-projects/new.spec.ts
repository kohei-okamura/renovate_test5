/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue, { ComponentOptions } from 'vue'
import { dwsProjectServiceMenuResolverStateKey } from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { dwsProjectStateKey } from '~/composables/stores/use-dws-project-store'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsProjectNewPage from '~/pages/users/_id/dws-projects/new.vue'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'
import { SnackbarService } from '~/services/snackbar-service'
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
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('pages/users/_id/dws-project/_projectId/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('dwsProjects', 'users')
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
        dayOfWeeks: [1, 2, 6, 7],
        category: 11,
        recurrence: 24,
        slot: { start: '04:30', end: '12:00' },
        options: [300003, 301102, 301104, 301105, 301106],
        contents: [
          { menuId: 27, content: '海苔も好きだ。', duration: 97, memo: '海苔も好きだ。' },
          { menuId: 89, content: 'サンマーメンが好きだ。', duration: 51, memo: 'サンマーメンが好きだ。' }
        ]
      },
      {
        summaryIndex: 2,
        dayOfWeeks: [1, 2, 7],
        category: 11,
        recurrence: 24,
        slot: { start: '08:30', end: '16:30' },
        options: [300003, 301102, 301104, 301105, 301106],
        contents: [
          { menuId: 96, content: '海苔も好きだ。', duration: 43, memo: '冷やし中華を注文する。' },
          { menuId: 46, content: 'サンマーメンが好きだ。', duration: 92, memo: 'チャーシューを注文する。' }
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
  const stub = createDwsProjectStub()
  const dwsProjectStore = createDwsProjectStoreStub(createDwsProjectResponseStub(stub.id))
  const menuResolverStore = createDwsProjectServiceMenuResolverStoreStub({
    menus: createDwsProjectServiceMenuStubs()
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const userStore = createUserStoreStub(createUserResponseStub(userId))

  let wrapper: Wrapper<Vue & any>

  function mountComponent (options: ComponentOptions<Vue> = {}) {
    wrapper = mount(DwsProjectNewPage, {
      ...options,
      ...provides(
        [dwsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [dwsProjectStateKey, dwsProjectStore.state],
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
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn($api.dwsProjects, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($api.dwsProjects.create).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($api.dwsProjects.create).mockClear()
    })

    it('should call $api.dwsProjects.create when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.dwsProjects.create).toHaveBeenCalledTimes(1)
      expect($api.dwsProjects.create).toHaveBeenCalledWith({ form, userId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('障害福祉サービス計画情報を登録しました。')
    })

    it.each([
      ['writtenOn', '作成日を入力してください。'],
      ['effectivatedOn', '適用日を入力してください。'],
      ['requestFromUser', 'ご本人の希望を入力してください。'],
      ['requestFromFamily', 'ご家族の希望を入力してください。'],
      ['objective', '援助目標を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message, testId = undefined) => {
        jest.spyOn($api.dwsProjects, 'create').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
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
  })
})
