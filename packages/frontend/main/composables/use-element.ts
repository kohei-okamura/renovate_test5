/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { getCurrentInstance } from '@nuxtjs/composition-api'
import { assert } from '@zinger/helpers'

export function useElement (): Lazy<Element> {
  const vm = getCurrentInstance()
  assert(vm !== null, 'vm is null')
  return () => vm.proxy.$el
}
