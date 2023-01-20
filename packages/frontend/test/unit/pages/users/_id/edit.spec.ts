/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Sex } from '@zinger/enums/lib/sex'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { userStateKey, UserStore, userStoreKey } from '~/composables/stores/use-user-store'
import { HttpStatusCode } from '~/models/http-status-code'
import UsersEditPage from '~/pages/users/_id/edit.vue'
import { UsersApi } from '~/services/api/users-api'
import { $datetime } from '~/services/datetime-service'
import { SnackbarService } from '~/services/snackbar-service'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/users/_id/edit.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: UsersApi.Form = {
    familyName: '玉井',
    givenName: '詩織',
    phoneticFamilyName: 'タマイ',
    phoneticGivenName: 'シオリ',
    sex: Sex.female,
    birthday: $datetime.from(1995, 6, 4),
    postcode: '164-0001',
    prefecture: Prefecture.tokyo,
    city: '中野区',
    street: '中央1-35-6',
    apartment: 'レッチフィールド中野坂上6F',
    contacts: [{
      tel: '03-5937-6825', relationship: 20, name: '玉井 詩織'
    }],
    isEnabled: true,
    billingDestination: {
      destination: 1,
      paymentMethod: 1,
      contractNumber: '7599599805',
      corporationName: 'デイサービス土屋 中野中央',
      agentName: '新井 恵梨香',
      addr: {
        postcode: '545-0034',
        prefecture: 27,
        city: '大阪市阿倍野区',
        street: '阿倍野元町2-6-12',
        apartment: ''
      },
      tel: '0731-85-3606'
    }
  }
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const userStore = createUserStoreStub(createUserResponseStub())

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    store?: UserStore
  }

  function mountComponent (params: MountComponentParams = {}) {
    const store = params.store ?? userStore
    wrapper = mount(UsersEditPage, {
      ...provides(
        [userStateKey, store.state],
        [userStoreKey, store]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('form', () => {
    it('should have default value', () => {
      mountComponent()
      expect(wrapper.vm.value).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('submit', () => {
    const stub = createUserStub(1)
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
      jest.spyOn(userStore, 'update').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(userStore.update).mockReset()
    })

    it('should call userStore.update when pass the validation', async () => {
      await wrapper.vm.submit(form)

      expect(userStore.update).toHaveBeenCalledTimes(1)
      expect(userStore.update).toHaveBeenCalledWith({ form, id: stub.id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('利用者基本情報を編集しました。')
    })

    /**
     * @TODO v-forしている要素についてエラーが取得できないため修正する.
     */
    it.each([
      ['isEnabled', '契約中のため利用終了にすることができません。先に契約情報を編集してください。'],
      ['familyName', '姓を入力してください。'],
      ['givenName', '名を入力してください。'],
      ['phoneticFamilyName', 'フリガナ：姓を入力してください。'],
      ['phoneticGivenName', 'フリガナ：名を入力してください。'],
      ['sex', '性別を入力してください。'],
      ['birthday', '生年月日を入力してください。'],
      ['postcode', '郵便番号を入力してください。'],
      ['prefecture', '都道府県を入力してください。'],
      ['city', '市区町村を入力してください。'],
      ['street', '町名・番地を入力してください。'],
      ['apartment', '建物名などを入力してください。']
      // ['tel', '電話番号を入力してください。'],
      // ['relationship', '続柄・関係を入力してください。'],
      // ['name', '名前を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(userStore.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
