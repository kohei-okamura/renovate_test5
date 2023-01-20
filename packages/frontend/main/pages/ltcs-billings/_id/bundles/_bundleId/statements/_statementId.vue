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
  ltcsBillingStatementStateKey,
  ltcsBillingStatementStoreKey,
  useLtcsBillingStatementStore
} from '~/composables/stores/use-ltcs-billing-statement-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'LtcsBillingStatementStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.statementId),
  setup () {
    const { $route } = usePlugins()
    const store = useLtcsBillingStatementStore()
    provide(ltcsBillingStatementStoreKey, store)
    provide(ltcsBillingStatementStateKey, store.state)
    return useAsync(() => store.get({
      billingId: +$route.params.id,
      bundleId: +$route.params.bundleId,
      id: +$route.params.statementId
    }))
  }
})
</script>
