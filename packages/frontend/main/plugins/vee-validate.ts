/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineNuxtPlugin } from '@nuxtjs/composition-api'
import Vue from 'vue'
import { setupVeeValidate } from '~/support/validation'

/**
 * VeeValidate プラグイン.
 */
export default defineNuxtPlugin(() => {
  setupVeeValidate(Vue)
})
