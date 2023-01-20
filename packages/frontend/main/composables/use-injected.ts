/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { inject, InjectionKey } from '@nuxtjs/composition-api'

export function useInjected<T> (key: InjectionKey<T>): T {
  const x = inject(key)
  if (!x) {
    throw new Error(`${String(key)} is not provided`)
  }
  return x
}
