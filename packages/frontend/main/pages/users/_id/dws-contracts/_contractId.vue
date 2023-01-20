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
  dwsContractStateKey,
  dwsContractStoreKey,
  useDwsContractStore
} from '~/composables/stores/use-dws-contract-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsContractStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.contractId),
  setup () {
    const { $route } = usePlugins()
    const store = useDwsContractStore()
    provide(dwsContractStateKey, store.state)
    provide(dwsContractStoreKey, store)
    return useAsync(() => store.get({
      id: +$route.params.contractId,
      userId: +$route.params.id
    }))
  }
})
</script>
