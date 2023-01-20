/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { TaxType } from '@zinger/enums/lib/tax-type'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { ownExpenseProgramStateKey } from '~/composables/stores/use-own-expense-program-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import OwnExpenseProgramsViewPage from '~/pages/own-expense-programs/_id/index.vue'
import { createOwnExpenseProgramResponseStub } from '~~/stubs/create-own-expense-program-response-stub'
import { createOwnExpenseProgramStoreStub } from '~~/stubs/create-own-expense-program-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/own-expense-programs/_id/index.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    options?: MountOptions<Vue>
    auth?: Partial<Auth>
    taxType?: TaxType
  }

  async function mountComponent (params: MountComponentParams = {}) {
    const options = params.options ?? {}
    const auth = params.auth ?? { isSystemAdmin: true }
    const taxType = params.taxType ?? TaxType.taxExcluded
    const response = createOwnExpenseProgramResponseStub(1, taxType)
    const store = createOwnExpenseProgramStoreStub(response)
    wrapper = mount(OwnExpenseProgramsViewPage, {
      ...options,
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [ownExpenseProgramStateKey, store.state])
    })
    await wrapper.vm.$nextTick()
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

  it('should be rendered correctly', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('z-data-card（基本情報）', () => {
    it('data-card-item（費用（税抜）and 費用（税込）and 税率区分）is not rendered when taxType value is TaxType.taxExempted', async () => {
      const taxType = TaxType.taxExempted

      await mountComponent({ taxType })

      expect(wrapper.find('[data-data-card]')).toMatchSnapshot()
      unmountComponent()
    })

    it('data-card-item（費用）is not rendered when taxType value is not TaxType.taxExempted', async () => {
      const taxType = TaxType.taxExcluded

      await mountComponent({ taxType })

      expect(wrapper.find('[data-data-card]')).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [Permission.updateOwnExpensePrograms]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should be rendered when the staff has permission: ${requiredPermissions}`, () => {
      const auth = {
        permissions: requiredPermissions
      }

      mountComponent({ auth })

      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should not be rendered when the staff does not have permission: ${requiredPermissions}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }

      mountComponent({ auth })

      expect(wrapper).not.toContainElement('[data-fab]')
    })
  })
})
