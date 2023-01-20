/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin, useFetch } from '@nuxtjs/composition-api'
import { setupBeforeRouteLeaveCompat } from '~/composables/vue-router-compat'

type FetchCallback = () => void | Promise<void>
export type UseFetch = (callback: FetchCallback) => Pick<ReturnType<typeof useFetch>, 'fetch' | 'fetchState'>

export default defineNuxtPlugin((_, inject) => {
  inject('useFetch', useFetch)
  setupBeforeRouteLeaveCompat()
})
