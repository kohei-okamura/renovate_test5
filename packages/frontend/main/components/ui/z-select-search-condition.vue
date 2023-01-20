<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-select
    v-bind="$attrs"
    :clearable="clearable"
    :items="options"
    v-on="$listeners"
    @click:clear="onClickClear"
  />
</template>

<script lang="ts">
import { computed, defineComponent, nextTick } from '@nuxtjs/composition-api'
import { VSelectOption } from '~/models/vuetify'

type Props = Readonly<{
  items: VSelectOption[]
  unselectedText?: string
}>

export default defineComponent<Props>({
  name: 'ZSelectSearchCondition',
  props: {
    items: { type: Array, required: true },
    unselectedText: { type: String, default: '指定しない' }
  },
  setup (props, context) {
    const clearable = computed(() => context.attrs.value !== '')
    const onClickClear = () => {
      nextTick(() => { context.emit('input', '') })
    }
    const options = computed(() => [
      { text: props.unselectedText, value: '' },
      ...props.items
    ])
    return {
      clearable,
      onClickClear,
      options
    }
  }
})
</script>
