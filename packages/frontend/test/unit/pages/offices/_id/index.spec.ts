/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import { dwsAreaGradesStateKey, dwsAreaGradesStoreKey } from '~/composables/stores/use-dws-area-grades-store'
import { ltcsAreaGradesStateKey, ltcsAreaGradesStoreKey } from '~/composables/stores/use-ltcs-area-grades-store'
import { officeStateKey } from '~/composables/stores/use-office-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import OfficesViewPage from '~/pages/offices/_id/index.vue'
import { createTabService, TabService } from '~/services/tab-service'
import { createDwsAreaGradeStubs } from '~~/stubs/create-dws-area-grade-stub'
import { createDwsAreaGradesStoreStub } from '~~/stubs/create-dws-area-grades-store-stub'
import { createLtcsAreaGradeStubs } from '~~/stubs/create-ltcs-area-grade-stub'
import { createLtcsAreaGradesStoreStub } from '~~/stubs/create-ltcs-area-grades-store-stub'
import { createOfficeResponseStub } from '~~/stubs/create-office-response-stub'
import { createOfficeStoreStub } from '~~/stubs/create-office-store-stub'
import { createOfficeStubs } from '~~/stubs/create-office-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/offices/_id/index.vue', () => {
  const { mount } = setupComponentTest()
  const stub = createOfficeStubs().find(x => {
    return x.purpose === Purpose.internal && x.qualifications.includes(OfficeQualification.dwsHomeHelpService)
  })!
  const id = stub.id
  const $tabs = createMock<TabService>({
    ...createTabService()
  })
  let wrapper: Wrapper<Vue & any>

  function mountComponent (
    options: MountOptions<Vue> = {},
    auth: Partial<Auth> = { isSystemAdmin: true }
  ) {
    const dwsAreaGradesStore = createDwsAreaGradesStoreStub({
      dwsAreaGrades: createDwsAreaGradeStubs()
    })
    const ltcsAreaGradesStore = createLtcsAreaGradesStoreStub({
      ltcsAreaGrades: createLtcsAreaGradeStubs()
    })
    const response = createOfficeResponseStub(id)
    const store = createOfficeStoreStub(response)
    wrapper = mount(OfficesViewPage, {
      ...options,
      ...provides(
        [dwsAreaGradesStoreKey, dwsAreaGradesStore],
        [dwsAreaGradesStateKey, dwsAreaGradesStore.state],
        [ltcsAreaGradesStoreKey, ltcsAreaGradesStore],
        [ltcsAreaGradesStateKey, ltcsAreaGradesStore.state],
        [officeStateKey, store.state],
        [sessionStoreKey, createAuthStub(auth)]
      )
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent({ mocks: { $tabs } })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('tabs', () => {
    beforeAll(() => {
      mountComponent({ mocks: { $tabs } })
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should have correct tab items', () => {
      const expected = ([
        reactive({ label: '基本情報', to: '#office' }),
        reactive({ label: '算定情報', to: '#calc-specs' })
      ])
      const tabs = wrapper.vm.$data.tabs

      expect(tabs).toStrictEqual(expected)
    })

    it('should not have calc-spec tab item if purpose is external', async () => {
      const expected = ([
        reactive({ label: '基本情報', to: '#office' })
      ])
      await setData(wrapper, { office: { purpose: Purpose.external } })
      const tabs = wrapper.vm.$data.tabs

      expect(tabs).toStrictEqual(expected)
    })

    it('should display "office" tab in initial state', () => {
      const tabItem = wrapper.find('[value="office"]')
      expect(tabItem.isVisible()).toBeTrue()
    })
  })

  describe.each([
    ['office'],
    ['calc-specs']
  ])('FAB (speed dial) in "%s" tab', name => {
    const requiredPermission: Permission[] = [Permission.updateInternalOffices, Permission.updateExternalOffices]

    it('should be rendered when session auth is system admin', () => {
      mountComponent({ mocks: { $tabs } })
      expect(wrapper).toContainElement(`[value="${name}"] [data-fab]`)
      expect(wrapper.find(`[value="${name}"] [data-fab]`)).toMatchSnapshot()
      unmountComponent()
    })

    it(`should be rendered when the staff has permissions: ${requiredPermission}`, () => {
      const permissions = requiredPermission
      mountComponent({ mocks: { $tabs } }, { permissions })
      expect(wrapper).toContainElement(`[value="${name}"] [data-fab]`)
      expect(wrapper.find(`[value="${name}"] [data-fab]`)).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermission}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermission.includes(x))
      mountComponent({ mocks: { $tabs } }, { permissions })
      expect(wrapper).not.toContainElement(`[value="${name}"] [data-fab]`)
      unmountComponent()
    })
  })
})
