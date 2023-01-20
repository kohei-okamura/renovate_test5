<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import {
  dwsBillingServiceReportStateKey,
  dwsBillingServiceReportStoreKey,
  useDwsBillingServiceReportStore
} from '~/composables/stores/use-dws-billing-service-report-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsBillingReportStoreProviderPage',
  validate: ({ params }: NuxtContext) => {
    return !isNaN(+params.id) &&
        !isNaN(+params.bundleId) &&
        !isNaN(+params.reportId)
  },
  setup () {
    const { $route } = usePlugins()
    const store = useDwsBillingServiceReportStore()
    provide(dwsBillingServiceReportStoreKey, store)
    provide(dwsBillingServiceReportStateKey, store.state)
    return useAsync(() => store.get({
      billingId: +$route.params.id,
      bundleId: +$route.params.bundleId,
      id: +$route.params.reportId
    }))
  }
})
</script>
