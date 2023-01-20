/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  createLocalVue,
  mount,
  shallowMount,
  ThisTypedMountOptions,
  ThisTypedShallowMountOptions,
  Wrapper
} from '@vue/test-utils'
import Vue, { VueConstructor } from 'vue'
import Vuetify from 'vuetify'
import { setupVeeValidate } from '~/support/validation'
import nuxt from '~~/nuxt.config'
import { setupVue } from '~~/test/helpers/setup-vue'
import toHaveBeenWarned from '~~/test/helpers/to-have-been-warned'

function useNuxtEnv (): void {
  Object.assign(process.env, nuxt.env)
}

function mockRequestAnimationFrame () {
  window.requestAnimationFrame = (f: any) => f()
}

function mockIntersectionObserver () {
  global.IntersectionObserver = jest.fn().mockImplementation(() => ({
    observe: () => jest.fn(),
    unobserve: () => jest.fn(),
    disconnect: () => jest.fn()
  }))
}

function setupLocalVue (): VueConstructor {
  const vue = createLocalVue()
  vue.config.async = false
  setupVeeValidate(vue)
  return vue
}

type MountComponentOptions<T extends Vue> = ThisTypedMountOptions<T> | (() => ThisTypedMountOptions<T>)

type MountComponent = <V extends Vue> (component: any, options?: MountComponentOptions<V>) => Wrapper<V>

export const mountComponent: MountComponent = (component, f = {}) => {
  const localVue = setupLocalVue()
  const options = typeof f === 'function' ? f() : f
  const vuetify = new Vuetify()
  return mount(component, {
    ...options,
    localVue,
    vuetify
  })
}

type ShallowMountComponentOptions<T extends Vue> =
  ThisTypedShallowMountOptions<T> | (() => ThisTypedShallowMountOptions<T>)

type ShallowMountComponent = <V extends Vue> (component: any, options?: ShallowMountComponentOptions<V>) => Wrapper<V>

const shallowMountComponent: ShallowMountComponent = (component, f = {}) => {
  const localVue = setupLocalVue()
  const options = typeof f === 'function' ? f() : f
  const vuetify = new Vuetify()
  return shallowMount(component, {
    ...options,
    localVue,
    vuetify
  })
}

type ComponentTestUtils = {
  mount: MountComponent
  shallowMount: ShallowMountComponent
}

export function setupComponentTest (): ComponentTestUtils {
  setupVue()
  toHaveBeenWarned.init()
  beforeAll(() => {
    jest.useFakeTimers({
      // 2018-05-17 00:00:00 JST
      now: new Date(2018, 4, 17, 9, 0, 0, 0)
    })
    useNuxtEnv()
    mockRequestAnimationFrame()
    mockIntersectionObserver()
  })
  afterEach(() => {
    jest.clearAllTimers()
  })
  return {
    mount: mountComponent,
    shallowMount: shallowMountComponent
  }
}
