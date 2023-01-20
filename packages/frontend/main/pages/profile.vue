<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { sessionStateKey } from '~/composables/stores/use-session-store'
import { staffStateKey, staffStoreKey, useStaffStore } from '~/composables/stores/use-staff-store'
import { useAsync } from '~/composables/use-async'
import { useInjected } from '~/composables/use-injected'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'SettingsPage',
  validate ({ app }: NuxtContext) {
    return app.$globalStore.session.state.isActive.value
  },
  setup () {
    const { auth } = useInjected(sessionStateKey)
    const staffStore = useStaffStore()
    const id = auth.value!.staff.id
    provide(staffStoreKey, staffStore)
    provide(staffStateKey, staffStore.state)
    return useAsync(() => staffStore.get({ id }))
  }
})
</script>
