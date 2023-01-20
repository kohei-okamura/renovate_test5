/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import ErrorLayout from '~/layouts/error.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('layouts/error.vue', () => {
  const { mount } = setupComponentTest()
  const message = 'お探しのページは見つかりませんでした。'
  let wrapper: Wrapper<Vue>

  beforeAll(() => {
    const propsData = {
      error: { statusCode: 404, message }
    }
    wrapper = mount(ErrorLayout, { propsData })
  })

  afterAll(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered correctly when the status code is a 404 error', () => {
    const text = wrapper.text()
    expect(text).toContain('Not Found')
    expect(text).toContain(message)
  })

  it('should be rendered correctly when the status code is a non-404 error', () => {
    const message = 'ただいまアクセスしづらくなっております。'
    const propsData = {
      error: { statusCode: 503, message }
    }
    const localWrapper = mount(ErrorLayout, { propsData })
    const text = localWrapper.text()
    expect(text).toContain('エラーが発生しました、管理者へのお問い合わせをお願いします。')
    expect(text).toContain(message)
  })
})
