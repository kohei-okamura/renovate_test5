/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive, Ref, toRefs } from '@nuxtjs/composition-api'

export type DrawerService = {
  readonly isOpened: Ref<boolean>
  set (opened: boolean): void
}

export function createDrawerService (): DrawerService {
  const data = reactive({
    isOpened: false
  })
  return {
    ...toRefs(data),
    set (opened): void {
      data.isOpened = opened
    }
  }
}
