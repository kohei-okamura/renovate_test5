/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import deepmerge from 'deepmerge'
import flushPromises from 'flush-promises'
import Vue from 'vue'
import ZUserBillingItemDetailCard from '~/components/domain/user-billing/z-user-billing-item-detail-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { UserBilling } from '~/models/user-billing'
import { ValidationObserverInstance } from '~/support/validation/types'
import { createUserBillingStub } from '~~/stubs/create-user-billing-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { getValidationObserver } from '~~/test/helpers/get-validation-observer'
import { provides } from '~~/test/helpers/provides'
import { setData } from '~~/test/helpers/set-data'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-user-billing-item-card.vue', () => {
  const { mount } = setupComponentTest()
  const userBilling = createUserBillingStub()

  const defaultPropsData = {
    userBilling: {
      ...userBilling,
      carriedOverAmount: -50,
      result: UserBillingResult.pending,
      totalAmount: 200
    }
  }

  let wrapper: Wrapper<Vue & any>

  type MountComponentArguments = MountOptions<Vue> & {
    auth?: Partial<Auth>
    propsData?: { userBilling: DeepPartial<UserBilling> }
  }

  function mountComponent ({ auth, propsData, ...options }: MountComponentArguments = {}) {
    wrapper = mount(ZUserBillingItemDetailCard, {
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
  })

  describe('permission', () => {
    it('should be rendered when the staff have permissions.updateUserBillings', () => {
      mountComponent({ auth: { permissions: [Permission.updateUserBillings] } })
      expect(wrapper.find('[data-carried-over-amount-button]')).toExist()
      unmountComponent()
    })

    it('should not be rendered when the result is neither pending nor none', () => {
      mountComponent({ propsData: { userBilling: { result: UserBillingResult.paid } } })
      expect(wrapper.find('[data-carried-over-amount-button]')).not.toExist()
      unmountComponent()
    })

    it('should be rendered when the result is pending', () => {
      mountComponent({ propsData: { userBilling: { result: UserBillingResult.pending } } })
      expect(wrapper.find('[data-carried-over-amount-button]')).toExist()
      unmountComponent()
    })

    it('should be rendered when the result is none', () => {
      mountComponent({ propsData: { userBilling: { result: UserBillingResult.none } } })
      expect(wrapper.find('[data-carried-over-amount-button]')).toExist()
      unmountComponent()
    })

    it('should be rendered when the staff does not have permissions.updateUserBillings', () => {
      mountComponent({ auth: { permissions: [] } })
      expect(wrapper.find('[data-carried-over-amount-button]')).not.toExist()
      unmountComponent()
    })
  })

  describe('validation', () => {
    let observer: ValidationObserverInstance

    async function validate (values: { changedCarriedOverAmount?: number | string } = {}): Promise<void> {
      await setData(wrapper, values)
      await observer.validate()
      jest.runOnlyPendingTimers()
    }

    beforeAll(() => {
      mountComponent()
      observer = getValidationObserver(wrapper)
    })

    afterAll(() => {
      unmountComponent()
    })

    afterEach(() => {
      observer.reset()
    })

    it('should not fail if carriedOverAmount is negative number', async () => {
      const messageWrapper = wrapper.find('[data-carried-over-amount-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ changedCarriedOverAmount: -1 })
      expect(observer).toBePassed()
    })

    it('should not fail if carriedOverAmount is zero', async () => {
      const messageWrapper = wrapper.find('[data-carried-over-amount-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ changedCarriedOverAmount: 0 })
      expect(observer).toBePassed()
    })

    it('should not fail if carriedOverAmount plus totalAmount is zero', async () => {
      const messageWrapper = wrapper.find('[data-carried-over-amount-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      // FYI totalAmount には既に carriedOverAmount が含まれているため指定可能な繰越金額の上限は
      // totalAmount(200) - 現在の carriedOverAmount(-50) になる
      await validate({ changedCarriedOverAmount: -250 })
      expect(observer).toBePassed()
    })

    it('should fail if carriedOverAmount is string', async () => {
      const messageWrapper = wrapper.find('[data-carried-over-amount-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ changedCarriedOverAmount: 'fail' })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('半角数字のみで入力してください。')
    })

    it('should fail if carriedOverAmount plus totalAmount is negative number', async () => {
      const messageWrapper = wrapper.find('[data-carried-over-amount-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ changedCarriedOverAmount: -251 })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('繰越金額と各明細の合計金額の合計が0円以上になるようにしてください。')
    })

    it('should fail if carriedOverAmount is not changed', async () => {
      const messageWrapper = wrapper.find('[data-carried-over-amount-form] .v-messages')
      expect(messageWrapper.text()).toBe('')
      await validate({ changedCarriedOverAmount: -50 })
      expect(observer).not.toBePassed()
      expect(messageWrapper.text()).toBe('繰越金額を変更してください。')
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
        const value = '-100'

        await setData(wrapper, { changedCarriedOverAmount: value })
        await wrapper.vm.showChangeDialog()
        await wrapper.vm.$nextTick()

        const dialog = wrapper.findComponent({ name: 'z-prompt-dialog' })

        await dialog.vm.$emit('click:positive', new MouseEvent('click'))
        await flushPromises()

        const emitted = wrapper.emitted('click:update')
        expect(emitted).toBeTruthy()
        expect(emitted!.length).toBe(1)
        expect(emitted![0][0]).toStrictEqual(parseInt(value))
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
