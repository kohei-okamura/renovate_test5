<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div class="z-hour-and-minute-field" v-bind="$attrs">
    <z-flex :class="$style.slot">
      <div :class="$style.item">
        <z-text-field
          v-model="hours"
          class="z-text-field--numeric"
          data-hour-text-field
          hide-details
          suffix="時間"
          :disabled="disabled"
          :error="hasErrors"
          :label="label"
          :readonly="readonly"
          @blur="onBlur"
        />
      </div>
      <div :class="$style.item">
        <z-text-field
          v-model="minutes"
          class="z-text-field--numeric"
          data-minute-text-field
          hide-details
          max-length="2"
          suffix="分"
          :disabled="disabled"
          :error="hasErrors"
          :readonly="readonly"
          @blur="onBlur"
        />
      </div>
    </z-flex>
    <div :class="$style.details">
      <v-messages v-if="hasErrors" color="error" :value="errorMessages" />
    </div>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { toNarrowAlphanumeric } from 'jaco'
import { TimeDuration } from '~/models/time-duration'

type Props = Readonly<{
  disabled: boolean
  errorMessages: string[]
  label: string
  readonly: boolean
  value: number | string | TimeDuration
}>

export default defineComponent<Props>({
  name: 'ZHourAndMinuteField',
  props: {
    disabled: { type: Boolean, default: false },
    errorMessages: { type: Array, default: () => [] },
    label: { type: String, default: '' },
    readonly: { type: Boolean, default: false },
    value: { type: [Number, String, Object], default: '' }
  },
  setup (props, context) {
    const duration = computed(() => TimeDuration.from(props.value).orUndefined ?? TimeDuration.zero())
    const hasErrors = computed(() => props.errorMessages.length > 0)
    const correctValue = (value: string) => {
      const halfNumValue = parseInt(toNarrowAlphanumeric(value))
      return isNaN(halfNumValue) ? value : halfNumValue
    }
    const hours = computed({
      get: () => (duration.value?.hours ?? '') + '',
      set: (value: string) => {
        context.emit('input', duration.value?.withHours(correctValue(value)))
      }
    })
    const minutes = computed({
      get: () => (duration.value?.minutes ?? '') + '',
      set: (value: string) => {
        context.emit('input', duration.value?.withMinutes(correctValue(value)))
      }
    })
    const onBlur = (event: Event) => context.emit('blur', event)
    return {
      hasErrors,
      hours,
      minutes,
      onBlur
    }
  }
})
</script>

<style lang="scss" module>
.slot {
  margin: 8px 0 4px;
}

.details {
  margin-bottom: 8px;
  padding: 0 12px;
}

.item {
  flex-grow: 1;
  width: 100%;
}

.item + .item {
  margin-left: 4px;
}
</style>
