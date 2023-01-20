<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-badge
    class="mx-2"
    color="primary"
    overlap
    :content="numberOfNotices"
    :offset-x="16"
    :offset-y="16"
    :value="hasNotification"
  >
    <v-btn data-button icon small :disabled="!hasNotification" @click="onClick">
      <v-icon color="white" dark>{{ $icons.notification }}</v-icon>
    </v-btn>
  </v-badge>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'

type Props = {
  numberOfNotices: number
}

export default defineComponent<Props>({
  name: 'ZNotificationIcon',
  props: {
    numberOfNotices: {
      type: Number,
      default: 0
    }
  },
  setup (props, context) {
    return {
      hasNotification: computed(() => props.numberOfNotices >= 1),
      onClick: (event: MouseEvent) => context.emit('click', event)
    }
  }
})
</script>
