<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-prompt-dialog
    :active="active"
    :in-progress="inProgress"
    :options="options"
    @click:negative="onClickNegative"
    @click:positive="onClickPositive"
  >
    <template #form>
      <validation-observer ref="observer" tag="div">
        <z-date-picker
          v-model="date"
          :max="range.max"
          :min="range.min"
          :multiple="multiple"
          :show-current="false"
          full-width
        />
        <z-validate-error-messages
          v-slot="{ errors }"
          class="mt-1"
          data-error
          vid="date"
          :rules="rules.date"
          :value="date"
        >
          <z-error-container class="error--text v-messages">
            <div>{{ errors[0] }}</div>
          </z-error-container>
        </z-validate-error-messages>
        <slot name="option"></slot>
      </validation-observer>
    </template>
  </z-prompt-dialog>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from '@nuxtjs/composition-api'
import { datetime } from '~/composables/datetime'
import { DateLike, ISO_DATETIME_FORMAT } from '~/models/date'
import { $datetime } from '~/services/datetime-service'
import { observerRef } from '~/support/reactive'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = {
  active: boolean
  inProgress: boolean
  max?: DateLike
  message?: string
  min?: DateLike
  multiple: boolean
  positiveLabel?: string
}

export default defineComponent<Props>({
  name: 'ZDateConfirmDialog',
  props: {
    active: { type: Boolean, default: false },
    inProgress: { type: Boolean, default: false },
    max: { type: [String, Object], default: undefined },
    message: { type: String, default: '日付を選択して実行を押してください。' },
    min: { type: [String, Object], default: undefined },
    multiple: { type: Boolean, default: false },
    positiveLabel: { type: String, default: '実行' }
  },
  setup (props, context) {
    const observer = observerRef()
    const date = ref<string | undefined>()
    const range = computed(() => {
      return {
        max: props.max ? $datetime.parse(props.max).toISODate() : undefined,
        min: props.min ? $datetime.parse(props.min).toISODate() : undefined
      }
    })
    const options = {
      message: props.message,
      positive: props.positiveLabel
    }
    const rules = validationRules({
      date: { required }
    })
    const clear = () => {
      date.value = undefined
      requestAnimationFrame(() => {
        observer.value?.reset()
      })
    }
    const onClickNegative = (e: Event) => {
      e.stopPropagation()
      clear()
      context.emit('click:negative', false)
    }
    const onClickPositive = (e: Event) => {
      e.stopPropagation()
      const value = date.value && datetime(date.value, ISO_DATETIME_FORMAT)
      observer.value?.handleSubmit(() => {
        context.emit('click:positive', value)
        clear()
      })
    }
    return {
      date,
      observer,
      onClickNegative,
      onClickPositive,
      options,
      range,
      rules
    }
  }
})
</script>
