<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <validation-provider ref="provider" v-slot="{ errors, invalid }" tag="div" :rules="rules" v-bind="$attrs">
    <input type="hidden" :value="value" @input="$emit('input', $event.target.value)">
    <slot v-if="invalid && !!errors[0]" :errors="errors">
      <span class="error--text v-messages">{{ errors[0] }}</span>
    </slot>
  </validation-provider>
</template>

<script lang="ts">
import { defineComponent, nextTick, toRefs, watch } from '@nuxtjs/composition-api'
import { componentRef } from '~/support/reactive'
import { Rules, ValidationProviderInstance } from '~/support/validation/types'

type Props = {
  rules: Rules
  value: any
}

export default defineComponent<Props>({
  props: {
    rules: { type: Object, required: true },
    value: { required: true, validator: () => true }
  },
  setup (props: Props) {
    const propRefs = toRefs(props)
    const provider = componentRef<ValidationProviderInstance>()
    watch(
      propRefs.value,
      async () => {
        await nextTick()
        await provider.value?.validate()
      },
      { deep: true }
    )
    return {
      provider
    }
  }
})
</script>
