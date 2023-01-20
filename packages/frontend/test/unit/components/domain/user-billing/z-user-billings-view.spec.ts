/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { isEmpty } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZUserBillingsView from '~/components/domain/user-billing/z-user-billings-view.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useUserBillingsStore } from '~/composables/stores/use-user-billings-store'
import { Auth } from '~/models/auth'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { UserBilling } from '~/models/user-billing'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { mapValues } from '~/support/utils/map-values'
import { createUserBillingStubs } from '~~/stubs/create-user-billing-stub'
import { createUserBillingsStoreStub } from '~~/stubs/create-user-billings-store-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createFormData } from '~~/test/helpers/create-form-data'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { TEST_NOW } from '~~/test/helpers/date'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-user-billings-view.vue', () => {
  const { mount } = setupComponentTest()
  const userId = 182
  const userBillings = createUserBillingStubs(5)
  const userBillingsStore = createUserBillingsStoreStub({ userBillings })

  const $router = createMockedRouter()
  const defaultParams = {
    page: 1,
    itemsPerPage: 10
  }
  const userBilling: UserBilling = {
    ...userBillings[0],
    dwsItem: {
      dwsStatementId: 2,
      score: 530,
      unitCost: 37274,
      subtotalCost: 95060,
      tax: 0,
      medicalDeductionAmount: 61113,
      benefitAmount: 13305,
      subsidyAmount: 59337,
      totalAmount: 62643,
      copayWithoutTax: 13956,
      copayWithTax: 0
    },
    ltcsItem: {
      ltcsStatementId: 3,
      score: 495,
      unitCost: 23119,
      subtotalCost: 30017,
      tax: 8,
      medicalDeductionAmount: 54959,
      benefitAmount: 9690,
      subsidyAmount: 5094,
      totalAmount: 91522,
      copayWithoutTax: 94646,
      copayWithTax: 757168
    },
    otherItems: [
      {
        score: 375,
        unitCost: 59054,
        subtotalCost: 16516,
        tax: 10,
        medicalDeductionAmount: 35199,
        totalAmount: 20506,
        copayWithoutTax: 46618,
        copayWithTax: 466180
      },
      {
        score: 375,
        unitCost: 59054,
        subtotalCost: 16516,
        tax: 10,
        medicalDeductionAmount: 35199,
        totalAmount: 20506,
        copayWithoutTax: 46618,
        copayWithTax: 466180
      },
      {
        score: 375,
        unitCost: 59054,
        subtotalCost: 16516,
        tax: 10,
        medicalDeductionAmount: 35199,
        totalAmount: 20506,
        copayWithoutTax: 46618,
        copayWithTax: 466180
      }
    ],
    result: UserBillingResult.paid
  }
  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = {
    auth?: Partial<Auth>
    options?: MountOptions<Vue>
    params?: Partial<UserBillingsApi.GetIndexParams>
  }

  function mountComponent ({ auth, options, params }: MountComponentParams = {}) {
    const query = mapValues({ ...defaultParams, ...params }, x => isEmpty(x) ? '' : String(x))
    const $routes = createMockedRoutes({ query })
    wrapper = mount(ZUserBillingsView, {
      propsData: {
        userId
      },
      ...options,
      ...provides([sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })]),
      mocks: {
        $router,
        $routes,
        ...options?.mocks
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mocked(useUserBillingsStore).mockReturnValue(userBillingsStore)
    mountComponent({})
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
    mocked(useUserBillingsStore).mockReset()
  })

  it.each([
    ['invoice button if result is not UserBillingResult.none', { result: UserBillingResult.none }, 'invoice'],
    ['receipt button if result is not UserBillingResult.paid', { result: UserBillingResult.pending }, 'receipt'],
    ['receipt button if result is not UserBillingResult.none', { result: UserBillingResult.none }, 'receipt'],
    ['statement button if dwsItem and ltcsItem is undefined', { ltcsItem: undefined, dwsItem: undefined }, 'statement']
  ])('should not rendered download %s', (_, stubValue, btnName) => {
    mocked(useUserBillingsStore).mockReturnValue(
      createUserBillingsStoreStub({ userBillings: [{ ...userBilling, ...stubValue }] })
    )
    mountComponent({})
    expect(wrapper.find(`[data-download-button="${btnName}"]`)).not.toExist()
    unmountComponent()
    mocked(useUserBillingsStore).mockReset()
  })

  it('should not rendered download notice button if dwsItem is undefined', () => {
    mocked(useUserBillingsStore).mockReturnValue(
      createUserBillingsStoreStub({ userBillings: [{ ...userBilling, dwsItem: undefined }] })
    )
    mountComponent({})
    expect(wrapper.find('[data-download-button="statement"]')).toExist()
    expect(wrapper.find('[data-download-button="notice"]')).not.toExist()
    unmountComponent()
    mocked(useUserBillingsStore).mockReset()
  })

  it('should call userBillingsStore.getIndex correct query', () => {
    expect(userBillingsStore.getIndex).toHaveBeenCalledTimes(1)
    expect(userBillingsStore.getIndex).toHaveBeenCalledWith(createFormData({ ...defaultParams, userId }))
  })

  describe('download', () => {
    describe.each<string>([
      ['invoice'],
      ['receipt'],
      ['notice'],
      ['statement']
    ])('download %s', type => {
      beforeEach(() => {
        const stub = createUserBillingsStoreStub({ userBillings: [{ ...userBilling }] })
        mocked(useUserBillingsStore).mockReturnValue(stub)
        mountComponent({})
      })

      afterEach(() => {
        unmountComponent()
        mocked(useUserBillingsStore).mockRestore()
      })

      it('should show date confirmation dialog', async () => {
        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        expect(dialog.props().active).toBeFalse()

        await click(() => wrapper.find(`[data-download-button="${type}"]`))
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it(`should emit download:${type} when positive clicked`, async () => {
        await click(() => wrapper.find(`[data-download-button="${type}"]`))
        await wrapper.vm.$nextTick()

        const testYearMonth = TEST_NOW.toFormat(ISO_MONTH_FORMAT)
        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:positive', testYearMonth)

        const actual = wrapper.emitted(`click:download:${type}`)

        expect(actual).toHaveLength(1)
        expect(actual![0][0]).toEqual({ ids: [userBilling.id], issuedOn: testYearMonth })
      })

      it(`should not emit download:${type} when when negative clicked`, async () => {
        await click(() => wrapper.find(`[data-download-button="${type}"]`))
        await wrapper.vm.$nextTick()

        const dialog = wrapper.find('[data-date-confirm-dialog="download"]')

        await dialog.vm.$emit('click:negative')

        const actual = wrapper.emitted(`click:download:${type}`)

        expect(actual).toBeFalsy()
      })
    })
  })
})
