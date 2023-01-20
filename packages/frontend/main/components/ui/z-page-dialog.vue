<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog v-model="isActive" transition="dialog" :persistent="persistent || progress" :width="width">
    <slot></slot>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, onMounted } from '@nuxtjs/composition-api'
import { useSyncedProp } from '~/composables/use-synced-prop'

type Props = Readonly<{
  persistent: boolean
  progress: boolean
  value: boolean
  width: number
}>

export default defineComponent<Props>({
  name: 'ZPageDialog',
  props: {
    persistent: { type: Boolean, default: false },
    progress: { type: Boolean, default: false },
    value: { type: Boolean, default: false },
    width: { type: Number, default: 500 }
  },
  setup (props, context) {
    const isActive = useSyncedProp('value', props, context, 'input')
    const open = () => {
      isActive.value = true
    }
    onMounted(() => {
      setTimeout(open, 100)
    })
    return {
      isActive,
      open
    }
  }
})
</script>
