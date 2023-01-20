/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZBillingFileListCard from '~/components/domain/billing/z-billing-file-list-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createLtcsBillingFileStubs } from '~~/stubs/create-ltcs-billing-file-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-billing-file-list-card.vue', () => {
  const { mount } = setupComponentTest()
  const items = createLtcsBillingFileStubs()

  let wrapper: Wrapper<Vue>

  function mountComponent (options: Partial<MountOptions<Vue>> = {}) {
    wrapper = mount(ZBillingFileListCard, {
      ...options,
      ...provides([sessionStoreKey, createAuthStub({ isSystemAdmin: true })])
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it.each([
    ['should be rendered correctly', items, true],
    ['should be rendered correctly when it is not downloadable', items, false],
    ['should be rendered correctly when items is empty', [], true]
  ])('%s', (_, items, downloadable) => {
    const propsData = {
      items,
      downloadable
    }
    mountComponent({ propsData })
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
