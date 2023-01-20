/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { nextTick, Ref } from '@nuxtjs/composition-api'
import { ValidationObserverInstance } from '~/support/validation/types'

type UseAsyncValidate = {
  (observerRef: Ref<ValidationObserverInstance | undefined>): () => Promise<void>
}

export const useAsyncValidate: UseAsyncValidate = observerRef => {
  return async () => {
    await nextTick()
    await observerRef.value?.validate()
  }
}
