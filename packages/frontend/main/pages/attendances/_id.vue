<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { attendanceStateKey, attendanceStoreKey, useAttendanceStore } from '~/composables/stores/use-attendance-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'AttendanceStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.id),
  setup () {
    const { $route } = usePlugins()
    const store = useAttendanceStore()
    provide(attendanceStoreKey, store)
    provide(attendanceStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.id
    }))
  }
})
</script>
