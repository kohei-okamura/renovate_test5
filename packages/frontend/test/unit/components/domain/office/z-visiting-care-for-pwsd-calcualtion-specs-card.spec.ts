/*
* Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZVisitingCareForPwsdCalcSpecsCard from '~/components/domain/office/z-visiting-care-for-pwsd-calc-specs-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createVisitingCareForPwsdCalcSpecStubs } from '~~/stubs/create-visiting-care-for-pwsd-calc-spec-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-visiting-care-for-pwsd-calc-specs-card.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const office = createOfficeStub()
    const items = createVisitingCareForPwsdCalcSpecStubs(office.id)
    const propsData = { items, office }
    wrapper = mount(ZVisitingCareForPwsdCalcSpecsCard, {
      ...provides(
        [sessionStoreKey, createAuthStub({ isSystemAdmin: true })]
      ),
      propsData
    })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  describe('footer', () => {
    it('should change text and icon when clicked footer btn', async () => {
      await click(() => wrapper.find('[data-toggle-expanded-btn]'))
      expect(wrapper.find('[data-toggle-expanded-btn]')).toMatchSnapshot()
    })
  })
})
