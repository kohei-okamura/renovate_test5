/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsContractStateKey, DwsContractStore, dwsContractStoreKey } from '~/composables/stores/use-dws-contract-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import DwsContractEditPage from '~/pages/users/_id/dws-contracts/_contractId/edit.vue'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createContractResponseStub } from '~~/stubs/create-contract-response-stub'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createDwsContractStoreStub } from '~~/stubs/create-dws-contract-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedBack } from '~~/test/helpers/create-mocked-back'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/dws-contracts/_contractId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $back = createMockedBack()
  const $route = createMockedRoute({
    query: { status: `${ContractStatus.terminated}` }
  })
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const userId = 1
  const form: DwsContractsApi.UpdateForm = {
    officeId: 3,
    status: ContractStatus.terminated,
    contractedOn: '2008-05-17',
    terminatedOn: '2021-04-30',
    dwsPeriods: {
      [DwsServiceDivisionCode.homeHelpService]: Object.freeze({
        start: '2008-06-01',
        end: '2012-09-30'
      }),
      [DwsServiceDivisionCode.visitingCareForPwsd]: Object.freeze({
        start: '2012-10-01',
        end: '2021-04-15'
      })
    },
    note: 'だるまさんがころんだ'
  }
  const mocks = {
    $back,
    $form,
    $route,
    $router,
    $snackbar
  }
  const stub = createContractStub()
  const contractStore = createDwsContractStoreStub(createContractResponseStub(stub.id))
  const userStore = createUserStoreStub(createUserResponseStub(stub.userId))

  let wrapper: Wrapper<Vue & any>

  function mountComponent (store: DwsContractStore = contractStore) {
    wrapper = mount(DwsContractEditPage, {
      ...provides(
        [dwsContractStateKey, store.state],
        [dwsContractStoreKey, store],
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
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('submit', () => {
    const stub = createContractStub(1)
    const store = createDwsContractStoreStub(createContractResponseStub(stub.id))

    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      mountComponent(store)
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn(store, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(store.update).mockReset()
    })

    it('should call dwsContractStore.update when pass the validation', async () => {
      const expected = {
        form,
        id: stub.id,
        userId
      }

      await wrapper.vm.submit(form)

      expect(store.update).toHaveBeenCalledTimes(1)
      expect(store.update).toHaveBeenCalledWith(expected)
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('契約情報を編集しました。')
    })

    it.each([
      // @TODO 事業所は契約状態が「仮契約」の時しか入力できないため一旦テスト対象から除外する
      // ['officeId', '事業所を入力してください。'],
      // @TODO 契約日は契約状態が「本契約」の時しか入力できないため一旦テスト対象から除外する
      // ['contractedOn', '契約日を入力してください。'],
      ['terminatedOn', '解約日を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(store.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
          errors: {
            [key]: [message]
          }
        }))

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()
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
