/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { ltcsBillingsStoreKey } from '~/composables/stores/use-ltcs-billings-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import LtcsBillingIndexPage from '~/pages/ltcs-billings/index.vue'
import { RouteQuery } from '~/support/router/types'
import { createLtcsBillingStubs } from '~~/stubs/create-ltcs-billing-stub'
import { createLtcsBillingsStoreStub } from '~~/stubs/create-ltcs-billings-store-stub'
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

describe('pages/ltcs-billings/index.vue', () => {
  const { mount } = setupComponentTest()
  const { objectContaining } = expect
  const $router = createMockedRouter()
  const ltcsBillings = createLtcsBillingStubs(20)
  const ltcsBillingsStore = createLtcsBillingsStoreStub({ ltcsBillings })

  let wrapper: Wrapper<Vue>

  function mountComponent (query: RouteQuery = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    const $routes = createMockedRoutes({ query })
    const mocks = {
      $router,
      $routes
    }
    wrapper = mount(LtcsBillingIndexPage, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [ltcsBillingsStoreKey, ltcsBillingsStore]
      ),
      mocks
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
    mocked(ltcsBillingsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call ltcsBillingStore.getIndex', () => {
    mountComponent({ page: '1' })

    expect(ltcsBillingsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(ltcsBillingsStore.getIndex).toHaveBeenCalledWith(objectContaining({ page: 1 }))
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
      statuses: [20],
      start: TEST_NOW.toFormat('yyyy-MM'),
      end: TEST_NOW.plus({ months: 5 }).toFormat('yyyy-MM')
    }],
    [{
      officeId: 2,
      statuses: [],
      start: TEST_NOW.minus({ months: 5 }).toFormat('yyyy-MM'),
      end: TEST_NOW.toFormat('yyyy-MM')
    }]
  ])('should call ltcsBillingsStore.getIndex correct query with %s', (params, expected: Record<string, unknown> = params) => {
    const query = createParamsToQuery(params)
    mountComponent(query)

    expect(ltcsBillingsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(ltcsBillingsStore.getIndex).toHaveBeenCalledWith(createFormData({ ...expected, desc: true }))

    unmountComponent()
  })
})
