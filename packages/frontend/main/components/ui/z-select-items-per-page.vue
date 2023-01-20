<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-select-items-per-page class="text-body-2" :class="$style.root">
    <span class="flex-grow-0 flex-shrink-0 pr-2">表示件数:</span>
    <z-select :items="items" :outlined="false" :value="currentValue" @change="onChange" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { selectOptions } from '~/composables/select-options'
import { ItemsPerPage, ItemsPerPageValuesStandard } from '~/models/items-per-page'

type Props = Readonly<{
  currentValue: ItemsPerPage
  optionValues: ItemsPerPage[]
}>

export default defineComponent<Props>({
  name: 'ZSelectItemsPerPage',
  props: {
    currentValue: {
      type: Number,
      required: true
    },
    optionValues: {
      type: Array,
      default: () => ItemsPerPageValuesStandard
    }
  },
  setup (props, context) {
    const items = selectOptions<ItemsPerPage>(
      props.optionValues.map(v => ({ text: `${v}`, value: v }))
    )
    return {
      items,
      onChange: (v: number) => context.emit('change', v)
    }
  }
})
</script>

<style lang="scss" module>
.root {
  align-items: center;
  display: flex;
  justify-content: flex-end;

  :global {
    .v-input {
      flex: 0 0 60px;

      input[id^="input-"] {
        width: 0;
      }
    }
  }
}
</style>
