<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-nuxt-child :resolved="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import {
  dwsBillingStatementStateKey,
  dwsBillingStatementStoreKey,
  useDwsBillingStatementStore
} from '~/composables/stores/use-dws-billing-statement-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsBillingStatementStoreProviderPage',
  middleware: [auth(Permission.viewBillings)],
  validate: ({ params }: NuxtContext) => {
    return !isNaN(+params.id) &&
      !isNaN(+params.bundleId) &&
      !isNaN(+params.statementId)
  },
  setup () {
    const { $route } = usePlugins()
    const store = useDwsBillingStatementStore()
    provide(dwsBillingStatementStoreKey, store)
    provide(dwsBillingStatementStateKey, store.state)
    return useAsync(() => store.get({
      billingId: +$route.params.id,
      bundleId: +$route.params.bundleId,
      id: +$route.params.statementId
    }))
  }
})
</script>
