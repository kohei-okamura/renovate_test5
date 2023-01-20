/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Plugins } from '~/plugins'

declare module '@nuxt/types' {
  interface Context extends Plugins {
  }

  interface NuxtAppOptions extends Plugins {
  }
}

declare module 'vue/types/vue' {
  export interface Vue extends Plugins {
  }
}

declare global {
  interface Window {
    onNuxtReady (f: () => void): void
  }
}
