/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import VueCompositionAPI from '@vue/composition-api'
import { config, RouterLinkStub } from '@vue/test-utils'
import Vue from 'vue'
import Vuetify from 'vuetify'
import { setupComponents } from '~/components'
import { mocks } from '~~/test/setup/mocks'
import { stubs } from '~~/test/setup/stubs'

export function setupVue () {
  config.mocks = mocks
  config.stubs = stubs
  Vue.use(Vuetify)
  Vue.use(VueCompositionAPI)
  Vue.component('NuxtLink', RouterLinkStub)
  setupComponents()
}
