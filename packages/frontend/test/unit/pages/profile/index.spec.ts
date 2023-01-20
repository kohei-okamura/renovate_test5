/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { StaffData, staffStateKey } from '~/composables/stores/use-staff-store'
import { Auth } from '~/models/auth'
import ProfileViewPage from '~/pages/profile/index.vue'
import { createEmptyBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createStaffResponseStub } from '~~/stubs/create-staff-response-stub'
import { createStaffStoreStub } from '~~/stubs/create-staff-store-stub'
import { STAFF_ID_MIN } from '~~/stubs/create-staff-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/profile/index.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue & any>

  type MountComponentOptions = {
    auth?: Partial<Auth>
    data?: Partial<StaffData>
  }

  function mountComponent ({ data = {}, auth = { isSystemAdmin: true } }: MountComponentOptions = {}) {
    const store = createStaffStoreStub({
      ...createStaffResponseStub(STAFF_ID_MIN),
      ...data
    })
    wrapper = mount(ProfileViewPage, {
      ...provides([staffStateKey, store.state], [sessionStoreKey, createAuthStub(auth)])
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    unmountComponent()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
  })

  describe('alert', () => {
    it('should not be rendered when the bank account has been filled', () => {
      mountComponent()
      expect(wrapper).not.toContainElement('[data-no-bank-account-alert]')
    })

    it('should be rendered when the bank account does not filled', () => {
      const data = {
        bankAccount: createEmptyBankAccountStub()
      }
      mountComponent({ data })
      expect(wrapper).toContainElement('[data-no-bank-account-alert]')
      expect(wrapper.find('[data-no-bank-account-alert]')).toMatchSnapshot()
    })
  })

  describe('FAB', () => {
    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
    })

    it(`should be rendered when the staff has permission: ${Permission.updateUsers}`, () => {
      const permissions = [Permission.updateUsers]
      mountComponent({ auth: { permissions } })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
    })

    it(`should not be rendered when the staff does not have permission: ${Permission.updateUsers}`, () => {
      const permissions = Permission.values.filter(x => x !== Permission.updateUsers)
      mountComponent({ auth: { permissions } })
      expect(wrapper).not.toContainElement('[data-fab]')
    })

    it('should not be rendered when the bank account has not been filled', () => {
      const data = {
        bankAccount: createEmptyBankAccountStub()
      }
      mountComponent({ data })
      expect(wrapper).not.toContainElement('[data-fab]')
    })
  })

  describe('FAB (speed dial)', () => {
    const data = {
      bankAccount: createEmptyBankAccountStub()
    }

    it('should be rendered when session auth is system admin', () => {
      mountComponent({ data })
      expect(wrapper).toContainElement('[data-fab-speed-dial]')
      expect(wrapper.find('[data-fab-speed-dial]')).toMatchSnapshot()
    })

    it(`should be rendered when the staff has permission: ${Permission.updateUsers}`, () => {
      const permissions = [Permission.updateUsers]
      mountComponent({ data, auth: { permissions } })
      expect(wrapper).toContainElement('[data-fab-speed-dial]')
      expect(wrapper.find('[data-fab-speed-dial]')).toMatchSnapshot()
    })

    it(`should not be rendered when the staff does not have permission: ${Permission.updateUsers}`, () => {
      const permissions = Permission.values.filter(x => x !== Permission.updateUsers)
      mountComponent({ data, auth: { permissions } })
      expect(wrapper).not.toContainElement('[data-fab-speed-dial]')
    })

    it('should not be rendered when the bank account has been filled', () => {
      mountComponent()
      expect(wrapper).not.toContainElement('[data-fab-speed-dial]')
    })

    describe('FAB: 銀行振込口座を登録', () => {
      const requiredPermissions: Permission[] = [
        Permission.updateUsers,
        Permission.updateUsersBankAccount
      ]

      it(`should be rendered when the staff has permissions: ${requiredPermissions.join(', ')}`, () => {
        const permissions = requiredPermissions
        mountComponent({ data, auth: { permissions } })
        expect(wrapper).toContainElement('[data-fab-speed-dial]')
        expect(wrapper.find('[data-fab-speed-dial]')).toMatchSnapshot()
      })

      it(`should not be rendered when the staff does not have permissions: ${requiredPermissions.join(', ')}`, () => {
        const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
        mountComponent({ data, auth: { permissions } })
        expect(wrapper).not.toContainElement('[data-fab-speed-dial]')
      })
    })
  })
})
