/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import MapPage from '~/pages/map.vue'
import { Plugins } from '~/plugins'
import { createStaffStubs } from '~~/stubs/create-staff-stub'
import { createStaffsStoreStub } from '~~/stubs/create-staffs-store-stub'
import { mockedGooglePlugin } from '~~/test/helpers/mocked-google-plugin'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

jest.mock('@google/markerclusterer')

describe('pages/map.vue', () => {
  const { mount } = setupComponentTest()
  const mocks: Partial<Plugins> = {
    $google: mockedGooglePlugin()
  }
  createStaffsStoreStub({
    staffs: createStaffStubs(20)
  })

  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    wrapper = mount(MapPage, {
      mocks
    })
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })
})
