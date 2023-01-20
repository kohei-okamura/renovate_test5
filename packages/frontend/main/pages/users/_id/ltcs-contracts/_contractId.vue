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
  ltcsContractStateKey,
  ltcsContractStoreKey,
  useLtcsContractStore
} from '~/composables/stores/use-ltcs-contract-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'LtcsContractStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.contractId),
  setup () {
    const { $route } = usePlugins()
    const store = useLtcsContractStore()
    provide(ltcsContractStoreKey, store)
    provide(ltcsContractStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.contractId,
      userId: +$route.params.id
    }))
  }
})
</script>
