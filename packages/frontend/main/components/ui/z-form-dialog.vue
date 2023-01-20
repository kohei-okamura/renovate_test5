<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page-dialog v-model="isActive">
    <v-form data-form @submit.prevent="submit">
      <v-card>
        <z-card-titlebar color="blue-grey">{{ title }}</z-card-titlebar>
        <slot></slot>
      </v-card>
    </v-form>
  </z-page-dialog>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { useSyncedProp } from '~/composables/use-synced-prop'

type Props = Readonly<{
  title: string
  value: boolean
}>

export default defineComponent<Props>({
  name: 'ZFormDialog',
  props: {
    title: { type: String, default: '' },
    value: { type: Boolean, default: false }
  },
  setup (props, context) {
    return {
      isActive: useSyncedProp('value', props, context, 'input'),
      submit: () => context.emit('submit')
    }
  }
})
</script>
