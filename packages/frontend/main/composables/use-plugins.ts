/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { getCurrentInstance } from '@nuxtjs/composition-api'
import { assert } from '@zinger/helpers'
import Vue from 'vue'
import { Plugins } from '~/plugins'

type UsePlugins = () => Plugins & {
  $route: Vue['$route']
  $router: Vue['$router']
  $vuetify: Vue['$vuetify']
}

export const usePlugins: UsePlugins = () => {
  const vm = getCurrentInstance()
  assert(vm !== null, 'There is no current instance')
  return vm.proxy
}
