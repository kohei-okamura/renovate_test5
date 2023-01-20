/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Vue, { Component, VueConstructor } from 'vue'
import { DefaultProps, PropsDefinition } from 'vue/types/options'
import { ExtendedVue } from 'vue/types/vue'

type ExtendVuetifyComponent = {
  (component: Component, name: string, props?: PropsDefinition<DefaultProps>):
    ExtendedVue<Vue, unknown, unknown, unknown, unknown>
}

export const extendVuetifyComponent: ExtendVuetifyComponent = (component, name, props = {}) => {
  return (component as VueConstructor).extend({ name, props })
}
