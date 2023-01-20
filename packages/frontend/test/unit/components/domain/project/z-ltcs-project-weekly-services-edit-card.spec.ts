/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZLtcsProjectWeeklyServicesEditCard
  from '~/components/domain/project/z-ltcs-project-weekly-services-edit-card.vue'
import {
  ltcsProjectServiceMenuResolverStateKey
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { Plugins } from '~/plugins'
import {
  createLtcsHomeVisitLongTermCareDictionaryIndexResponseStub
} from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-index-response-stub'
import { createLtcsProjectServiceMenuStubs } from '~~/stubs/create-ltcs-project-service-menu-stub'
import {
  createLtcsProjectServiceMenusResolverStoreStub
} from '~~/stubs/create-ltcs-project-service-menus-resolver-store-stub'
import { createLtcsProjectStub } from '~~/stubs/create-ltcs-project-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-ltcs-project-weekly-services-edit-card.vue', () => {
  const { mount } = setupComponentTest()
  const $api = createMockedApi('ltcsHomeVisitLongTermCareDictionary')
  const mocks: Partial<Plugins> = {
    $api
  }
  const menuResolverStore = createLtcsProjectServiceMenusResolverStoreStub({
    menus: createLtcsProjectServiceMenuStubs()
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const dictionaryIndexResponse = createLtcsHomeVisitLongTermCareDictionaryIndexResponseStub()
  const project = createLtcsProjectStub()

  let wrapper: Wrapper<Vue & any>

  beforeEach(async () => {
    jest.spyOn($api.ltcsHomeVisitLongTermCareDictionary, 'getIndex').mockResolvedValue(dictionaryIndexResponse)
    wrapper = mount(ZLtcsProjectWeeklyServicesEditCard, ({
      ...provides(
        [ltcsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [ownExpenseProgramResolverStateKey, ownExpenseProgramResolverStore.state]
      ),
      mocks,
      propsData: {
        value: project.programs[0]
      },
      stubs: ['z-validate-error-messages']
    }))
    await wrapper.vm.$nextTick()
  })

  afterEach(() => {
    wrapper.destroy()
    mocked($api.ltcsHomeVisitLongTermCareDictionary.getIndex).mockReset()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should be add content item when click add button', async () => {
    const before = wrapper.vm.value.contents.length
    await click(() => wrapper.find('[data-add-content]'))
    expect(wrapper.vm.value.contents.length).toBe(before + 1)
  })
})
