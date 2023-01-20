<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog persistent="persistent" :width="options.width || 400" :value="active">
    <v-card>
      <v-card-title v-if="options.title">{{ options.title }}</v-card-title>
      <v-card-text>
        <p v-if="options.message" :class="$style.message">{{ options.message }}</p>
        <slot name="form"></slot>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          v-if="cancelable"
          color="grey"
          data-button-negative
          text
          :disabled="inProgress"
          @click="$emit('click:negative', $event)"
        >
          <span>{{ options.negative || 'キャンセル' }}</span>
        </v-btn>
        <v-btn
          data-button-positive
          text
          type="submit"
          :color="options.color || 'primary'"
          :disabled="inProgress"
          @click="$emit('click:positive', $event)"
        >
          <span>{{ options.positive || 'OK' }}</span>
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'

type Props = {
  active: boolean
  cancelable: boolean
  inProgress: boolean
  options: {
    color?: string
    message?: string
    negative?: string
    positive?: string
    title?: string
    width?: number
  }
}

export default defineComponent<Props>({
  name: 'ZPromptDialog',
  props: {
    active: { type: Boolean, default: false },
    cancelable: { type: Boolean, default: true },
    inProgress: { type: Boolean, default: false },
    options: { type: Object, default: () => ({}) }
  }
})
</script>

<style lang="scss" module>
.message {
  margin-bottom: 12px !important;
  white-space: pre-wrap;
}
</style>
