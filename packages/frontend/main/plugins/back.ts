/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin } from '@nuxtjs/composition-api'
import VueRouter, { Route } from 'vue-router'

export type Back = {
  (fallback?: string): Promise<Route>
}

/**
 * ブラウザバックプラグイン.
 */
export default defineNuxtPlugin((context, inject) => {
  const back: Back = (fallback = '/') => {
    const router: VueRouter = context.app.router!
    return window.history.length === 1 ? router.replace(fallback) : new Promise<Route>(() => router.back())
  }
  inject('back', back)
})
