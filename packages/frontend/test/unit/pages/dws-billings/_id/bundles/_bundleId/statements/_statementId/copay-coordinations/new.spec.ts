/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { camelToKebab } from '@zinger/helpers/index'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { dwsBillingStatementStateKey } from '~/composables/stores/use-dws-billing-statement-store'
import { dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import { NuxtContext } from '~/models/nuxt'
import DwsBillingCopayCoordinationsNewPage
  from '~/pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/new.vue'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import { SnackbarService } from '~/services/snackbar-service'
import {
  createDwsBillingCopayCoordinationResponseStub
} from '~~/stubs/create-dws-billing-copay-coordination-response-stub'
import { createDwsBillingResponseStub } from '~~/stubs/create-dws-billing-response-stub'
import { createDwsBillingStatementResponseStub } from '~~/stubs/create-dws-billing-statement-response-stub'
import { createDwsBillingStatementStoreStub } from '~~/stubs/create-dws-billing-statement-store-stub'
import { createDwsBillingStoreStub } from '~~/stubs/create-dws-billing-store-stub'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('pages/dws-billings/_id/bundles/_bundleId/statements/_statementId/copay-coordinations/new.vue', () => {
  type RouterArguments = {
    params: {
      id: string
      bundleId: string
    }
    query: {
      userId: string
    }
  }
  const { mount, shallowMount } = setupComponentTest()
  const dwsBillingStore = createDwsBillingStoreStub(createDwsBillingResponseStub(86, 1))
  const { billing, bundles } = dwsBillingStore.state
  const bundle = bundles.value![0]
  const dwsBillingStatementStore = createDwsBillingStatementStoreStub(createDwsBillingStatementResponseStub({
    billingId: billing.value?.id,
    bundleId: bundle.id,
    id: 24
  }))
  const createRouterArguments = ({ params, query }: DeepPartial<RouterArguments>): RouterArguments => {
    return {
      params: {
        ...{
          id: String(billing.value!.id),
          bundleId: String(bundle.id)
        },
        ...params
      },
      query: {
        ...{
          userId: '1'
        },
        ...query
      }
    }
  }
  const args = createRouterArguments({})
  const $api = createMockedApi('dwsBillingCopayCoordinations', 'dwsBillingStatements')
  const $form = createMockedFormService()
  const $route = createMockedRoute(args)
  const $router = createMockedRouter()
  const $snackbar = createMock<SnackbarService>()
  const mocks = {
    $api,
    $form,
    $route,
    $router,
    $snackbar
  }

  let wrapper: Wrapper<Vue & any>

  type MountArguments = {
    auth?: Partial<Auth>
    isShallow?: true
    options?: MountOptions<Vue>
  }

  async function mountComponent ({ auth, isShallow, options }: MountArguments = {}) {
    const fn = isShallow ? shallowMount : mount
    wrapper = await fn(DwsBillingCopayCoordinationsNewPage, {
      ...options,
      ...provides(
        [dwsBillingStoreKey, dwsBillingStore],
        [dwsBillingStatementStateKey, dwsBillingStatementStore.state],
        [sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]
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

  test('意図通りレンダリングされる', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validation', () => {
    beforeAll(() => {
      mountComponent({ isShallow: true })
    })

    afterAll(() => {
      unmountComponent()
    })

    test('問題がなければバリデーションを通過する', () => {
      const context = createMock<NuxtContext>(args)

      const result = wrapper.vm.$options.validate!(context)

      expect(result).toBeTrue()
    })

    test.each([
      ['id'],
      ['bundleId']
    ])('%s が数値でない場合はバリデーションを通過しない', key => {
      const args = createRouterArguments({ params: { [key]: 'abc' } })
      const context = createMock<NuxtContext>(args)

      const result = wrapper.vm.$options.validate!(context)

      expect(result).toBeFalse()
    })

    test.each([
      ['id'],
      ['bundleId']
    ])('%s が未設定である場合はバリデーションを通過しない', key => {
      const args = createRouterArguments({ params: { [key]: undefined } })
      const context = createMock<NuxtContext>(args)

      const result = wrapper.vm.$options.validate!(context)

      expect(result).toBeFalse()
    })

    test('userId が数値でない場合はバリデーションを通過しない', () => {
      const args = createRouterArguments({ query: { userId: 'abc' } })
      const context = createMock<NuxtContext>(args)

      const result = wrapper.vm.$options.validate!(context)

      expect(result).toBeFalse()
    })

    test('userId が未設定である場合はバリデーションを通過しない', () => {
      const args = createRouterArguments({ query: { userId: undefined } })
      const context = createMock<NuxtContext>(args)

      const result = wrapper.vm.$options.validate!(context)

      expect(result).toBeFalse()
    })
  })

  describe('submit', () => {
    const identifiers = {
      billingId: billing.value!.id,
      bundleId: bundle.id
    }
    const form: Partial<DwsBillingCopayCoordinationsApi.Form> = {
      userId: USER_ID_MIN,
      items: [
        { officeId: OFFICE_ID_MIN, subtotal: { fee: 3200, copay: 1200, coordinatedCopay: 480 } }
      ],
      result: CopayCoordinationResult.coordinated
    }

    beforeAll(() => {
      mountComponent()
      jest.spyOn(dwsBillingStore, 'get')
      jest.spyOn($api.dwsBillingCopayCoordinations, 'create').mockResolvedValue(
        createDwsBillingCopayCoordinationResponseStub({ ...identifiers, id: 88 })
      )
      jest.spyOn($snackbar, 'success').mockReturnValue()
      jest.spyOn($router, 'replace').mockReturnValue()
    })

    afterAll(() => {
      mocked($router.replace).mockRestore()
      mocked($snackbar.success).mockRestore()
      mocked($api.dwsBillingCopayCoordinations.create).mockRestore()
      mocked(dwsBillingStore.get).mockRestore()
      unmountComponent()
    })

    afterEach(() => {
      mocked($router.replace).mockClear()
      mocked($snackbar.success).mockClear()
      mocked($api.dwsBillingCopayCoordinations.create).mockClear()
      mocked(dwsBillingStore.get).mockClear()
    })

    test('$api.dwsBillingCopayCoordinations.create を呼び出す', async () => {
      await wrapper.vm.submit(form)

      expect($api.dwsBillingCopayCoordinations.create).toHaveBeenCalledTimes(1)
      expect($api.dwsBillingCopayCoordinations.create).toHaveBeenCalledWith({ ...identifiers, form })
      expect(dwsBillingStore.get).toHaveBeenCalledTimes(1)
    })

    test('完了時に詳細画面へ遷移する', async () => {
      await wrapper.vm.submit(form)

      expect($router.replace).toHaveBeenCalledTimes(1)
      expect($router.replace).toHaveBeenCalledWith('/dws-billings/86/bundles/851/statements/24/copay-coordinations/88')
    })

    test('完了時に Snackbar を表示する', async () => {
      await wrapper.vm.submit(form)

      expect($snackbar.success).toHaveBeenCalledTimes(1)
      expect($snackbar.success).toHaveBeenCalledWith('利用者負担上限額管理結果票を作成しました。')
    })

    test.each([
      ['result', '利用者負担上限額管理結果を入力してください。']
    ])(
      'サーバーから 400 Bad Request が返ってきたらエラー内容を表示する: %s',
      async (key, message) => {
        jest.spyOn($api.dwsBillingCopayCoordinations, 'create').mockRejectedValue(
          createAxiosError(HttpStatusCode.BadRequest, {
            errors: {
              [key]: [message]
            }
          })
        )

        await wrapper.vm.submit(form)
        await wrapper.vm.$nextTick()

        const targetWrapper = wrapper.find(`[data-${camelToKebab(key)}]`)

        expect($snackbar.success).not.toHaveBeenCalled()
        expect(targetWrapper.text()).toContain(message)
        expect(targetWrapper).toMatchSnapshot()
      }
    )
  })
})
