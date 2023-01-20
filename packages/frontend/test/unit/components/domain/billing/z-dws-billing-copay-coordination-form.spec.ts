import { ref } from '@nuxtjs/composition-api'
import { MountOptions, Wrapper } from '@vue/test-utils'
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZDwsBillingCopayCoordinationForm from '~/components/domain/billing/z-dws-billing-copay-coordination-form.vue'
import { FormProps } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { DwsBillingOffice } from '~/models/dws-billing-office'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { OfficeId } from '~/models/office'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import { ValidationObserverInstance } from '~/support/validation/types'
import {
  createDwsBillingCopayCoordinationResponseStub
} from '~~/stubs/create-dws-billing-copay-coordination-response-stub'
import { createDwsBillingCopayCoordinationStoreStub } from '~~/stubs/create-dws-billing-copay-coordination-store-stub'
import { createDwsBillingStatementStub } from '~~/stubs/create-dws-billing-statement-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createMockedFormService } from '~~/test/helpers/create-mocked-form-service'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('z-dws-billing-copay-coordination-form.vue', () => {
  const { mount, shallowMount } = setupComponentTest()
  const $form = createMockedFormService()
  const statement = createDwsBillingStatementStub()
  const store = createDwsBillingCopayCoordinationStoreStub(createDwsBillingCopayCoordinationResponseStub())
  const { billing, bundle, copayCoordination } = store.state
  const { items, result } = copayCoordination.value!

  type FormValue = {
    items: Array<{ officeId?: OfficeId, subtotal: Partial<DwsBillingCopayCoordinationsApi.Subtotal> }>
    result: CopayCoordinationResult
  }
  const value: FormValue = {
    items: items.map(x => ({
      officeId: x.office.officeId,
      subtotal: {
        fee: 1000,
        copay: 300,
        coordinatedCopay: 100
      }
    })),
    result
  }
  type Props = DeepPartial<{
    errors: Record<string, any>
    progress: boolean
    value: FormValue
    buttonText: string
    bundle: typeof bundle
    office: DwsBillingOffice
    statement: DwsBillingStatement
    status: DwsBillingStatus
  }>
  const createPropsData = (): Props => ({
    errors: {},
    progress: false,
    value,
    buttonText: '登録',
    bundle,
    office: billing.value!.office,
    statement,
    status: DwsBillingStatus.checking
  })
  let wrapper: Wrapper<Vue & any>

  type MountComponentParams = Omit<MountOptions<Vue>, 'propsData'> & {
    isShallow?: true
    propsData?: Props
    value?: FormProps<DwsBillingCopayCoordinationsApi.Form>['value']
  }

  function mountComponent ({ isShallow, propsData: d, mocks: m, value, ...other }: MountComponentParams = {}) {
    const data = createPropsData()
    const propsData = {
      ...data,
      ...d,
      value: value ?? {
        ...data.value,
        ...d?.value
      }
    }
    const mocks = { ...m, $form }
    const fn = isShallow ? shallowMount : mount
    wrapper = fn(ZDwsBillingCopayCoordinationForm, { propsData, mocks, ...other })
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

  describe('display', () => {
    it('should be rendered correctly', () => {
      mountComponent()
      expect(wrapper).toMatchSnapshot()
      unmountComponent()
    })
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance | undefined

    async function execValidate (targetObserver = observer) {
      await targetObserver!.validate()
      jest.runOnlyPendingTimers()
    }

    function localMount (value: Partial<FormValue> = {}, params: Omit<MountComponentParams, 'propsData'> = {}) {
      mountComponent({ ...params, propsData: { value } })
      observer = getValidationObserver(wrapper)
    }

    function localUnmount () {
      observer = undefined
      unmountComponent()
    }

    it('should pass when input correctly', async () => {
      localMount()
      await execValidate()
      expect(observer).toBePassed()
      localUnmount()
    })

    it('should fail when result is empty', async () => {
      localMount({ result: undefined }, { stubs: ['v-simple-table'] })
      await execValidate()
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-result] .v-messages').text()).toBe('入力してください。')
      localUnmount()
    })

    it('should fail when officeId is empty', async () => {
      localMount({
        items: [{
          officeId: undefined,
          subtotal: {
            fee: 1000,
            copay: 300,
            coordinatedCopay: 100
          }
        }]
      })
      const childObserver = getValidationObserver(wrapper, 'childObserver0')
      await execValidate(childObserver)
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-error-messages="0"]').text()).toContain('事業所名を入力してください。')
      localUnmount()
    })

    it('should fail when fee is zero', async () => {
      localMount({
        items: [{
          officeId: 30,
          subtotal: {
            fee: 0,
            copay: 0,
            coordinatedCopay: 0
          }
        }]
      })
      const childObserver = getValidationObserver(wrapper, 'childObserver0')
      await execValidate(childObserver)
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-error-messages="0"]').text()).toContain('総費用額には1以上の値を入力してください。')
      localUnmount()
    })

    it.each([
      ['fee', '総費用額'],
      ['copay', '利用者負担額'],
      ['coordinatedCopay', '管理結果後利用者負担額']
    ])('should fail when subtotal.%s is empty', async (name, displayName) => {
      localMount({
        items: [{
          officeId: 30,
          subtotal: {
            ...{
              fee: 1000,
              copay: 300,
              coordinatedCopay: 100
            },
            [name]: undefined
          }
        }]
      })
      const childObserver = getValidationObserver(wrapper, 'childObserver0')
      await execValidate(childObserver)
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-error-messages="0"]').text()).toContain(`${displayName}を入力してください。`)
      localUnmount()
    })

    it.each([
      [['copay', 1200, '利用者負担額'], ['fee', 1000, '総費用額']],
      [['coordinatedCopay', 2000, '管理結果後利用者負担額'], ['copay', 1500, '利用者負担額']]
    ])('should fail when %j is greater than %j', async (low, high) => {
      localMount({
        items: [{
          officeId: 30,
          subtotal: {
            ...{
              fee: 5000,
              copay: 2000,
              coordinatedCopay: 1000
            },
            ...{
              [low[0]]: low[1],
              [high[0]]: high[1]
            }
          }
        }]
      })
      const childObserver = getValidationObserver(wrapper, 'childObserver0')
      await execValidate(childObserver)
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-error-messages="0"]').text()).toContain(`${low[2]}には${high[2]}以下の値を入力してください。`)
      localUnmount()
    })

    it('should fail if subtotal.copay is greater than user\'s copay limit', async () => {
      mountComponent({
        propsData: {
          value: {
            items: [{
              officeId: 30,
              subtotal: {
                fee: 5000,
                copay: 2000,
                coordinatedCopay: 1000
              }
            }]
          },
          statement: {
            ...statement,
            user: { ...statement.user, copayLimit: 100 }
          }
        }
      })

      observer = getValidationObserver(wrapper)
      await execValidate()
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-error-messages="0"]').text()).toContain('利用者負担額が利用者負担上限月額を超えないようにしてください。')
      observer = undefined
      unmountComponent()
    })

    it('should fail if total coordinated copay is greater than user\'s copay limit', async () => {
      mountComponent({
        propsData: {
          statement: {
            ...statement,
            user: { ...statement.user, copayLimit: 100 }
          }
        }
      })

      observer = getValidationObserver(wrapper)
      await execValidate()
      expect(observer).not.toBePassed()
      expect(wrapper.find('[data-total-coordinated-copay]').text()).toBe('管理結果後利用者負担額の合計が利用者負担上限月額を超えないようにしてください。')
      observer = undefined
      unmountComponent()
    })

    describe('number of offices', () => {
      beforeAll(() => {
        localMount({
          items: [{
            officeId: 30,
            subtotal: {
              fee: 1000,
              copay: 300,
              coordinatedCopay: 100
            }
          }]
        })
      })

      afterAll(() => {
        localUnmount()
      })

      it.each([
        ['appropriated', CopayCoordinationResult.appropriated],
        ['not coordinated', CopayCoordinationResult.notCoordinated],
        ['coordinated', CopayCoordinationResult.coordinated]
      ])('should fail if it has only one office when %s', async (_, result) => {
        await wrapper.setData({ result })
        await setData(wrapper, {
          serviceProvisionStatus: {
            isProvided: ref(true)
          }
        })
        await execValidate()
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-number-of-offices]').text()).toBe('上限額管理を行う場合は利用者負担額集計・調整欄を2件以上登録してください。')
      })
    })
    describe('office Only', () => {
      beforeAll(() => {
        localMount({
          items: [{
            officeId: 30,
            subtotal: {
              fee: 1000,
              copay: 300,
              coordinatedCopay: 100
            }
          },
          {
            officeId: 31,
            subtotal: {
              fee: 1000,
              copay: 300,
              coordinatedCopay: 100
            }
          }]

        })
      })

      afterAll(() => {
        localUnmount()
      })

      it.each([
        ['appropriated', CopayCoordinationResult.appropriated],
        ['not coordinated', CopayCoordinationResult.notCoordinated],
        ['coordinated', CopayCoordinationResult.coordinated]
      ])('should fail if it has some offices when %s', async (_, result) => {
        await wrapper.setData({ result })
        await setData(wrapper, {
          serviceProvisionStatus: {
            isProvided: ref(false)
          }
        })
        await execValidate()
        expect(observer).not.toBePassed()
        expect(wrapper.find('[data-office-only]').text()).toBe('他事業所におけるサービス提供がない場合は、利用者負担額集計・調整欄を1件のみ登録してください。')
      })
    })
  })

  describe('initial isProvided value', () => {
    it('should be true if new copayCoordination display', () => {
      mountComponent({
        value: {}
      })

      expect(wrapper.vm.serviceProvisionStatus.isProvided.value).toBeTrue()
    })

    it('should be false if edit copayCoordination display and items length is 1', () => {
      mountComponent({
        value: {
          items: [{
            officeId: 1,
            subtotal: {
              fee: 1000,
              copay: 300,
              coordinatedCopay: 100
            }
          }]
        }
      })

      expect(wrapper.vm.serviceProvisionStatus.isProvided.value).toBeFalse()
    })

    it('should be true if edit copayCoordination display and items length is not 1', () => {
      mountComponent({
        value: {
          items: [
            {
              officeId: 1,
              subtotal: {
                fee: 1000,
                copay: 300,
                coordinatedCopay: 100
              }
            },
            {
              officeId: 2,
              subtotal: {
                fee: 1000,
                copay: 300,
                coordinatedCopay: 100
              }
            }
          ]
        }
      })

      expect(wrapper.vm.serviceProvisionStatus.isProvided.value).toBeTrue()
    })
  })

  describe('add item button', () => {
    beforeAll(() => {
      mountComponent()
    })

    it('should be rendered if ServiceProvisionStatus is provided', async () => {
      await setData(wrapper, {
        serviceProvisionStatus: {
          isProvided: ref(true)
        }
      })

      expect(wrapper.find('[data-add-item-btn]')).toExist()
    })

    it('should not be rendered if ServiceProvisionStatus is not provided', async () => {
      await setData(wrapper, {
        serviceProvisionStatus: {
          isProvided: ref(false)
        }
      })

      expect(wrapper.find('[data-add-item-btn]')).not.toExist()
    })
  })
})
