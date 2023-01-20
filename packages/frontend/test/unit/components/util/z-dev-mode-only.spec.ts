/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { Wrapper } from '@vue/test-utils'
import { noop } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZDevModeOnly from '~/components/util/z-dev-mode-only.vue'
import { useObservableLocalStorage } from '~/composables/use-observable-local-storage'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-observable-local-storage')

describe('z-dev-mode-only.vue', () => {
  const { shallowMount } = setupComponentTest()

  let wrapper: Wrapper<Vue>

  async function mountComponent () {
    const scopedSlots = {
      default: '<span data-slot-default>開発者モード</span>'
    }
    wrapper = shallowMount(ZDevModeOnly, {
      scopedSlots
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent (): void {
    wrapper.destroy()
  }

  it('should render when developer mode is enabled', async () => {
    mocked(useObservableLocalStorage).mockReturnValueOnce(computed({ get: () => true, set: () => noop() }))
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
    mocked(useObservableLocalStorage).mockClear()
  })

  it('should not render when developer mode is disabled', async () => {
    mocked(useObservableLocalStorage).mockReturnValueOnce(computed({ get: () => false, set: () => noop() }))
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
    mocked(useObservableLocalStorage).mockClear()
  })
})
