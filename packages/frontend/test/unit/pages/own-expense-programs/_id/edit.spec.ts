/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { noop } from '@zinger/helpers/index'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  ownExpenseProgramStateKey,
  OwnExpenseProgramStore,
  ownExpenseProgramStoreKey
} from '~/composables/stores/use-own-expense-program-store'
import { useOffices } from '~/composables/use-offices'
import { HttpStatusCode } from '~/models/http-status-code'
import { OwnExpenseProgram } from '~/models/own-expense-program'
import OwnExpenseProgramsEditPage from '~/pages/own-expense-programs/_id/edit.vue'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createOwnExpenseProgramResponseStub } from '~~/stubs/create-own-expense-program-response-stub'
import { createOwnExpenseProgramStoreStub } from '~~/stubs/create-own-expense-program-store-stub'
import { createOwnExpenseProgramStub } from '~~/stubs/create-own-expense-program-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/own-expense-programs/_id/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const createStubForm = (stub: OwnExpenseProgram): OwnExpenseProgramsApi.Form => ({
    officeId: stub.officeId,
    name: stub.name,
    durationMinutes: stub.durationMinutes,
    fee: {
      taxExcluded: stub.fee.taxExcluded,
      taxIncluded: stub.fee.taxIncluded,
      taxType: stub.fee.taxType,
      taxCategory: stub.fee.taxCategory
    },
    note: stub.note
  })
  const ownExpenseProgramStore = createOwnExpenseProgramStoreStub(createOwnExpenseProgramResponseStub())

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    store?: OwnExpenseProgramStore
  }

  function mountComponent (params: MountComponentParams = {}) {
    const store = params.store ?? ownExpenseProgramStore
    wrapper = mount(OwnExpenseProgramsEditPage, {
      ...provides(
        [ownExpenseProgramStoreKey, store],
        [ownExpenseProgramStateKey, store.state]
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
    const stub = createOwnExpenseProgramStub()
    const form = createStubForm(stub)
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
      jest.spyOn(ownExpenseProgramStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(ownExpenseProgramStore.update).mockReset()
    })

    it('should call ownExpenseProgramStore.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect(ownExpenseProgramStore.update).toHaveBeenCalledTimes(1)
      expect(ownExpenseProgramStore.update).toHaveBeenCalledWith({ form, id: stub.id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('自費サービスを編集しました。')
    })

    it('should display errors when server responses 400 Bad Request (error occurred in "%s")', async () => {
      mocked(ownExpenseProgramStore.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
        errors: {
          name: '自費サービス名を入力してください。'
        }
      }))

      await wrapper.vm.submit(form)
      await wrapper.vm.$nextTick()
      await jest.runAllTimers()

      const targetWrapper = wrapper.find('[data-name]')

      expect($snackbar.success).not.toHaveBeenCalled()
      expect($snackbar.error).toHaveBeenCalledTimes(1)
      expect($snackbar.error).toHaveBeenCalledWith('正しく入力されていない項目があります。入力内容をご確認ください。')
      expect(targetWrapper.text()).toContain('自費サービス名を入力してください。')
      expect(targetWrapper).toMatchSnapshot()
    })
  })
})
