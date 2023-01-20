/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZRoleForm from '~/components/domain/role/z-role-form.vue'
import { permissionsStoreKey } from '~/composables/stores/use-permissions-store'
import { RolesApi } from '~/services/api/roles-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createPermissionGroupStubs } from '~~/stubs/create-permission-group-stub'
import { createPermissionsStoreStub } from '~~/stubs/create-permissions-store-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { submit } from '~~/test/helpers/trigger'

describe('z-role-form.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const form: RolesApi.Form = {
    name: 'システム管理者',
    isSystemAdmin: false,
    scope: 1,
    permissions: {}
  }
  const propsData: Record<string, any> & { value?: Partial<typeof form> } = {
    errors: {},
    buttonText: '登録',
    progress: false
  }
  const permissionGroups = createPermissionGroupStubs()
  const permissionsStore = createPermissionsStoreStub({ permissionGroups })
  let wrapper: Wrapper<Vue>

  type MountComponentArguments = Omit<MountOptions<Vue>, 'propsData'> & {
    isShallow?: true
    value?: Partial<typeof form>
  }

  function mountComponent ({ isShallow, value, ...options }: MountComponentArguments = {}) {
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(ZRoleForm, {
      ...options,
      ...provides([permissionsStoreKey, permissionsStore]),
      mocks: { ...mocks, ...options?.mocks },
      propsData: { ...propsData, value: value ?? form }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    jest.spyOn(permissionsStore, 'getIndex').mockResolvedValue()
  })

  afterAll(() => {
    mocked(permissionsStore.getIndex).mockRestore()
  })

  afterEach(() => {
    mocked(permissionsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should be toggle permission state when checkbox click', async () => {
    await mountComponent({ stubs: ['z-text-field', 'z-select'] })
    const form = (wrapper.vm as any).form
    const toBeNotAllowed = (element: HTMLElement) => {
      expect(element.getAttribute('aria-checked')).toBe('false')
      expect(form.permissions).toMatchObject({})
    }
    for (const group of permissionGroups) {
      const permissions = group.permissions
      const checkboxWrapper = wrapper.find<HTMLElement>(`[data-permission-group-checkbox="${group.id}"]`)
      const element = checkboxWrapper.element
      // initial (no check)
      toBeNotAllowed(element)
      // to enable (check)
      await checkboxWrapper.trigger('click')
      expect(element.getAttribute('aria-checked')).toBe('true')
      for (const permission of permissions) {
        expect(form.permissions[permission]).toBeTrue()
      }
      // to disable (no check)
      await checkboxWrapper.trigger('click')
      toBeNotAllowed(element)
    }
    unmountComponent()
  })

  describe('setup', () => {
    it('should call permissionsStore.getIndex()', () => {
      mountComponent({ isShallow: true })
      expect(permissionsStore.getIndex).toHaveBeenCalledTimes(1)
      expect(permissionsStore.getIndex).toHaveBeenCalledWith()
      unmountComponent()
    })
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: Partial<RolesApi.Form> = {}): Promise<void> {
      await setData(wrapper, {
        form: { ...form, ...values }
      })
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should pass when input correctly', async () => {
      await validate()
      expect(observer).toBePassed()
    })

    it('should fail when name is empty', async () => {
      await validate({
        name: ''
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-name] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when scope is empty', async () => {
      await validate({
        scope: undefined
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-scope] .v-messages').text()).toBe('入力してください。')
    })

    it('should fail when name is longer than 100 characters', async () => {
      await validate({
        name: 'x'.repeat(100)
      })
      expect(observer).toBePassed()
      await validate({
        name: 'x'.repeat(101)
      })
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-name] .v-messages').text()).toBe('100文字以内で入力してください。')
    })
  })

  describe('submit', () => {
    it('should be set false to "isSystemAdmin" at initialization', async () => {
      mountComponent({ value: { name: form.name, scope: form.scope } })
      const expected = { isSystemAdmin: false }
      await submit(() => wrapper.find('[data-form]'))
      expect(wrapper.emitted('submit')![0][0]).toMatchObject(expected)
      unmountComponent()
    })

    it('should emit empty "permissions" when submit if "isSystemAdmin" is true', async () => {
      const value = Object.assign({}, form, {
        isSystemAdmin: true,
        permissions: {
          [Permission.createOfficeGroups]: true,
          [Permission.deleteOfficeGroups]: true,
          [Permission.listOfficeGroups]: true,
          [Permission.updateOfficeGroups]: true,
          [Permission.viewOfficeGroups]: true
        }
      })
      mountComponent({ value })
      const expected = { isSystemAdmin: true, permissions: {} }
      await submit(() => wrapper.find('[data-form]'))
      expect(wrapper.emitted('submit')![0][0]).toMatchObject(expected)
      unmountComponent()
    })
  })
})
