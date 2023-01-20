/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { Permission } from '@zinger/enums/lib/permission'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import deepmerge from 'deepmerge'
import flushPromises from 'flush-promises'
import Vue from 'vue'
import ZBillingDestinationAccount from '~/components/domain/user-billing/z-billing-destination-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { UserBillingDestination } from '~/models/user-billing-destination'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-bank-account-card.vue', () => {
  const { mount } = setupComponentTest()
  const userBilling = createUserBillingStub()

  type PropsData = {
    billingDestination: UserBillingDestination
    result: UserBillingResult
  }

  const defaultPropsData: PropsData = {
    billingDestination: {
      ...userBilling.user.billingDestination,
      paymentMethod: PaymentMethod.transfer
    },
    result: UserBillingResult.pending
  }

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    auth?: Partial<Auth>
    propsData?: DeepPartial<PropsData>
  }

  function mountComponent ({ auth, propsData, ...options }: MountComponentArguments = {}) {
    wrapper = mount(ZBillingDestinationAccount, {
      propsData: deepmerge(defaultPropsData, propsData ?? {}),
      ...options,
      mocks: {
        ...options?.mocks
      },
      ...provides([sessionStoreKey, createAuthStub(auth ?? { isSystemAdmin: true })])
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('button', () => {
    it('should be rendered when the staff has permissions.updateUserBillings', () => {
      mountComponent({ auth: { permissions: [Permission.updateUserBillings] } })
      expect(wrapper.find('[data-payment-method-button]')).toExist()
      unmountComponent()
    })

    it.each([
      ['inProgress', UserBillingResult.inProgress],
      ['paid', UserBillingResult.paid],
      ['unpaid', UserBillingResult.unpaid],
      ['none', UserBillingResult.none]
    ])('should not be rendered when the result is %s', (_, result) => {
      mountComponent({ propsData: { result } })
      expect(wrapper.find('[data-payment-method-button]')).not.toExist()
      unmountComponent()
    })

    it('should be rendered when the result is pending', () => {
      mountComponent({ propsData: { result: UserBillingResult.pending } })
      expect(wrapper.find('[data-payment-method-button]')).toExist()
      unmountComponent()
    })

    it('should not be rendered when the staff does not have permissions.updateUserBillings', () => {
      mountComponent({ auth: { permissions: [] } })
      expect(wrapper.find('[data-payment-method-button]')).not.toExist()
      unmountComponent()
    })
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    function createPropsData (paymentMethod: PaymentMethod) {
      return {
        billingDestination: {
          paymentMethod
        }
      }
    }

    async function validate (values: { selectedPaymentMethod: PaymentMethod | undefined }): Promise<void> {
      await setData(wrapper, values)
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    it('should not fail when paymentMethod change from withdrawal to withdrawal', async () => {
      await mountComponent({ propsData: createPropsData(PaymentMethod.withdrawal) })
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.withdrawal })
      expect(observer).toBePassed()

      unmountComponent()
    })

    it('should not fail when paymentMethod change from withdrawal to transfer', async () => {
      await mountComponent({ propsData: createPropsData(PaymentMethod.withdrawal) })
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.transfer })
      expect(observer).toBePassed()

      unmountComponent()
    })

    it('should not fail when paymentMethod change from withdrawal to collection', async () => {
      await mountComponent({ propsData: createPropsData(PaymentMethod.transfer) })
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.collection })
      expect(observer).toBePassed()

      unmountComponent()
    })

    it('should not fail when paymentMethod change from transfer to collection', async () => {
      await mountComponent({ propsData: createPropsData(PaymentMethod.transfer) })
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.collection })
      expect(observer).toBePassed()

      unmountComponent()
    })

    it('should not fail when paymentMethod change from collection to transfer', async () => {
      await mountComponent({ propsData: createPropsData(PaymentMethod.collection) })
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.transfer })
      expect(observer).toBePassed()

      unmountComponent()
    })

    it('should fail if paymentMethod is undefined', async () => {
      await mountComponent()
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: undefined })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('入力してください。')

      unmountComponent()
    })

    it('should fail when paymentMethod change to none', async () => {
      await mountComponent()
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.none })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('口座振替への変更はできません。')

      unmountComponent()
    })

    it('should fail when paymentMethod change from transfer to withdrawal', async () => {
      await mountComponent({ propsData: createPropsData(PaymentMethod.transfer) })
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.withdrawal })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('口座振替への変更はできません。')

      unmountComponent()
    })

    it('should fail when paymentMethod change from collection to withdrawal', async () => {
      await mountComponent({ propsData: createPropsData(PaymentMethod.collection) })
      observer = getValidationObserver(wrapper)

      const messageWrapper = wrapper.find('[data-payment-method-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ selectedPaymentMethod: PaymentMethod.withdrawal })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('口座振替への変更はできません。')

      unmountComponent()
    })
  })

  describe('event', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    describe('update carried over amount', () => {
      it('should show dialog', async () => {
        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })
        expect(dialog.props().active).toBeFalse()

        await wrapper.vm.showChangeDialog()
        await wrapper.vm.$nextTick()

        // props.active が true になっていることを確認する
        expect(dialog.props().active).toBeTrue()
      })

      it('should emit click:update when positive clicked', async () => {
        const expected = PaymentMethod.collection

        await setData(wrapper, { selectedPaymentMethod: expected })
        await wrapper.vm.showChangeDialog()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })

        await dialog.vm.$emit('click:positive', new MouseEvent('click'))
        await flushPromises()

        const emitted = wrapper.emitted('click:update')
        expect(emitted).toBeTruthy()
        expect(emitted!.length).toBe(1)
        expect(emitted![0][0]).toStrictEqual(expected)
        expect(dialog.props().active).toBeFalse()
      })

      it('should not emit click:update when positive clicked if value is not changed', async () => {
        await setData(wrapper, { selectedPaymentMethod: PaymentMethod.transfer })
        await wrapper.vm.showChangeDialog()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })

        await dialog.vm.$emit('click:positive', new MouseEvent('click'))
        await flushPromises()

        const emitted = wrapper.emitted('click:update')
        // FIXME
        // 毎回 mount をするのはコストが高いので避けたいが、それだとケースごとに emitted をクリアできない
        // ひとまず length が増えていないことで呼ばれていないことを確認しているが emitted をクリアしたい
        expect(emitted!.length).toBe(1)
        expect(dialog.props().active).toBeFalse()
      })

      it('should not emit click:update when negative clicked', async () => {
        await wrapper.vm.showChangeDialog()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })

        await dialog.vm.$emit('click:negative', new MouseEvent('click'))
        await flushPromises()

        const emitted = wrapper.emitted('click:update')
        // FIXME
        // 毎回 mount をするのはコストが高いので避けたいが、それだとケースごとに emitted をクリアできない
        // ひとまず length が増えていないことで呼ばれていないことを確認しているが emitted をクリアしたい
        expect(emitted!.length).toBe(1)
        expect(dialog.props().active).toBeFalse()
      })
    })
  })
})
