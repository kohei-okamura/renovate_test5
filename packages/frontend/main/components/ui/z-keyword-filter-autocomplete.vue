<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-autocomplete
    v-bind="$attrs"
    :filter="filter"
    :items="items"
    v-on="$listeners"
  />
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { toKatakana } from '~/support/jaco'

type Item = {
  keyword: string
  text: string
  value: number | string | undefined
}

type Props = Readonly<{
  items: Item[]
}>

export default defineComponent<Props>({
  name: 'ZKeywordFilterAutocomplete',
  props: {
    items: { type: Array, required: true }
  },
  setup () {
    const filter = (item: Item, queryText: string) => {
      return item.keyword.includes(toKatakana(queryText))
    }
    return {
      filter
    }
  }
})
</script>
