/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZBankAccountCard from '~/components/domain/bank-account/z-bank-account-card.vue'
import { BANK_ID_MIN, createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-bank-account-card.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  function mountComponent (options: MountOptions<Vue>) {
    wrapper = mount(ZBankAccountCard, options)
  }

  it('should be rendered correctly without title', () => {
    const propsData = {
      ...createBankAccountStub(BANK_ID_MIN)
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly with title', () => {
    const propsData = {
      ...createBankAccountStub(BANK_ID_MIN + 1),
      title: '給与振込口座'
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
  })
})
