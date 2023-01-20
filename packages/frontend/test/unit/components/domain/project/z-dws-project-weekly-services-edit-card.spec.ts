/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZDwsProjectWeeklyServicesEditCard from '~/components/domain/project/z-dws-project-weekly-services-edit-card.vue'
import { dwsProjectServiceMenuResolverStateKey } from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import {
  createDwsProjectServiceMenuResolverStoreStub
} from '~~/stubs/create-dws-project-service-menu-resolver-store-stub'
import { createDwsProjectServiceMenuStubs } from '~~/stubs/create-dws-project-service-menu-stub'
import { createDwsProjectStub } from '~~/stubs/create-dws-project-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-dws-project-weekly-services-edit-card.vue', () => {
  const { mount } = setupComponentTest()
  const menuResolverStore = createDwsProjectServiceMenuResolverStoreStub({
    menus: createDwsProjectServiceMenuStubs()
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const dwsProject = createDwsProjectStub()

  let wrapper: Wrapper<Vue & any>

  beforeEach(async () => {
    wrapper = mount(ZDwsProjectWeeklyServicesEditCard, ({
      ...provides(
        [dwsProjectServiceMenuResolverStateKey, menuResolverStore.state],
        [ownExpenseProgramResolverStateKey, ownExpenseProgramResolverStore.state]
      ),
      propsData: { value: dwsProject.programs[0] },
      stubs: ['z-validate-error-messages']
    }))
    await wrapper.vm.$nextTick()
  })

  afterEach(() => {
    wrapper.destroy()
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
