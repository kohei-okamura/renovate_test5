/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import Vue from 'vue'
import { officesStoreKey } from '~/composables/stores/use-offices-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import OfficesIndexPage from '~/pages/offices/index.vue'
import { RouteQuery } from '~/support/router/types'
import { createOfficeStubs } from '~~/stubs/create-office-stub'
import { createOfficesStoreStub } from '~~/stubs/create-offices-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { createParamsToQuery } from '~~/test/helpers/create-params-to-query'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/offices/index.vue', () => {
  const { mount } = setupComponentTest()
  const { objectContaining } = expect
  const $router = createMockedRouter()
  const offices = createOfficeStubs(20)
  const officesStore = createOfficesStoreStub({ offices })

  let wrapper: Wrapper<Vue>

  function mountComponent (query: RouteQuery = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    const $routes = createMockedRoutes({ query })
    wrapper = mount(OfficesIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [officesStoreKey, officesStore]
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

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should dispatch offices/getIndex', () => {
    mountComponent({ page: '1' })

    expect(officesStore.getIndex).toHaveBeenCalledTimes(1)
    expect(officesStore.getIndex).toHaveBeenCalledWith(objectContaining({ page: 1 }))

    unmountComponent()
  })

  it('should  be rendered purpose when the staff', () => {
    const permissions = Permission.values
    mountComponent({}, { permissions })
    expect(wrapper).toContainElement('[data-purpose]')
  })

  it.skip.each<string, Permission[]>([
    ['listInternalOffices', Permission.values.filter(x => !Permission.listInternalOffices.includes(x))],
    ['listExternalOffices', Permission.values.filter(x => !Permission.listExternalOffices.includes(x))]
  ])('should not be rendered purpose when the staff does not have permission: %s', (_, permissions) => {
    mountComponent({}, { permissions })
    expect(wrapper).not.toContainElement('[data-purpose]')
  })

  it.each([
    [{}, { prefecture: '', status: [1, 2], purpose: '', q: '' }],
    [{ prefecture: 2, status: [1], purpose: Purpose.external, q: '' }],
    [{ prefecture: 2, status: [2], purpose: Purpose.internal, q: '' }],
    [{ prefecture: 2, status: [1, 2, 9], purpose: Purpose.internal, q: 'keyword' }]
  ])('should call officesStore.getIndex correct query with %s', (params, expected: Record<string, unknown> = params) => {
    const query = createParamsToQuery(params)
    mountComponent(query)

    expect(officesStore.getIndex).toHaveBeenCalledTimes(1)
    expect(officesStore.getIndex).toHaveBeenCalledWith(createFormData(expected))

    unmountComponent()
  })

  describe('FAB', () => {
    const requiredPermissions: Permission[] = [Permission.createInternalOffices, Permission.createExternalOffices]

    afterEach(() => {
      unmountComponent()
    })

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should be rendered when the staff has permissions: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-fab]')
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
    })
  })
})
