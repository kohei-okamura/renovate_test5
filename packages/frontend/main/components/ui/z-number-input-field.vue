<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-text-field
    v-model="localValue"
    class="z-text-field--numeric"
    data-text-field
    v-bind="attrs"
    v-on="listeners"
  />
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { omit } from '@zinger/helpers'
import { toNarrowAlphanumeric } from 'jaco'

type Props = Readonly<{
  value: number | string
}>

export default defineComponent<Props>({
  name: 'ZNumberInputField',
  props: {
    value: { type: [Number, String], default: '' }
  },
  setup (props, context) {
    const correctValue = (value: string) => {
      const halfNumValue = parseInt(toNarrowAlphanumeric(value))
      return isNaN(halfNumValue) ? value : halfNumValue
    }
    const localValue = computed({
      get: () => (props.value ?? '') + '',
      set: (value: string) => context.emit('input', correctValue(value))
    })
    const attrs = computed(() => omit(context.attrs, ['value']))
    const listeners = computed(() => omit(context.listeners, ['input']))
    return {
      attrs,
      listeners,
      localValue
    }
  }
})
</script>
