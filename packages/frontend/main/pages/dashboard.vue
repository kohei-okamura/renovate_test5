<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page class="page-dashboard-index" compact :breadcrumbs="breadcrumbs">
    <z-my-shifts-card v-if="isAuthorized([permissions.listShifts])" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { useAuth } from '~/composables/use-auth'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'DashboardPage',
  middleware: [auth()],
  setup () {
    return {
      ...useAuth(),
      ...useBreadcrumbs('dashboard')
    }
  },
  head: () => ({
    title: 'ダッシュボード'
  })
})
</script>
