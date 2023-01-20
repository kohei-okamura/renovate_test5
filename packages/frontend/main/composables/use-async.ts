/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, toRefs } from '@nuxtjs/composition-api'
import Vue from 'vue'

type State<T> = {
  isRejected: boolean
  isResolved: boolean
  rejectedValue: any | undefined
  resolvedValue: T | undefined
}

export function useAsync<T> (f: () => Promise<T>) {
  const data = reactive<State<T>>({
    isRejected: false,
    isResolved: false,
    rejectedValue: undefined as any | undefined,
    resolvedValue: undefined as T | undefined
  })
  f().then(x => {
    Vue.set(data, 'resolvedValue', x)
    Vue.set(data, 'isResolved', true)
  }).catch(x => {
    Vue.set(data, 'rejectedValue', x)
    Vue.set(data, 'isRejected', true)
  })
  return toRefs(data)
}
