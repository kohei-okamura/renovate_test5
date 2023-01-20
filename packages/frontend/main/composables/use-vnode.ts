/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { getCurrentInstance } from '@nuxtjs/composition-api'
import { assert } from '@zinger/helpers/index'
import { VNode } from 'vue'

export function useVnode (): VNode {
  const vm = getCurrentInstance()
  assert(vm !== null, 'vm is null')
  return vm.proxy.$vnode
}
