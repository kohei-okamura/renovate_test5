/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsSubsidyStateKey } from '~/composables/stores/use-dws-subsidy-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import DwsSubsidyViewPage from '~/pages/users/_id/dws-subsidies/_subsidyId/index.vue'
import { createDwsSubsidyResponseStub } from '~~/stubs/create-dws-subsidy-response-stub'
import { createDwsSubsidyStoreStub } from '~~/stubs/create-dws-subsidy-store-stub'
import { DWS_SUBSIDY_ID_MIN } from '~~/stubs/create-dws-subsidy-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/users/_id/dws-subsidies/_subsidyId/index.vue', () => {
  const { mount } = setupComponentTest()
  const userResponse = createUserResponseStub()
  const userStore = createUserStoreStub(userResponse)

  let wrapper: Wrapper<Vue>

  type MountComponentParams = {
    auth?: Partial<Auth>
    options?: MountOptions<Vue>
  }

  function mountComponent (params: MountComponentParams = {}, subsidyType?: UserDwsSubsidyType) {
    const dwsSubsidyResponse = createDwsSubsidyResponseStub(DWS_SUBSIDY_ID_MIN, subsidyType)
    const dwsSubsidyStore = createDwsSubsidyStoreStub(dwsSubsidyResponse)
    const auth = params.auth ?? { isSystemAdmin: true }
    const options = params.options ?? {}
    wrapper = mount(DwsSubsidyViewPage, {
      ...options,
      ...provides(
        [dwsSubsidyStateKey, dwsSubsidyStore.state],
        [sessionStoreKey, createAuthStub(auth)],
        [userStateKey, userStore.state]
      )
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
  })

  afterAll(() => {
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe.each([
    ['給付率', UserDwsSubsidyType.benefitRate],
    ['定率負担', UserDwsSubsidyType.copayRate],
    ['給付額', UserDwsSubsidyType.benefitAmount],
    ['本人負担額', UserDwsSubsidyType.copayAmount]
  ])('z-data-card-item（自治体助成情報）', (label, subsidyType) => {
    const selector = '[data-data-card]'

    it(`should be rendered z-data-card-item（${label}） when userDwsSubsidyType is ${subsidyType}`, () => {
      mountComponent({}, subsidyType)
      expect(wrapper).toContainElement(selector)
      expect(wrapper.find(selector)).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [Permission.updateUserDwsSubsidies]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should be rendered when the staff has permission: ${requiredPermissions}`, () => {
      const auth = {
        permissions: requiredPermissions
      }

      mountComponent({ auth })

      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permission: ${requiredPermissions}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }

      mountComponent({ auth })

      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })
})
