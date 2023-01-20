/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import ZContractsCard from '~/components/domain/contract/z-contracts-card.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useOffices } from '~/composables/use-offices'
import { createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { createUseOfficesStub } from '~~/stubs/create-use-offices-stub'
import { createUserStub } from '~~/stubs/create-user-stub'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('~/composables/use-offices')

describe('z-contracts-card.vue', () => {
  const { mount } = setupComponentTest()
  const user = createUserStub()
  const items = createContractStubsForUser(user.id)
  const propsData = {
    items,
    options: {
      footerLink: `/users/${user.id}/dws-certifications/new?segment=${ServiceSegment.disabilitiesWelfare}`,
      title: '障害福祉サービス受給者証'
    },
    permission: Permission.listDwsContracts
  }

  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    mocked(useOffices).mockReturnValue(createUseOfficesStub())
    wrapper = mount(ZContractsCard, {
      ...provides(
        [sessionStoreKey, createAuthStub({ isSystemAdmin: true })]
      ),
      propsData
    })
  })

  afterAll(() => {
    wrapper.destroy()
    mocked(useOffices).mockReset()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
