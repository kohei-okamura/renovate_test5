/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ZLtcsProjectWeeklyServicesCard from '~/components/domain/project/z-ltcs-project-weekly-services-card.vue'
import {
  ltcsProjectServiceMenuResolverStateKey
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { ownExpenseProgramResolverStoreKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { createLtcsProjectServiceMenuStubs } from '~~/stubs/create-ltcs-project-service-menu-stub'
import {
  createLtcsProjectServiceMenusResolverStoreStub
} from '~~/stubs/create-ltcs-project-service-menus-resolver-store-stub'
import { createLtcsProjectStub } from '~~/stubs/create-ltcs-project-stub'
import { createOwnExpenseProgramResolverStoreStub } from '~~/stubs/create-own-expense-program-resolver-stub'
import { createOwnExpenseProgramStubs } from '~~/stubs/create-own-expense-program-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-ltcs-project-weekly-services-card.vue', () => {
  const { mount } = setupComponentTest()
  const menusResolverStore = createLtcsProjectServiceMenusResolverStoreStub({
    menus: createLtcsProjectServiceMenuStubs()
  })
  const ownExpenseProgramResolverStore = createOwnExpenseProgramResolverStoreStub({
    ownExpensePrograms: createOwnExpenseProgramStubs()
  })
  const project = createLtcsProjectStub()

  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    wrapper = mount(ZLtcsProjectWeeklyServicesCard, ({
      ...provides(
        [ltcsProjectServiceMenuResolverStateKey, menusResolverStore.state],
        [ownExpenseProgramResolverStoreKey, ownExpenseProgramResolverStore]
      ),
      propsData: { program: project.programs[0] }
    }))
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
