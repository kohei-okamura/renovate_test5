/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { ownExpenseProgramsStoreKey } from '~/composables/stores/use-own-expense-programs-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import OwnExpenseProgramsIndexPage from '~/pages/own-expense-programs/index.vue'
import { RouteQuery } from '~/support/router/types'
import { mapValues } from '~/support/utils/map-values'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createOwnExpenseProgramsStoreStub } from '~~/stubs/create-own-expense-programs-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/own-expense-programs/index.vue', () => {
  const { mount } = setupComponentTest()
  const { objectContaining } = expect
  const $router = createMockedRouter()
  const ownExpensePrograms = createOwnExpenseProgramStubs(20)
  const ownExpenseProgramsStore = createOwnExpenseProgramsStoreStub({ ownExpensePrograms })

  let wrapper: Wrapper<Vue>

  type MountComponentParams = {
    auth?: Partial<Auth>
    query?: RouteQuery
  }

  function mountComponent (params: MountComponentParams = {}) {
    const auth = params.auth ?? { isSystemAdmin: true }
    const query = params.query ?? {}
    const $routes = createMockedRoutes({ query })
    wrapper = mount(OwnExpenseProgramsIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [ownExpenseProgramsStoreKey, ownExpenseProgramsStore]
      ),
      mocks: {
        $router,
        $routes
      }
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

  beforeEach(() => {
    mocked(ownExpenseProgramsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call ownExpenseProgramsStore.getIndex', () => {
    const query = { page: '1' }

    mountComponent({ query })

    expect(ownExpenseProgramsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(ownExpenseProgramsStore.getIndex).toHaveBeenCalledWith(objectContaining({ page: 1 }))
    unmountComponent()
  })

  it.each([
    [{}, { officeId: '', q: '' }],
    [{ officeId: '', q: 'keyword' }],
    [{ officeId: 2, q: 'keyword' }]
  ])('should call ownExpenseProgramsStore.getIndex correct query with %s', (params, expected: Record<string, unknown> = params) => {
    const query = mapValues(params, x => isEmpty(x) ? '' : String(x))
    mountComponent({ query })

    expect(ownExpenseProgramsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(ownExpenseProgramsStore.getIndex).toHaveBeenCalledWith(createFormData(expected))

    unmountComponent()
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [Permission.createOwnExpensePrograms]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should be rendered when the staff has permissions: ${requiredPermissions}`, () => {
      const auth = {
        permissions: requiredPermissions
      }

      mountComponent({ auth })

      expect(wrapper).toContainElement('[data-fab]')
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const auth = {
        permissions: Permission.values.filter(x => !requiredPermissions.includes(x))
      }

      mountComponent({ auth })

      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })
})
