/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  ltcsProjectServiceMenuResolverStateKey
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { ltcsProjectStateKey } from '~/composables/stores/use-ltcs-project-store'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { Auth } from '~/models/auth'
import LtcsProjectViewPage from '~/pages/users/_id/ltcs-projects/_projectId/index.vue'
import { createLtcsProjectResponseStub } from '~~/stubs/create-ltcs-project-response-stub'
import { createLtcsProjectServiceMenuStubs } from '~~/stubs/create-ltcs-project-service-menu-stub'
import {
  createLtcsProjectServiceMenusResolverStoreStub
} from '~~/stubs/create-ltcs-project-service-menus-resolver-store-stub'
import { createLtcsProjectStoreStub } from '~~/stubs/create-ltcs-project-store-stub'
import { createLtcsProjectStub } from '~~/stubs/create-ltcs-project-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('pages/users/_id/ltcs-projects/_projectId/index.vue', () => {
  const { mount } = setupComponentTest()
  const $form = createMockedFormService()
  const mocks = {
    $form
  }
  const stub = createLtcsProjectStub()
  const ltcsProjectResponse = createLtcsProjectResponseStub(stub.id)
  const ltcsProjectStore = createLtcsProjectStoreStub(ltcsProjectResponse)
  const userStore = createUserStoreStub(createUserResponseStub())
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const menuResolverStore = createLtcsProjectServiceMenusResolverStoreStub({
    menus: createLtcsProjectServiceMenuStubs()
  })

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = mount(LtcsProjectViewPage, () => ({
      ...options,
      ...provides(
        [ltcsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [ltcsProjectStateKey, ltcsProjectStore.state],
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore],
        [sessionStoreKey, createAuthStub(auth)],
        [userStateKey, userStore.state]
      ),
      mocks
    }))
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useStaffs).mockReturnValue(createUseStaffsStub())
  })

  afterAll(() => {
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [Permission.updateLtcsProjects]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it('should be rendered when the staff has permissions', () => {
      mountComponent({}, { permissions: requiredPermissions })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })
})
