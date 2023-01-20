/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { DwsProvisionReportStatus } from '@zinger/enums/lib/dws-provision-report-status'
import { isEmpty } from '@zinger/helpers/index'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import {
  dwsProvisionReportsIndexStoreKey,
  DwsProvisionReportsStore
} from '~/composables/stores/use-dws-provision-reports-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import DwsProvisionReportsIndexPage from '~/pages/dws-provision-reports/index.vue'
import { RouteQuery } from '~/support/router/types'
import { mapValues } from '~/support/utils/map-values'
import { createDwsProvisionReportDigestStubs } from '~~/stubs/create-dws-provision-report-digest-stub'
import { createDwsProvisionReportsStoreStub } from '~~/stubs/create-dws-provision-reports-store-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/dws-provision-reports/index.vue', () => {
  const { mount } = setupComponentTest()
  const { objectContaining } = expect
  const $router = createMockedRouter()
  const dwsProvisionReports = createDwsProvisionReportDigestStubs(20)
  const dwsProvisionReportsStore = createDwsProvisionReportsStoreStub({ dwsProvisionReports })

  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    query?: RouteQuery
    auth?: Partial<Auth>
    store?: DwsProvisionReportsStore
  }

  function mountComponent ({ query, auth, store }: MountComponentParams = {}) {
    const $routes = createMockedRoutes({ query: query ?? {} })
    wrapper = mount(DwsProvisionReportsIndexPage, {
      ...provides(
        [dwsProvisionReportsIndexStoreKey, store ?? dwsProvisionReportsStore],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
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
    mocked(dwsProvisionReportsStore.getIndex).mockClear()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should call dwsProvisionReportsStore.getIndex', () => {
    mountComponent({ query: { page: '1' } })

    expect(dwsProvisionReportsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(dwsProvisionReportsStore.getIndex).toHaveBeenCalledWith(objectContaining({ page: 1 }))
    unmountComponent()
  })

  it.each([
    [{}, { officeId: '', providedIn: TEST_NOW.toFormat('yyyy-MM'), q: '', status: '' }],
    [{ officeId: 2, providedIn: TEST_NOW.plus({ months: 5 }).toFormat('yyyy-MM'), q: '', status: '' }],
    [{ officeId: 2, providedIn: TEST_NOW.minus({ years: 1 }).toFormat('yyyy-MM'), q: 'keyword', status: '' }],
    [{
      officeId: 2,
      providedIn: TEST_NOW.minus({ years: 1 }).toFormat('yyyy-MM'),
      q: 'keyword',
      status: DwsProvisionReportStatus.fixed
    }]
  ])('should call dwsProvisionReportsStore.getIndex correct query with %s', (params, expected: Record<string, unknown> = params) => {
    const query = mapValues(params, x => isEmpty(x) ? '' : String(x))
    mountComponent({ query })

    expect(dwsProvisionReportsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(dwsProvisionReportsStore.getIndex).toHaveBeenCalledWith(createFormData(expected))

    unmountComponent()
  })

  describe('display of no data', () => {
    const selector = '[data-table]'

    beforeAll(() => {
      const store = createDwsProvisionReportsStoreStub({ dwsProvisionReports: [] })
      mountComponent({ store })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should display a message ask to select an office when initial state', () => {
      const target = wrapper.find(selector)
      expect(target.text()).toContain('事業所を選択してください')
    })

    it('should display a message that there is no data when search result is empty', async () => {
      wrapper.vm.form.officeId = 1
      await wrapper.vm.submit()
      const target = wrapper.find(selector)
      expect(target.text()).toContain('障害福祉サービス予実')
    })

    it('should display a message ask to select an office when search without officeId', async () => {
      wrapper.vm.form.officeId = ''
      await wrapper.vm.submit()
      const target = wrapper.find(selector)
      expect(target.text()).toContain('事業所を選択してください')
    })
  })
})
