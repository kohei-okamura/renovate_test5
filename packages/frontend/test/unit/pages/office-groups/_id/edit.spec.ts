/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import flushPromises from 'flush-promises'
import Vue from 'vue'
import { Route } from 'vue-router'
import { officeGroupsStoreKey } from '~/composables/stores/use-office-groups-store'
import { HttpStatusCode } from '~/models/http-status-code'
import { NuxtContext } from '~/models/nuxt'
import OfficeGroupsEditPage from '~/pages/office-groups/_id/edit.vue'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createOfficeGroupStub } from '~~/stubs/create-office-group-stub'
import { createOfficeGroupsStoreStub } from '~~/stubs/create-office-groups-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/office-groups/_id/edit.vue', () => {
  const { mount } = setupComponentTest()
  const officeGroup = createOfficeGroupStub()!
  const $api = createMockedApi('officeGroups')
  const $route = createMock<Route>({
    params: { id: `${officeGroup.id}` }
  })
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: OfficeGroupsApi.Form = {
    name: '関東ブロック',
    parentOfficeGroupId: 30,
    sortOrder: 1
  }
  const mocks = {
    $api,
    $form,
    $route,
    $router,
    $snackbar
  }
  const officeGroupsStore = createOfficeGroupsStoreStub()

  let wrapper: Wrapper<Vue & any>

  async function mountComponent () {
    wrapper = mount(OfficeGroupsEditPage, {
      ...provides([officeGroupsStoreKey, officeGroupsStore]),
      mocks
    })
    await flushPromises()
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    jest.spyOn($api.officeGroups, 'get').mockResolvedValue({ officeGroup })
  })

  afterAll(() => {
    mocked($api.officeGroups.get).mockReset()
  })

  beforeEach(() => {
    mocked($api.officeGroups.get).mockClear()
  })

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('setup', () => {
    it('should call $api.officeGroups.get', async () => {
      await mountComponent()
      expect($api.officeGroups.get).toHaveBeenCalledTimes(1)
      expect($api.officeGroups.get).toHaveBeenCalledWith({ id: officeGroup.id })
      unmountComponent()
    })
  })

  describe('validate', () => {
    beforeAll(async () => {
      await mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should return true when valid id given', () => {
      const params = { id: officeGroup.id + '' }
      const context = createMock<NuxtContext>({ params })

      const actual = wrapper.vm.$options.validate(context)

      expect(actual).toBeTrue()
    })

    it('should return false when non-numeric id given', () => {
      const params = { id: 'abc' }
      const context = createMock<NuxtContext>({ params })

      const actual = wrapper.vm.$options.validate(context)

      expect(actual).toBeFalse()
    })

    it('should return false when id not given', () => {
      const params = {}
      const context = createMock<NuxtContext>({ params })

      const actual = wrapper.vm.$options.validate(context)

      expect(actual).toBeFalse()
    })
  })

  describe('submit', () => {
    const id = officeGroup.id
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeAll(async () => {
      await mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    beforeEach(() => {
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn(officeGroupsStore, 'update').mockResolvedValue()
    })

    afterEach(() => {
      mocked(officeGroupsStore.update).mockReset()
      mocked($snackbar.success).mockReset()
      mocked($snackbar.error).mockReset()
    })

    it('should call officeGroupsStore.update', async () => {
      await wrapper.vm.submit(form)

      expect(officeGroupsStore.update).toHaveBeenCalledTimes(1)
      expect(officeGroupsStore.update).toHaveBeenCalledWith({ form, id })
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('事業所グループを編集しました。')
    })

    it.each([
      ['name', '事業所グループ名を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        mocked(officeGroupsStore.update).mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
