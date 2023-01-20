<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { dwsCertificationStateKey, useDwsCertificationStore } from '~/composables/stores/use-dws-certification-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'DwsCertificationStoreProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.certificationId),
  setup () {
    const { $route } = usePlugins()
    const store = useDwsCertificationStore()
    provide(dwsCertificationStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.certificationId,
      userId: +$route.params.id
    }))
  }
})
</script>
