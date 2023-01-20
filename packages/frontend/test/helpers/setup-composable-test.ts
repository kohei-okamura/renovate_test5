/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineComponent } from '@nuxtjs/composition-api'
import VueCompositionAPI from '@vue/composition-api'
import { createLocalVue, mount, MountOptions } from '@vue/test-utils'
import { isFunction, noop } from '@zinger/helpers'
import Vue from 'vue'

const setupComponent = (template: string = '<div></div>') => defineComponent({
  setup: noop,
  template
})

type SetupComposableTestOptions = Partial<MountOptions<Vue>> | (() => Partial<MountOptions<Vue>>)

export const setupComposableTest = (options: SetupComposableTestOptions = {}) => {
  const localVue = createLocalVue()
  localVue.use(VueCompositionAPI)
  const { template, ...mountOptions } = (isFunction(options) ? options() : options)
  return mount(setupComponent(template), {
    ...mountOptions,
    localVue
  })
}
