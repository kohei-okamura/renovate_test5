/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import Vue from 'vue'
import { NavigationGuard, NavigationGuardNext } from 'vue-router'
import { Route } from 'vue-router/types/router'
import { createLifeCycleHook } from '~/composables/create-life-cycle-hook'

export const onBeforeRouteLeave = createLifeCycleHook('beforeRouteLeaveCompat')

/**
 * `onBeforeRouteLeave` を使えるようにするためのセットアップ処理.
 */
export const setupBeforeRouteLeaveCompat = () => {
  type BeforeRouteLeaveCompat = {
    (guards: NavigationGuard[], to: Route, from: Route, next: NavigationGuardNext): void
  }
  const beforeRouteLeaveCompat: BeforeRouteLeaveCompat = ([head, ...tail], to, from, next) => {
    try {
      head(to, from, nextTo => {
        nextTo !== undefined || tail.length <= 0 ? next(nextTo) : beforeRouteLeaveCompat(tail, to, from, next)
      })
    } catch (error) {
      next(error as Error)
    }
  }
  Vue.mixin({
    beforeRouteLeave (this: Vue, to, from, next) {
      const hooks = this.$options.beforeRouteLeaveCompat ?? []
      assert(Array.isArray(hooks), 'type guard: options.beforeRouteLeaveCompat is always an array when referenced')
      hooks.length <= 0 ? next() : beforeRouteLeaveCompat(hooks, to, from, next)
    }
  })
  Vue.config.optionMergeStrategies['beforeRouteLeaveCompat'] = Vue.config.optionMergeStrategies['beforeRouteLeave']
}
