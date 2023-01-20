/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Ref } from '@nuxtjs/composition-api'

export async function withProgressState<T> (progressState: Ref<boolean>, f: () => Promise<T>): Promise<T> {
  try {
    progressState.value = true
    return await f()
  } finally {
    progressState.value = false
  }
}
