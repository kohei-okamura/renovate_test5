/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import { HttpStatusCode } from '~/models/http-status-code'
import StaffVerificationPage from '~/pages/staff-verifications/_token.vue'
import { createAxiosError } from '~~/test/helpers/create-axios-error'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedRoute } from '~~/test/helpers/create-mocked-route'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('pages/staff-verifications/_token.vue', () => {
  const { mount } = setupComponentTest()
  const token = 'x'.repeat(60)
  const params = { token }
  const $route = createMockedRoute({ params })
  const $api = createMockedApi('staffs')
  const mocks = {
    $api,
    $route
  }
  let wrapper: Wrapper<Vue & any>

  async function mountComponent () {
    wrapper = mount(StaffVerificationPage, {
      mocks
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  beforeEach(() => {
    jest.spyOn($api.staffs, 'verify').mockResolvedValue(undefined)
  })

  afterEach(() => {
    jest.clearAllMocks()
  })

  it('should display message when server responses 2xx', async () => {
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it('should display message when server responses 403 Forbidden', async () => {
    jest.spyOn($api.staffs, 'verify').mockRejectedValue(createAxiosError(HttpStatusCode.Forbidden))
    await mountComponent()
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  describe('validate', () => {
    const context: { params: Dictionary } = {
      params: { ...params }
    }

    beforeAll(async () => {
      await mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should success when valid token given', () => {
      const result = wrapper.vm.$options.validate!(context)
      expect(result).toBeTrue()
    })

    it('should success when valid token given', () => {
      context.params.token = 'x'.repeat(59)
      expect(wrapper.vm.$options.validate!(context)).toBeFalse()

      context.params.token = 'x'.repeat(61)
      expect(wrapper.vm.$options.validate!(context)).toBeFalse()

      context.params.token = '-'.repeat(60)
      expect(wrapper.vm.$options.validate!(context)).toBeFalse()
    })
  })
})
