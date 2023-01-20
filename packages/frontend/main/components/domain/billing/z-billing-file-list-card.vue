<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table :items="items" :options="tableOptions">
    <template #item.name="{ item }">{{ item.name }}</template>
    <template #item.type="{ item }">
      <v-icon>{{ fileIcons[item.mimeType] }}</v-icon>
      <span class="ml-1 file-type">{{ fileTypes[item.mimeType] }}</span>
    </template>
    <template #item.createdAt="{ item }">
      <z-datetime :value="item.createdAt" />
    </template>
    <template #item.download="{ item }">
      <v-btn color="secondary" icon @click="$emit('click:download', item)">
        <v-icon>{{ $icons.download }}</v-icon>
      </v-btn>
    </template>
  </z-data-table>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs } from '@nuxtjs/composition-api'
import { MimeType } from '@zinger/enums/lib/mime-type'
import { appendHeadersCommonProperty, dataTableOptions, TableHeaders } from '~/composables/data-table-options'
import { BillingFile } from '~/models/billing-file'
import { $icons } from '~/plugins/icons'

type Props = {
  downloadable: boolean
  items: BillingFile[]
}

export default defineComponent<Props>({
  name: 'ZBillingFileListCard',
  props: {
    downloadable: { type: Boolean, default: false },
    items: { type: Array, required: true }
  },
  setup (props: Props) {
    const reactiveProps = toRefs(props)

    const fileIcons = {
      [MimeType.csv]: $icons.csv,
      [MimeType.pdf]: $icons.pdf
    }
    const fileTypes = {
      [MimeType.csv]: 'CSV',
      [MimeType.pdf]: 'PDF'
    }

    const defaultHeaders: TableHeaders = [
      { text: '種類', value: 'name' },
      { text: 'ファイル形式', value: 'type', width: 120 },
      { text: '作成日時', value: 'createdAt', width: 190 }
    ]
    const tableOptions = computed(() => dataTableOptions({
      content: 'ファイル',
      headers: appendHeadersCommonProperty(
        reactiveProps.downloadable.value
          ? [
            ...defaultHeaders,
            { text: 'ダウンロード', value: 'download', align: 'center', width: 120 }]
          : defaultHeaders,
        { align: 'start' }
      ),
      title: 'ファイル'
    }))

    return {
      fileIcons,
      fileTypes,
      tableOptions
    }
  }
})
</script>
