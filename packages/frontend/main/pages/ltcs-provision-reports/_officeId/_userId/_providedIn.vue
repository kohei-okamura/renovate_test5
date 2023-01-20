<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <nuxt-child v-if="isResolved" />
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { DateTime } from 'luxon'
import {
  ltcsProvisionReportStateKey,
  ltcsProvisionReportStoreKey,
  useLtcsProvisionReportStore
} from '~/composables/stores/use-ltcs-provision-report-store'
import {
  ltcsProvisionReportsStateKey,
  ltcsProvisionReportsStoreKey,
  useLtcsProvisionReportsStore
} from '~/composables/stores/use-ltcs-provision-reports-store'
import { userStateKey, useUserStore } from '~/composables/stores/use-user-store'
import { useAsync } from '~/composables/use-async'
import { usePlugins } from '~/composables/use-plugins'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { NuxtContext } from '~/models/nuxt'

export default defineComponent({
  name: 'LtcsProvisionReportStoreProviderPage',
  validate: ({ params }: NuxtContext) => {
    const thisMonth = DateTime.fromISO(DateTime.local().toFormat(ISO_MONTH_FORMAT))
    const providedIn = DateTime.fromISO(params.providedIn)
    return !isNaN(+params.officeId) &&
      !isNaN(+params.userId) &&
      providedIn.isValid && providedIn.diff(thisMonth, 'months').toObject().months! <= 3
  },
  setup () {
    const { $route } = usePlugins()
    const reportsStore = useLtcsProvisionReportsStore()
    provide(ltcsProvisionReportsStoreKey, reportsStore)
    provide(ltcsProvisionReportsStateKey, reportsStore.state)
    const userStore = useUserStore()
    provide(userStateKey, userStore.state)
    const reportStore = useLtcsProvisionReportStore()
    provide(ltcsProvisionReportStoreKey, reportStore)
    provide(ltcsProvisionReportStateKey, reportStore.state)
    return useAsync(() => {
      const officeId = +$route.params.officeId
      const userId = +$route.params.userId
      const providedIn = $route.params.providedIn
      return Promise.all([
        userStore.get({ id: userId }),
        reportsStore.getIndex({ officeId, providedIn }),
        reportStore.get({ officeId, userId, providedIn })
      ])
    })
  }
})
</script>
