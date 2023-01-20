/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MountOptions, Wrapper } from '@vue/test-utils'
import { noop } from '@zinger/helpers'
import Vue from 'vue'
import ZPromised from '~/components/util/z-promised.vue'
import { setProps } from '~~/test/helpers/set-props'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-promised.vue', () => {
  const { shallowMount } = setupComponentTest()

  let wrapper: Wrapper<Vue>

  async function mountComponent (promise: Promise<unknown>, options: MountOptions<Vue> = {}) {
    const scopedSlots = options.scopedSlots ?? {
      default: '<span data-slot-default>{{ props.data }}</span>',
      rejected: '<span data-slot-rejected>{{ props.error }}</span>',
      pending: '<span data-slot-pending>Loading...</span>'
    }
    wrapper = shallowMount(ZPromised, {
      ...options,
      propsData: {
        ...(options.propsData ?? {}),
        promise
      },
      scopedSlots
    })
    await wrapper.vm.$nextTick()
  }

  function unmountComponent (): void {
    wrapper.destroy()
  }

  it.each([
    ['when the promise is resolved', Promise.resolve('January'), {}],
    ['when the promise is rejected', Promise.reject(new Error('February')), {}],
    ['when the promise is pending', new Promise(noop), {}],
    ['when the promise is resolved and tag specified', Promise.resolve('March'), {
      propsData: { tag: 'h1' }
    }],
    ['when the promise is rejected and tag specified', Promise.reject(new Error('April')), {
      propsData: { tag: 'h1' }
    }],
    ['when the promise is pending and tag specified', new Promise(noop), {
      propsData: { tag: 'h1' }
    }],
    ['when the promise is rejected but rejected slot is not defined', Promise.reject(new Error('May')), {
      scopedSlots: {
        default: '<span data-slot-default>{{ props.data }}</span>'
      }
    }],
    ['when the promise is pending and pending slot is not defined', new Promise(noop), {
      scopedSlots: {
        default: '<span data-slot-default>{{ props.data }}</span>'
      }
    }]
  ])('should be rendered correctly %s', async (_, promise, options) => {
    await mountComponent(promise, options)
    expect(wrapper).toMatchSnapshot()
    unmountComponent()
  })

  it.each([
    [1, Promise.resolve('Before'), Promise.resolve('After')],
    [2, Promise.resolve('Before'), Promise.reject(new Error('After'))],
    [3, Promise.resolve('Before'), new Promise(noop)],
    [4, Promise.reject(new Error('Before')), Promise.resolve('After')],
    [5, Promise.reject(new Error('Before')), Promise.reject(new Error('After'))],
    [6, Promise.reject(new Error('Before')), new Promise(noop)],
    [7, new Promise(noop), Promise.resolve('After')],
    [8, new Promise(noop), Promise.reject(new Error('After'))]
  ])('should be rendered correctly when the promise changed: %d', async (_, promise, newPromise) => {
    await mountComponent(promise)
    await setProps(wrapper, { promise: newPromise })
    await wrapper.vm.$nextTick()

    expect(wrapper).toMatchSnapshot()

    unmountComponent()
  })
})
