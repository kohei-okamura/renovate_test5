/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, ref } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useOffices } from '~/composables/use-offices'
import { useUserBillingFileDownloader } from '~/composables/use-user-billing-file-downloader'
import { Auth } from '~/models/auth'
import { ISO_MONTH_FORMAT } from '~/models/date'
import UsersViewPage from '~/pages/users/_id/index.vue'
import { createTabService, TabService } from '~/services/tab-service'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'
import { createUserResponseStub } from '~~/stubs/create-user-response-stub'
import { createUserStoreStub } from '~~/stubs/create-user-store-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-user-billing-file-downloader')

type DownloaderFunctions = Omit<ReturnType<typeof useUserBillingFileDownloader>, 'errors' | 'progress'>
type DownloaderFunctionParameters = {
  [K in keyof DownloaderFunctions]: Parameters<DownloaderFunctions[K]>[0]
}[keyof DownloaderFunctions]

describe('pages/users/_id/index.vue', () => {
  type Component = Vue & {
    [key in keyof DownloaderFunctions]: (form: DownloaderFunctionParameters) => void
  }

  const { shallowMount } = setupComponentTest()
  const $tabs = createMock<TabService>({
    ...createTabService(),
    tab: ref('user')
  })
  const stub = createUserStub()
  const id = stub.id
  const response = createUserResponseStub(id)
  const userStore = createUserStoreStub(response)
  const downloader: ReturnType<typeof useUserBillingFileDownloader> = {
    downloadInvoices: jest.fn(),
    downloadNotices: jest.fn(),
    downloadReceipts: jest.fn(),
    downloadStatements: jest.fn(),
    errors: ref({}),
    progress: ref(false)
  }

  let wrapper: Wrapper<Component>

  function mountComponent (options: MountOptions<Component> = {}, auth: Partial<Auth> = { isSystemAdmin: true }) {
    wrapper = shallowMount(UsersViewPage, {
      ...options,
      ...provides(
        [sessionStoreKey, createAuthStub(auth)],
        [userStateKey, userStore.state]
      ),
      mocks: {
        $tabs,
        ...options?.mocks
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useUserBillingFileDownloader).mockReturnValue(downloader)
  })

  afterAll(() => {
    mocked(useUserBillingFileDownloader).mockRestore()
    mocked(useOffices).mockRestore()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('tabs', () => {
    const expectedTabItem = {
      user: reactive({ label: '利用者詳細', to: '#user' }),
      dws: reactive({
        label: '障害者総合支援',
        permissions: [
          Permission.listDwsContracts,
          Permission.listDwsCertifications,
          Permission.listUserDwsSubsidies,
          Permission.listUserDwsCalcSpecs,
          Permission.listDwsProjects
        ],
        to: '#dws'
      }),
      ltcs: reactive({
        label: '介護保険',
        permissions: [
          Permission.listLtcsContracts,
          Permission.listLtcsInsCards,
          Permission.listUserLtcsSubsidies,
          Permission.listUserLtcsCalcSpecs,
          Permission.listLtcsProjects
        ],
        to: '#ltcs'
      }),
      billings: reactive({
        label: '利用者請求',
        permissions: [
          Permission.listUserBillings
        ],
        to: '#billings'
      })
    }
    const requiredPermissions: Permission[] = [
      ...expectedTabItem.dws.permissions,
      ...expectedTabItem.ltcs.permissions,
      ...expectedTabItem.billings.permissions
    ]

    it('should have all tab items when session auth is system admin', async () => {
      await mountComponent()
      const tabs = wrapper.vm.$data.tabs
      expect(tabs).toStrictEqual(Object.values(expectedTabItem))
      unmountComponent()
    })

    it.each([
      [[Permission.listDwsContracts]],
      [[Permission.listDwsCertifications]],
      [[Permission.listUserDwsSubsidies]],
      [[Permission.listUserDwsCalcSpecs]],
      [[Permission.listDwsProjects]]
    ])('should have tab items(user and dws) when the staff have permissions: %s', async permissions => {
      await mountComponent({}, { permissions })
      const tabs = wrapper.vm.$data.tabs
      expect(tabs).toStrictEqual([expectedTabItem.user, expectedTabItem.dws])
      unmountComponent()
    })

    it.each([
      [[Permission.listLtcsContracts]],
      [[Permission.listLtcsInsCards]],
      [[Permission.listUserLtcsSubsidies]],
      [[Permission.listUserLtcsCalcSpecs]],
      [[Permission.listLtcsProjects]]
    ])('should have tab items(user and ltcs) when the staff have permissions: %s', async permissions => {
      await mountComponent({}, { permissions })
      const tabs = wrapper.vm.$data.tabs
      expect(tabs).toStrictEqual([expectedTabItem.user, expectedTabItem.ltcs])
      unmountComponent()
    })

    it.each([
      [[Permission.listUserBillings]]
    ])('should have tab items(user and billings) when the staff have permissions: %s', async permissions => {
      await mountComponent({}, { permissions })
      const tabs = wrapper.vm.$data.tabs
      expect(tabs).toStrictEqual([expectedTabItem.user, expectedTabItem.billings])
      unmountComponent()
    })

    it(`should have not tab items when the staff does not have permissions: ${requiredPermissions.join(', ')}`, async () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      await mountComponent({}, { permissions })
      const tabs = wrapper.vm.$data.tabs
      expect(tabs).toStrictEqual([expectedTabItem.user])
      unmountComponent()
    })

    it('should display "user" tab in initial state', () => {
      mountComponent()
      const tabItem = wrapper.find('[value="user"]')
      expect(tabItem.isVisible()).toBeTrue()
      unmountComponent()
    })
  })

  describe.each<[string, string, Permission[]]>([
    ['bank-account-card', '[data-bank-account-card]', [Permission.viewUsersBankAccount]],
    ['dws-contracts-card', '[data-dws-contracts-card]', [Permission.listDwsContracts]],
    ['dws-certifications-card', '[data-dws-certifications-card]', [Permission.listDwsCertifications]],
    ['dws-subsidies-card', '[data-dws-subsidies-card]', [Permission.listUserDwsSubsidies]],
    ['user-dws-calc-specs-card', '[data-user-dws-calc-specs-card]', [Permission.listUserDwsCalcSpecs]],
    ['dws-projects-card', '[data-dws-projects-card]', [Permission.listDwsProjects]],
    ['ltcs-contracts-card', '[data-ltcs-contracts-card]', [Permission.listLtcsContracts]],
    ['ltcs-ins-cards-card', '[data-ltcs-ins-cards-card]', [Permission.listLtcsInsCards]],
    ['ltcs-subsidies-card', '[data-ltcs-subsidies-card]', [Permission.listUserLtcsSubsidies]],
    ['user-ltcs-calc-specs-card', '[data-user-ltcs-calc-specs-card]', [Permission.listUserLtcsCalcSpecs]],
    ['ltcs-projects-card', '[data-ltcs-projects-card]', [Permission.listLtcsProjects]]
  ])('%s', (_, selector, requiredPermissions) => {
    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement(selector)
      unmountComponent()
    })

    it(`should be rendered when the staff has permissions: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement(selector)
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement(selector)
      unmountComponent()
    })
  })

  describe('FAB (speed dial)', () => {
    const requiredPermissions: Permission[] = [
      Permission.updateUsers,
      Permission.updateUsersBankAccount
    ]

    it('should be rendered when session auth is system admin', () => {
      mountComponent()
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it.each([
      [requiredPermissions],
      ...requiredPermissions.map(x => [[x]])
    ])('should be rendered when the staff has permissions: %s', permissions => {
      mountComponent({}, { permissions })
      expect(wrapper).toContainElement('[data-fab]')
      expect(wrapper.find('[data-fab]')).toMatchSnapshot()
      unmountComponent()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent({}, { permissions })
      expect(wrapper).not.toContainElement('[data-fab]')
      unmountComponent()
    })
  })

  describe('download', () => {
    const $form = createMockedFormService()
    const userBilling = createUserBillingStub()
    const form = { ids: [userBilling.id], issuedOn: TEST_NOW.toFormat(ISO_MONTH_FORMAT) }

    beforeAll(() => {
      mountComponent({
        mocks: {
          $form
        }
      })
    })

    afterAll(() => {
      unmountComponent()
    })

    describe.each<string, keyof DownloaderFunctions>([
      ['invoice', 'downloadInvoices'],
      ['receipt', 'downloadReceipts'],
      ['notice', 'downloadNotices'],
      ['statement', 'downloadStatements']
    ])('download %s', (_, fnName) => {
      afterEach(() => {
        mocked(useUserBillingFileDownloader).mockClear()
        mocked(downloader[fnName]).mockClear()
      })

      it(`should call useUserBillingFileDownloader.${fnName} when positive clicked`, async () => {
        await wrapper.vm[fnName](form)

        expect(downloader[fnName]).toHaveBeenCalledTimes(1)
        expect(downloader[fnName]).toHaveBeenCalledWith(form)
      })
    })
  })
})
