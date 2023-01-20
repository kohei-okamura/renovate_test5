<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { ltcsSubsidyStateKey, useLtcsSubsidyStore } from '~/composables/stores/use-ltcs-subsidy-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'LtcsSubsidyStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.ltcsSubsidyId),
  setup () {
    const { $route } = usePlugins()
    const store = useLtcsSubsidyStore()
    provide(ltcsSubsidyStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.ltcsSubsidyId,
      userId: +$route.params.id
    }))
  }
})
</script>
