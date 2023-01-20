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
  ownExpenseProgramStateKey,
  ownExpenseProgramStoreKey,
  useOwnExpenseProgramStore
} from '~/composables/stores/use-own-expense-program-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'OwnExpenseProgramStoreProviderPage',
  middleware: [auth(Permission.viewOwnExpensePrograms)],
  validate: ({ params }: NuxtContext) => !isNaN(+params.id),
  setup () {
    const { $route } = usePlugins()
    const store = useOwnExpenseProgramStore()
    provide(ownExpenseProgramStoreKey, store)
    provide(ownExpenseProgramStateKey, store.state)
    return useAsync(() => store.get({ id: +$route.params.id }))
  }
})
</script>
