<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import {
  ltcsInsCardStateKey,
  ltcsInsCardStoreKey,
  useLtcsInsCardStore
} from '~/composables/stores/use-ltcs-ins-card-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'LtcsInsCardStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.cardId),
  setup () {
    const { $route } = usePlugins()
    const store = useLtcsInsCardStore()
    provide(ltcsInsCardStoreKey, store)
    provide(ltcsInsCardStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.cardId,
      userId: +$route.params.id
    }))
  }
})
</script>
