/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.\
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { settingStateKey, settingStoreKey } from '~/composables/stores/use-setting-store'
import { HttpStatusCode } from '~/models/http-status-code'
import SettingsNewPage from '~/pages/settings/new.vue'
import { SettingApi } from '~/services/api/setting-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createSettingStoreStub } from '~~/stubs/create-setting-store-stub'
import { createSettingResponseStub } from '~~/stubs/create-setting-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/settings/new.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const mocks = {
    $form,
    $router,
    $snackbar
  }
  const settingStore = createSettingStoreStub(createSettingResponseStub())

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(SettingsNewPage, {
      ...provides(
        [settingStateKey, settingStore.state],
        [settingStoreKey, settingStore]
      ),
      mocks
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    mountComponent()
  })

  afterEach(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('submit', () => {
    const form: SettingApi.Form = {
      bankingClientCode: '1234567890'
    }

    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(() => {
      jest.spyOn(settingStore, 'create').mockResolvedValue()
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
    })

    afterEach(() => {
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
      mocked(settingStore.create).mockReset()
    })

    it('should call settingStore.create when pass the validation', async () => {
      await wrapper.vm.$data.submit(form)

      expect(settingStore.create).toHaveBeenCalledTimes(1)
      expect(settingStore.create).toHaveBeenCalledWith({ form })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.$data.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('事業者別設定を登録しました。')
    })

    it.each([
      ['bankingClientCode', '委託者番号を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(settingStore.create)
          .mockReset()
          .mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [key]: [message]
            }
          }))

        await wrapper.vm.$data.submit(form)
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
