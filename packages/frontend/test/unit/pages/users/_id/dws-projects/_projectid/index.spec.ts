/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsProjectServiceMenuResolverStateKey } from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { dwsProjectStateKey } from '~/composables/stores/use-dws-project-store'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { Auth } from '~/models/auth'
import DwsProjectViewPage from '~/pages/users/_id/dws-projects/_projectId/index.vue'
import { createDwsProjectResponseStub } from '~~/stubs/create-dws-project-response-stub'
import {
  createDwsProjectServiceMenuResolverStoreStub
} from '~~/stubs/create-dws-project-service-menu-resolver-store-stub'
import { createDwsProjectServiceMenuStubs } from '~~/stubs/create-dws-project-service-menu-stub'
import { createDwsProjectStoreStub } from '~~/stubs/create-dws-project-store-stub'
import { createDwsProjectStub } from '~~/stubs/create-dws-project-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('pages/users/_id/dws-projects/_projectId/index.vue', () => {
  const { mount } = setupComponentTest()
  const stub = createDwsProjectStub()
  const dwsProjectResponse = createDwsProjectResponseStub(stub.id)
  const dwsProjectStore = createDwsProjectStoreStub(dwsProjectResponse)
  const menuResolverStore = createDwsProjectServiceMenuResolverStoreStub({
    menus: createDwsProjectServiceMenuStubs()
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const userResponse = createUserResponseStub()
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue> = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = mount(DwsProjectViewPage, () => ({
      ...options,
      ...provides(
        [dwsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [dwsProjectStateKey, dwsProjectStore.state],
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore],
        [sessionStoreKey, createAuthStub(auth)],
        [userStateKey, userStore.state]
      )
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
    const requiredPermissions: Permission[] = [
      Permission.updateDwsProjects
    ]

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
