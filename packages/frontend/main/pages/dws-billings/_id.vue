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
import { dwsBillingStateKey, dwsBillingStoreKey, useDwsBillingStore } from '~/composables/stores/use-dws-billing-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsBillingStoreProviderPage',
  middleware: [auth(Permission.viewBillings)],
  validate: ({ params }: NuxtContext) => !isNaN(+params.id),
  setup () {
    const { $route } = usePlugins()
    const store = useDwsBillingStore()
    provide(dwsBillingStoreKey, store)
    provide(dwsBillingStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.id
    }))
  }
})
</script>
