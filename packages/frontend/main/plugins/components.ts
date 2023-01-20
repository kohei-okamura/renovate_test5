/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin } from '@nuxtjs/composition-api'
import Vue from 'vue'
import ZDataCardItem from '~/components/ui/z-data-card-item.vue'
import ZDataCard from '~/components/ui/z-data-card.vue'

/**
 * コンポーネントを利用可能とする.
 *
 * 一部のコンポーネントが VuetifyLoaderPlugin による動的ロードの対象とならないケースに対応する.
 */
export function setupComponents () {
  Vue.component('ZDataCard', ZDataCard)
  Vue.component('ZDataCardItem', ZDataCardItem)
}

/**
 * コンポーネントプラグイン.
 */
export default defineNuxtPlugin(() => {
  setupComponents()
})
