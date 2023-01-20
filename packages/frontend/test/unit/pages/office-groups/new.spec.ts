/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { Deferred } from 'ts-deferred'
import Vue from 'vue'
import { officeGroupsStoreKey } from '~/composables/stores/use-office-groups-store'
import { HttpStatusCode } from '~/models/http-status-code'
import OfficeGroupsNewPage from '~/pages/office-groups/new.vue'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createOfficeGroupsStoreStub } from '~~/stubs/create-office-groups-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/office-groups/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('officeGroups')
  const $route = createMockedRoute()
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: OfficeGroupsApi.Form = {
    name: '関東ブロック',
    parentOfficeGroupId: 30
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

  function mountComponent (options: MountOptions<Vue> = {}) {
    wrapper = mount(OfficeGroupsNewPage, {
      ...options,
      ...provides([officeGroupsStoreKey, officeGroupsStore]),
      mocks: {
        ...mocks,
        ...(options.mocks ?? {})
      }
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

  describe('setup', () => {
    it.each([
      [
        'should set form.parentOfficeGroupId when query given',
        { name: 'test', parentOfficeGroupId: '517' },
        { name: '', parentOfficeGroupId: 517 }
      ],
      [
        'should not set form.parentOfficeGroupId when no query given',
        {},
        { name: '', parentOfficeGroupId: undefined }
      ]
    ])('%s', (_, query, form) => {
      const $route = { query }
      const mocks = { $route }
      mountComponent({ mocks })
      expect(wrapper.vm.value).toStrictEqual(form)
      unmountComponent()
    })
  })

  describe('submit', () => {
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeEach(() => {
      mountComponent()
      jest.spyOn($api.officeGroups, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn(officeGroupsStore, 'getIndex').mockResolvedValue()
    })

    afterEach(() => {
      jest.clearAllMocks()
      unmountComponent()
    })

    it('should call $api.officeGroup.create', async () => {
      await wrapper.vm.submit(form)

      expect($api.officeGroups.create).toHaveBeenCalledTimes(1)
      expect($api.officeGroups.create).toHaveBeenCalledWith({ form })
    })

    it('should call officeGroupsStore.getIndex when succeeded to create', async () => {
      const deferred = new Deferred<void>()
      jest.spyOn($api.officeGroups, 'create').mockReturnValue(deferred.promise)

      const promise = wrapper.vm.submit(form)

      expect(officeGroupsStore.getIndex).not.toHaveBeenCalled()
      deferred.resolve()
      await wrapper.vm.$nextTick()
      await promise
      expect(officeGroupsStore.getIndex).toHaveBeenCalledTimes(1)
      expect(officeGroupsStore.getIndex).toHaveBeenCalledWith()
    })

    it('should not call officeGroupsStore.getIndex when failed to create', async () => {
      jest.spyOn($api.officeGroups, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))

      await wrapper.vm.submit(form)

      expect(officeGroupsStore.getIndex).not.toHaveBeenCalledWith()
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('事業所グループを登録しました。')
    })

    it.each([
      ['name', '事業所グループ名を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.officeGroups, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
