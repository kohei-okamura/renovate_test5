/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZProjectsCard from '~/components/domain/project/z-projects-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { createDwsProjectStubs } from '~~/stubs/create-dws-project-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUseStaffsStub } from '~~/stubs/create-use-staffs-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')
jest.mock('~/composables/use-staffs')

describe('z-projects-card.vue', () => {
  const { mount } = setupComponentTest()
  const user = createUserStub()
  const contracts = createContractStubsForUser(user.id)
  const items = createDwsProjectStubs(contracts)

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = mount(ZProjectsCard, {
      ...provides(
        [sessionStoreKey, createAuthStub({ isSystemAdmin: true })]
      ),
      propsData: {
        contracts,
        items,
        options: {
          content: '計画',
          title: '計画一覧'
        },
        permission: Permission.listDwsProjects,
        user
      }
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    mocked(useStaffs).mockReturnValue(createUseStaffsStub())
  })

  afterAll(() => {
    mocked(useStaffs).mockReset()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })
})
