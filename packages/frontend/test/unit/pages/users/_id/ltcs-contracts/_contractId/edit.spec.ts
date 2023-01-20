/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { assert, camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Range } from 'immutable'
import Vue from 'vue'
import { ltcsContractStoreKey } from '~/composables/stores/use-ltcs-contract-store'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import LtcsContractEditPage from '~/pages/users/_id/ltcs-contracts/_contractId/edit.vue'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createContractResponseStub } from '~~/stubs/create-contract-response-stub'
import { CONTRACT_ID_MAX, CONTRACT_ID_MIN, createContractStub } from '~~/stubs/create-contract-stub'
import { createLtcsContractStoreStub } from '~~/stubs/create-ltcs-contract-store-stub'
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

describe('pages/users/_id/ltcs-contracts/_contractId/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $back = createMockedBack()
  const $route = createMockedRoute({
    query: {
      status: `${ContractStatus.terminated}`
    }
  })
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: LtcsContractsApi.UpdateForm = {
    officeId: 3,
    status: ContractStatus.terminated,
    contractedOn: '2008-05-17',
    terminatedOn: '2021-04-30',
    ltcsPeriod: {
      start: '2012-10-01',
      end: '2021-04-15'
    },
    expiredReason: LtcsExpiredReason.hospitalized,
    note: 'だるまさんがころんだ'
  }
  const mocks = {
    $back,
    $form,
    $route,
    $router,
    $snackbar
  }
  const stub = Range(CONTRACT_ID_MIN, CONTRACT_ID_MAX)
    .map(createContractStub)
    .filter(x => x.serviceSegment === ServiceSegment.longTermCare)
    .find(x => x.status === ContractStatus.formal)
  assert(stub !== undefined, 'Failed to create contract stub')
  const { id, userId } = stub
  const ltcsContractStore = createLtcsContractStoreStub(createContractResponseStub(id))
  const userStore = createUserStoreStub(createUserResponseStub(userId))

  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    wrapper = mount(LtcsContractEditPage, {
      ...provides(
        [ltcsContractStoreKey, ltcsContractStore],
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
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn(ltcsContractStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(ltcsContractStore.update).mockReset()
    })

    it('should call ltcsContractStore.update when pass the validation', async () => {
      const expected = {
        form,
        id,
        userId
      }

      await wrapper.vm.submit(form)

      expect(ltcsContractStore.update).toHaveBeenCalledTimes(1)
      expect(ltcsContractStore.update).toHaveBeenCalledWith(expected)
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
        mocked(ltcsContractStore.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
