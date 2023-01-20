<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import {
  dwsBillingCopayCoordinationStateKey,
  dwsBillingCopayCoordinationStoreKey,
  useDwsBillingCopayCoordinationStore
} from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsBillingCopayCoordinationStoreProviderPage',
  middleware: [auth(Permission.viewBillings)],
  validate: ({ params }: NuxtContext) => {
    return !isNaN(+params.id) &&
      !isNaN(+params.bundleId) &&
      !isNaN(+params.coordinationId)
  },
  setup () {
    const { $route } = usePlugins()
    const store = useDwsBillingCopayCoordinationStore()
    provide(dwsBillingCopayCoordinationStateKey, store.state)
    provide(dwsBillingCopayCoordinationStoreKey, store)
    return useAsync(() => store.get({
      billingId: +$route.params.id,
      bundleId: +$route.params.bundleId,
      id: +$route.params.coordinationId
    }))
  }
})
</script>
