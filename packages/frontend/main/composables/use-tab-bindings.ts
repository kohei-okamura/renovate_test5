/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref, Ref } from '@nuxtjs/composition-api'

export function useTabBindings () {
  const tab: Ref<string | number | undefined> = ref()
  return { tab }
}
