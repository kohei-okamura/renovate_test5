<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { dwsProjectStateKey, dwsProjectStoreKey, useDwsProjectStore } from '~/composables/stores/use-dws-project-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsProjectStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.projectId),
  setup () {
    const { $route } = usePlugins()
    const store = useDwsProjectStore()
    provide(dwsProjectStoreKey, store)
    provide(dwsProjectStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.projectId,
      userId: +$route.params.id
    }))
  }
})
</script>
