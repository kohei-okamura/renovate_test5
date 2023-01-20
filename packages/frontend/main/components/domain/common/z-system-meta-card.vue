<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-card title="システム情報">
    <z-data-card-item label="ID" :icon="$icons.id" :value="id" />
    <z-data-card-item v-if="createdAt" label="登録日時" :icon="$icons.date">
      <z-datetime :value="createdAt" />
    </z-data-card-item>
    <z-data-card-item v-if="updatedAt" label="更新日時" :icon="createdAt ? undefined : $icons.date">
      <z-datetime :value="updatedAt" />
    </z-data-card-item>
  </z-data-card>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'

export default defineComponent({
  name: 'ZSystemMetaCard',
  props: {
    id: { type: Number, required: true },
    createdAt: { type: String, default: undefined },
    updatedAt: { type: String, default: undefined }
  }
})
</script>
