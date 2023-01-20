<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { settingStateKey, settingStoreKey, useSettingStore } from '~/composables/stores/use-setting-store'
import { useAsync } from '~/composables/use-async'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'SettingsPage',
  validate ({ app }: NuxtContext) {
    return app.$globalStore.session.state.isActive.value
  },
  setup () {
    const settingStore = useSettingStore()
    provide(settingStoreKey, settingStore)
    provide(settingStateKey, settingStore.state)
    return useAsync(() => settingStore.get())
  }
})
</script>
