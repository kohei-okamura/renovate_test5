<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-combobox
    v-model="syncedSelected"
    v-bind="$attrs"
    :background-color="bgColor"
    append-icon=""
    flat
    hide-no-data
    hide-selected
    multiple
    outlined
    small-chips
  >
    <template #selection="{ attrs, item, parent, selected }">
      <v-chip
        v-bind="attrs"
        :input-value="selected"
        label
        small
      >
        <span class="pr-2">{{ item }}</span>
        <v-icon right small @click="parent.selectItem(item)">{{ $icons.close }}</v-icon>
      </v-chip>
    </template>
  </v-combobox>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { colors } from '~/colors'
import { useSyncedProp } from '~/composables/use-synced-prop'

type Props = Readonly<{
  value: string[]
}>

export default defineComponent<Props>({
  name: 'ZMultipleEntryField',
  props: {
    value: { type: Array, default: () => [] }
  },
  setup (props, context) {
    const syncedSelected = useSyncedProp('value', props, context, 'input')
    return {
      bgColor: colors.textField.background,
      syncedSelected
    }
  }
})
</script>

<style lang="scss" module>

</style>
