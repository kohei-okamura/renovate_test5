/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZServiceReportFormatThreeOne from '~/components/domain/billing/z-service-report-format-three-one.vue'
import { createDwsBillingBundleStub } from '~~/stubs/create-dws-billing-bundle-stub'
import { createDwsBillingServiceReportStub } from '~~/stubs/create-dws-billing-service-report-stub'
import { createDwsBillingUserStub } from '~~/stubs/create-dws-billing-user-stub'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-service-report-format-three-one.vue', () => {
  const { mount } = setupComponentTest()
  const report = createDwsBillingServiceReportStub({
    bundle: createDwsBillingBundleStub(),
    user: createDwsBillingUserStub(),
    id: 10
  })

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZServiceReportFormatThreeOne, {
      propsData: { report }
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
})
