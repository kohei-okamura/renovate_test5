<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-fab-speed-dial :class="$style.root">
    <v-overlay v-model="data.isActive" z-index="5" color="white" data-z-fab-speed-dial-overlay opacity="0.8" />
    <v-speed-dial
      v-model="data.isActive"
      bottom
      :class="$style.fab"
      direction="top"
      fixed
      right
      transition="slide-y-reverse-transition"
    >
      <template #activator>
        <z-fab data-z-fab-speed-dial-fab :icon="fabIcon" />
      </template>
      <template #default>
        <slot></slot>
      </template>
    </v-speed-dial>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, reactive } from '@nuxtjs/composition-api'

type Props = Readonly<{
  icon: string
}>

export default defineComponent<Props>({
  name: 'ZFabSpeedDial',
  props: {
    icon: { type: String, required: true }
  },
  setup (props, { root }) {
    const data = reactive({
      isActive: false
    })
    return {
      window,
      data,
      fabIcon: computed(() => data.isActive ? root.$icons.close : props.icon)
    }
  }
})
</script>

<style lang="scss" module>
// .v-speed-dial--fixed の定義に勝つために詳細度を上げる
.root {
  .fab {
    z-index: 5;
  }
}
</style>
