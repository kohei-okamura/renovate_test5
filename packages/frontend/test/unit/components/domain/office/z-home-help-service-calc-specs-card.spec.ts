/*
* Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
* UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
*/
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZHomeHelpServiceCalcSpecsCard from '~/components/domain/office/z-home-help-service-calc-specs-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { createHomeHelpServiceCalcSpecStubs } from '~~/stubs/create-home-help-service-calc-spec-stub'
import { createOfficeStub } from '~~/stubs/create-office-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-home-help-service-calc-specs-card.vue', () => {
  const { mount } = setupComponentTest()
  const office = createOfficeStub()
  const items = createHomeHelpServiceCalcSpecStubs(office.id)
  let wrapper: Wrapper<Vue & any>

  beforeAll(() => {
    const propsData = { items, office }
    wrapper = mount(ZHomeHelpServiceCalcSpecsCard, {
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

  it('should be create itemLink', () => {
    const item = { id: 3 }
    const itemLink = wrapper.vm.accordionOptions.itemLink(item)
    const expectedLink = `/offices/${office.id}/home-help-service-calc-specs/${item.id}/edit`
    expect(itemLink).toBe(expectedLink)
  })

  describe('footer', () => {
    it('should change text and icon when clicked footer btn', async () => {
      await click(() => wrapper.find('[data-toggle-expanded-btn]'))
      expect(wrapper.find('[data-toggle-expanded-btn]')).toMatchSnapshot()
    })
  })
})
