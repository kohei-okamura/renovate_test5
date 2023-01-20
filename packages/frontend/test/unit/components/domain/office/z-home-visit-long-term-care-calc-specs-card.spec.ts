/*
* Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZHomeVisitLongTermCareCalcSpecsCard
  from '~/components/domain/office/z-home-visit-long-term-care-calc-specs-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createHomeVisitLongTermCareCalcSpecStubs } from '~~/stubs/create-home-visit-long-term-care-calc-spec-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-home-visit-long-term-care-calc-specs-card.vue', () => {
  const { mount } = setupComponentTest()
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const office = createOfficeStub()
    const items = createHomeVisitLongTermCareCalcSpecStubs(office.id)
    const propsData = { items, office }
    wrapper = mount(ZHomeVisitLongTermCareCalcSpecsCard, {
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
