<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-fab-speed-dial-button :class="$style.root" :style="style">
    <v-tooltip
      v-model="tooltip"
      dark
      left
      transition="slide-x-reverse-transition"
      :color="color"
      :open-on-hover="false"
    >
      <template #activator="{ on }">
        <span v-on="on"></span>
      </template>
      <template #default>
        <slot></slot>
      </template>
    </v-tooltip>
    <v-btn dark data-button fab="fab" small :color="color" v-bind="$attrs" @click="onClick">
      <v-icon>{{ icon }}</v-icon>
    </v-btn>
  </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, toRefs } from '@nuxtjs/composition-api'
import { assert, wait } from '@zinger/helpers'
import { useVnode } from '~/composables/use-vnode'

type Props = Readonly<{
  color: string
  icon: string
}>

export default defineComponent<Props>({
  inheritAttrs: false,
  props: {
    color: { type: String, default: 'secondary' },
    icon: { type: String, required: true }
  },
  setup (_, context) {
    const $vnode = useVnode()
    const data = reactive({
      style: {} as Partial<CSSStyleDeclaration>,
      tooltip: false
    })
    const onClick = (event: MouseEvent) => context.emit('click', event)
    onMounted(async () => {
      assert(typeof $vnode.key !== 'symbol', 'key should be a number')
      const delay = (+($vnode.key ?? 0) + 1) * 50
      data.style = {
        transitionDelay: delay + 'ms'
      }
      await wait(delay + 250)
      data.tooltip = true
    })
    return {
      ...toRefs(data),
      onClick
    }
  }
})
</script>

<style lang="scss" module>
.root {
  display: flex;
  flex-flow: row nowrap;
}
</style>
