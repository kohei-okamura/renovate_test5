/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { getCurrentInstance } from '@nuxtjs/composition-api'
import Vue, { ComponentOptions } from 'vue'

type Hook = keyof ComponentOptions<Vue>

export const createLifeCycleHook = <T extends Hook> (hook: T) => (callback: NonNullable<ComponentOptions<Vue>[T]>) => {
  const vm = getCurrentInstance()
  if (vm === null) {
    const hookName = `on${hook[0].toUpperCase()}${hook.slice(1)}`
    console.error(`${hookName} is called when there is no active component instance to be associated with.`)
  } else {
    const options = vm.proxy.$options
    const merge = Vue.config.optionMergeStrategies[hook]
    Object.assign(options, {
      [hook]: merge(options[hook], callback)
    })
  }
}
