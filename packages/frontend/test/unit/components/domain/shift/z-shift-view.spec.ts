/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZShiftView from '~/components/domain/shift/z-shift-view.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { Auth } from '~/models/auth'
import { createShiftResponseStub } from '~~/stubs/create-shift-response-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUseUsersStub } from '~~/stubs/create-use-users-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')
jest.mock('~/composables/use-users')

describe('z-shift-view.vue', () => {
  const { mount } = setupComponentTest()
  const propsData = createShiftResponseStub()

  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue>, auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = mount(ZShiftView, {
      ...options,
      ...provides([sessionStoreKey, createAuthStub(auth)])
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useStaffs).mockReturnValue(createUseStaffsStub())
    mocked(useUsers).mockReturnValue(createUseUsersStub())
  })

  afterAll(() => {
    mocked(useUsers).mockReset()
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe.each([
    ['user', '[data-users-card-item]', Permission.viewUsers],
    ['staff', '[data-staffs-card-item]', Permission.viewStaffs]
  ])('%s in z-data-card', (type, selector, requiredPermissions) => {
    it(`should be link to ${type} view page when session auth is system admin`, () => {
      mountComponent({ propsData })
      expect(wrapper.find(selector).html().includes('<a>')).toBeTrue()
      unmountComponent()
    })

    it(`should link to ${type} view page when the staff has permission: ${requiredPermissions}`, () => {
      const permissions = [requiredPermissions]
      mountComponent({ propsData }, { permissions })
      expect(wrapper.find(selector).html().includes('<a>')).toBeTrue()
      unmountComponent()
    })

    it(`should not link to ${type} view page when the staff does not have permission: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => x !== requiredPermissions)
      mountComponent({ propsData }, { permissions })
      expect(wrapper.find(selector).html().includes('<a>')).toBeFalse()
      unmountComponent()
    })
  })

  describe('display status', () => {
    const baseAttendance = propsData.shift

    it('should be displayed "キャンセル" if shift has been canceled', () => {
      const status = { isCanceled: true }
      const propsData = { shift: { ...baseAttendance, ...status } }
      mountComponent({ propsData })
      expect(wrapper.find('[data-status]').text()).toContain('キャンセル')
      unmountComponent()
    })

    it('should be displayed "確定" if shift has been confirmed and not canceled', () => {
      const status = { isCanceled: false, isConfirmed: true }
      const propsData = { shift: { ...baseAttendance, ...status } }
      mountComponent({ propsData })
      expect(wrapper.find('[data-status]').text()).toContain('確定')
      unmountComponent()
    })

    it('should be displayed "未確定" if shift has neither confirmed nor canceled', () => {
      const status = { isCanceled: false, isConfirmed: false }
      const propsData = { shift: { ...baseAttendance, ...status } }
      mountComponent({ propsData })
      expect(wrapper.find('[data-status]').text()).toContain('未確定')
      unmountComponent()
    })
  })

  describe('display cancel reason', () => {
    const baseAttendance = propsData.shift

    it('should not be displayed cancel reason if shift has not been canceled', () => {
      const status = { isCanceled: false }
      const propsData = { shift: { ...baseAttendance, ...status } }
      mountComponent({ propsData })
      expect(wrapper).not.toContainElement('[data-cancel-reason]')
      unmountComponent()
    })

    it('should be displayed cancel reason if shift has been canceled', () => {
      const status = { isCanceled: true }
      const propsData = { shift: { ...baseAttendance, ...status } }
      mountComponent({ propsData })
      expect(wrapper).toContainElement('[data-cancel-reason]')
      unmountComponent()
    })
  })
})
