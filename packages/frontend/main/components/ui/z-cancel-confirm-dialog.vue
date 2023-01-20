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
        <validation-provider v-slot="{ errors }" data-reason tag="div" vid="reason" :rules="rules.reason">
          <z-textarea v-model.trim="reason" label="キャンセル理由 *" :error-messages="errors" />
        </validation-provider>
      </validation-observer>
    </template>
  </z-prompt-dialog>
</template>

<script lang="ts">
import { defineComponent, ref } from '@nuxtjs/composition-api'
import { observerRef } from '~/support/reactive'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = {
  active: boolean
  inProgress: boolean
  message?: string
}

export default defineComponent<Props>({
  name: 'ZCancelConfirmDialog',
  props: {
    active: { type: Boolean, default: false },
    inProgress: { type: Boolean, default: false },
    message: { type: String, default: 'キャンセルする場合は、キャンセル理由を入力して実行を押してください。' }
  },
  setup (props, context) {
    const observer = observerRef()
    const reason = ref('')
    const options = {
      message: props.message,
      positive: '実行',
      width: 600
    }
    const rules = validationRules({
      reason: { required }
    })
    const onClickNegative = (e: Event) => {
      e.stopPropagation()
      observer.value?.reset()
      context.emit('click:negative', false)
    }
    const onClickPositive = async (e: Event) => {
      e.stopPropagation()
      if (await observer.value?.validate()) {
        context.emit('click:positive', reason.value)
      }
    }
    return {
      observer,
      onClickNegative,
      onClickPositive,
      options,
      reason,
      rules
    }
  }
})
</script>
