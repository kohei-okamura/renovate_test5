/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Vue from 'vue'
import { NavigationGuard } from 'vue-router'

/**
 * 参考： {@link https://github.com/vuejs/composition-api/issues/226#issuecomment-713695389}
 * vue.d.tsに記述するとエラーになるためこちらに記述しています。
 */
declare module '@vue/composition-api' {
  interface SetupContext {
    readonly refs: { [key: string]: Vue | Element | Vue[] | Element[] }
  }
}

declare module 'vue/types/options' {
  interface ComponentOptions<V extends Vue> {
    // Composition API で無理矢理 beforeRouteLeave フックを実現するための仕組み
    beforeRouteLeaveCompat?: NavigationGuard<V> | NavigationGuard<V>[]
  }
}
