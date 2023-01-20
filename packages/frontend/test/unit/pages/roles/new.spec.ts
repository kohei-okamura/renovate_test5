/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { camelToKebab, noop } from '@zinger/helpers'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { Deferred } from 'ts-deferred'
import Vue from 'vue'
import { permissionsStoreKey } from '~/composables/stores/use-permissions-store'
import { rolesStoreKey } from '~/composables/stores/use-roles-store'
import { HttpStatusCode } from '~/models/http-status-code'
import RolesNewPage from '~/pages/roles/new.vue'
import { RolesApi } from '~/services/api/roles-api'
import { SnackbarService } from '~/services/snackbar-service'
import { createPermissionGroupStubs } from '~~/stubs/create-permission-group-stub'
import { createPermissionsStoreStub } from '~~/stubs/create-permissions-store-stub'
import { createRoleStubs } from '~~/stubs/create-role-stub'
import { createRolesStoreStub } from '~~/stubs/create-roles-store-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/roles/new.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('roles')
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const $form = createMockedFormService()
  const form: RolesApi.Form = {
    name: 'システム管理者',
    isSystemAdmin: true,
    permissions: {},
    scope: 1
  }
  const mocks = {
    $api,
    $form,
    $router,
    $snackbar
  }
  const permissionGroups = createPermissionGroupStubs()
  const permissionsStore = createPermissionsStoreStub({ permissionGroups })
  const roles = createRoleStubs()
  const rolesStore = createRolesStoreStub({ roles })
  let wrapper: Wrapper<Vue & any>

  function mountComponent () {
    wrapper = mount(RolesNewPage, {
      ...provides(
        [permissionsStoreKey, permissionsStore],
        [rolesStoreKey, rolesStore]
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
    // scrollIntoViewが定義されてないエラーを回避するために空の関数にする
    // 参考:https://github.com/jsdom/jsdom/issues/1695
    Element.prototype.scrollIntoView = noop

    beforeEach(() => {
      jest.spyOn($api.roles, 'create').mockResolvedValue(undefined)
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($snackbar, 'error').mockReturnValue()
      jest.spyOn(rolesStore, 'getIndex').mockResolvedValue()
    })

    afterEach(() => {
      jest.clearAllMocks()
    })

    it('should call $api.roles.create when pass the validation', async () => {
      await wrapper.vm.submit(form)
      expect($api.roles.create).toHaveBeenCalledTimes(1)
      expect($api.roles.create).toHaveBeenCalledWith({ form })
    })

    it('should call rolesStore.getIndex when created', async () => {
      const deferred = new Deferred<void>()
      jest.spyOn($api.roles, 'create').mockReturnValue(deferred.promise)

      const promise = wrapper.vm.submit(form)

      expect(rolesStore.getIndex).not.toHaveBeenCalled()
      deferred.resolve()
      await wrapper.vm.$nextTick()
      await promise
      expect(rolesStore.getIndex).toHaveBeenCalledTimes(1)
      expect(rolesStore.getIndex).toHaveBeenCalledWith()
    })

    it('should not call rolesStore.getIndex when fail to create', async () => {
      jest.spyOn($api.roles, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest))
      await wrapper.vm.submit(form)
      expect(rolesStore.getIndex).not.toHaveBeenCalled()
    })

    it('should display message when succeeded', async () => {
      await wrapper.vm.submit(form)
      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('ロールを登録しました。')
    })

    it.each([
      ['name', 'ロール名を入力してください。'],
      ['isSystemAdmin', 'システム管理権限を入力してください。'],
      ['scope', '権限範囲を入力してください。']
    ])(
      'should display errors when server responses 400 Bad Request (error occurred in "%s")',
      async (key, message) => {
        jest.spyOn($api.roles, 'create').mockRejectedValue(createAxiosError(HttpStatusCode.BadRequest, {
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
