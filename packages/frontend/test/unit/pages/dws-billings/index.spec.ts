/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsBillingsStoreKey } from '~/composables/stores/use-dws-billings-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import DwsBillingIndexPage from '~/pages/dws-billings/index.vue'
import { RouteQuery } from '~/support/router/types'
import { createDwsBillingStubs } from '~~/stubs/create-dws-billing-stub'
import { createDwsBillingsStoreStub } from '~~/stubs/create-dws-billings-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { createParamsToQuery } from '~~/test/helpers/create-params-to-query'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/dws-billings/index.vue', () => {
  const { mount } = setupComponentTest()
  const { objectContaining } = expect
  const $router = createMockedRouter()
  const dwsBillingsStore = createDwsBillingsStoreStub({
    dwsBillings: createDwsBillingStubs(20)
  })

  let wrapper: Wrapper<Vue>

  function mountComponent (query: RouteQuery = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    const $routes = createMockedRoutes({ query })
    wrapper = mount(DwsBillingIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [dwsBillingsStoreKey, dwsBillingsStore]
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
    mocked(dwsBillingsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call dwsBillingStore.getIndex', () => {
    mountComponent({ page: '1' })

    expect(dwsBillingsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(dwsBillingsStore.getIndex).toHaveBeenCalledWith(objectContaining({ page: 1 }))
    unmountComponent()
  })

  it.each([
    [{}, { officeId: '', statuses: [10, 20, 30], start: undefined, end: undefined }],
    [{
      officeId: 2,
      statuses: [10],
      start: TEST_NOW.toFormat('yyyy-MM'),
      end: TEST_NOW.plus({ months: 5 }).toFormat('yyyy-MM')
    }],
    [{
      officeId: 2,
      statuses: [10, 20],
      start: TEST_NOW.toFormat('yyyy-MM'),
      end: TEST_NOW.plus({ months: 5 }).toFormat('yyyy-MM')
    }],
    [{
      officeId: 2,
      statuses: [],
      start: TEST_NOW.toFormat('yyyy-MM'),
      end: TEST_NOW.plus({ months: 5 }).toFormat('yyyy-MM')
    }],
    [{
      officeId: 2,
      statuses: [],
      start: TEST_NOW.minus({ months: 5 }).toFormat('yyyy-MM'),
      end: TEST_NOW.toFormat('yyyy-MM')
    }]
  ])('should call dwsBillingsStore.getIndex correct query with %s', (params, expected: Record<string, unknown> = params) => {
    const query = createParamsToQuery(params)
    mountComponent(query)

    expect(dwsBillingsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(dwsBillingsStore.getIndex).toHaveBeenCalledWith(createFormData({ ...expected, desc: true }))

    unmountComponent()
  })
})
