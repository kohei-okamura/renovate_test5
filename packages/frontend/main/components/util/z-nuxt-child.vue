<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="resolved" />
  <z-progress
    v-else
    ref="progress"
    :value="true"
    color="primary"
    message="読み込み中..."
    opacity="0.1"
  />
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'

type Props = {
  resolved: boolean
}

export default defineComponent<Props>({
  name: 'ZNuxtChild',
  props: {
    resolved: { type: Boolean, default: false }
  }
})
</script>
