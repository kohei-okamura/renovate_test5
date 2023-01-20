<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { userDwsCalcSpecStateKey, useUserDwsCalcSpecStore } from '~/composables/stores/use-user-dws-calc-spec-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'UserDwsCalcSpecStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.calcSpecId),
  setup () {
    const { $route } = usePlugins()
    const store = useUserDwsCalcSpecStore()
    provide(userDwsCalcSpecStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.calcSpecId,
      userId: +$route.params.id
    }))
  }
})
</script>
