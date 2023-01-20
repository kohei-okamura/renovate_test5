<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-overlay class="text-center" :opacity="opacity" :value="value" :z-index="zIndex">
    <v-progress-circular ref="progress" indeterminate size="64" :color="color" />
    <div data-z-progress-message v-bind="labelProps">{{ message }}</div>
  </v-overlay>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import deepmerge from 'deepmerge'
import { createVuetifyStyleColorProps } from '~/composables/create-vuetify-style-color-style'

type Props = {
  color?: string
  message?: string
  opacity?: string
  value: boolean
  zIndex?: number
}

export default defineComponent<Props>({
  name: 'ZProgress',
  props: {
    color: { type: String, default: undefined },
    message: { type: String, default: '処理中です。しばらくお待ちください。' },
    opacity: { type: String, default: '0.8' },
    value: { type: Boolean, required: true },
    /*
     * v-navigation-drawer の stackMinZIndex が 6 なので、デフォルト値をひとつ大きい値にしています
     * @see vuetify/src/components/VNavigationDrawer/VNavigationDrawer.ts
     */
    zIndex: { type: Number, default: 7 }
  },
  setup (props) {
    const makeLabelColorProps = (color?: string) => deepmerge(
      createVuetifyStyleColorProps(color),
      { class: { 'mt-4': true, 'subtitle-1': true } }
    )
    return {
      labelProps: computed(() => makeLabelColorProps(props.color))
    }
  }
})
</script>
