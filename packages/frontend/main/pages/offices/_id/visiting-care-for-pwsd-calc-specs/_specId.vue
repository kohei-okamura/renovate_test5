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
  useVisitingCareForPwsdCalcSpecStore,
  visitingCareForPwsdCalcSpecStateKey
} from '~/composables/stores/use-visiting-care-for-pwsd-calc-spec-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'VisitingCareForPwsdCalcSpecProviderPage',
  validate: ({ params }: NuxtContext) => !isNaN(+params.specId),
  setup () {
    const { $route } = usePlugins()
    const store = useVisitingCareForPwsdCalcSpecStore()
    provide(visitingCareForPwsdCalcSpecStateKey, store.state)
    return useAsync(() => store.get({
      id: +$route.params.specId,
      officeId: +$route.params.id
    }))
  }
})
</script>
