<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { userStateKey, userStoreKey, useUserStore } from '~/composables/stores/use-user-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'UserStoreProviderPage',
  middleware: [auth(Permission.viewUsers)],
  validate: ({ params }: NuxtContext) => !isNaN(+params.id),
  setup () {
    const { $route } = usePlugins()
    const store = useUserStore()
    provide(userStoreKey, store)
    provide(userStateKey, store.state)
    return useAsync(() => store.get({ id: +$route.params.id }))
  }
})
</script>
