/* eslint-disable import/no-duplicates,import/order */
/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare module '*.vue' {
  import Vue from 'vue'

  export default Vue
}

declare module 'vue-the-mask' {
  import { DirectiveFunction, DirectiveOptions } from 'vue'

  export const mask: DirectiveFunction | DirectiveOptions
}
