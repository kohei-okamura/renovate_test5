/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsContractNewPage from '~/pages/users/_id/ltcs-contracts/new.vue'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/ltcs-contracts/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsContracts', 'users')
  const $router = createMockedRouter()
  const $route = createMockedRoute({
    query: { segment: `${ServiceSegment.disabilitiesWelfare}` }
  })
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: LtcsContractsApi.CreateForm = {
    officeId: 3,
    note: 'だるまさんがころんだ'
  }
  const mocks = {
    $api,
    $form,
    $route,
    $router,
    $snackbar
  }
  const userId = 1

  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    const response = createUserResponseStub(userId)
    const userStore = createUserStoreStub(response)
    wrapper = mount(LtcsContractNewPage, {
      ...provides(
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
    mountComponent()
  })

  afterAll(() => {
    unmountComponent()
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
      jest.spyOn($api.ltcsContracts, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterAll(() => {
      mocked($snackbar.success).mockRestore()
      mocked($snackbar.error).mockRestore()
      mocked($api.ltcsContracts.create).mockRestore()
    })

    afterEach(() => {
      mocked($snackbar.success).mockClear()
      mocked($snackbar.error).mockClear()
      mocked($api.ltcsContracts.create).mockClear()
    })

    it('should call $api.ltcsContracts.create when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect($api.ltcsContracts.create).toHaveBeenCalledTimes(1)
      expect($api.ltcsContracts.create).toHaveBeenCalledWith({ form, userId })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('契約情報を登録しました。')
    })

    it.each([
      ['officeId', '事業所を入力してください。'],
      ['contractedOn', '重複する契約が既に登録されています。ご確認ください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.ltcsContracts, 'create').mockRejectedValueOnce(createAxiosError(HttpStatusCode.BadRequest, {
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
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
