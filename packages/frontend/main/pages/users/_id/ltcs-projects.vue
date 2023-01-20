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
  ltcsProjectServiceMenuResolverStateKey,
  useLtcsProjectServiceMenuResolverStore
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { useAsync } from '~/composables/use-async'

export default defineComponent({
  name: 'LtcsProjectStoreProviderPage',
  setup () {
    const store = useLtcsProjectServiceMenuResolverStore()
    provide(ltcsProjectServiceMenuResolverStateKey, store.state)
    return useAsync(() => store.update())
  }
})
</script>
