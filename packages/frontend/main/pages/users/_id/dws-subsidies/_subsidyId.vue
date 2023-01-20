<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { dwsSubsidyStateKey, dwsSubsidyStoreKey, useDwsSubsidyStore } from '~/composables/stores/use-dws-subsidy-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsSubsidyStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.id) && !isNaN(+params.subsidyId),
  setup () {
    const { $route } = usePlugins()
    const store = useDwsSubsidyStore()
    provide(dwsSubsidyStoreKey, store)
    provide(dwsSubsidyStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.subsidyId,
      userId: +$route.params.id
    }))
  }
})
</script>
