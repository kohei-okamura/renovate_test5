<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <v-alert
      v-if="!hasBankingClientCode"
      data-no-banking-client-code-alert
      type="warning"
    >
      <span>委託者番号が登録されていません。</span>
      <span v-if="isAuthorized([permissions.createOrganizationSettings])">
        <nuxt-link to="/settings/new">こちらから登録してください</nuxt-link>。
      </span>
    </v-alert>
    <z-data-card title="事業者別設定">
      <z-data-card-item label="委託者番号" :icon="$icons.id" :value="organizationSetting.bankingClientCode || '-'" />
    </z-data-card>
    <z-fab
      v-if="isAuthorized([permissions.updateOrganizationSettings]) && hasBankingClientCode"
      bottom
      data-fab
      fixed
      nuxt
      right
      to="/settings/edit"
      :icon="$icons.edit"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty } from '@zinger/helpers'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { settingStateKey } from '~/composables/stores/use-setting-store'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'SettingsIndexPage',
  middleware: [auth(Permission.viewOrganizationSettings)],
  setup () {
    const { organizationSetting } = useInjected(settingStateKey)
    const { isAuthorized, permissions } = useAuth()
    return {
      ...useBreadcrumbs('setting.index'),
      isAuthorized,
      permissions,
      hasBankingClientCode: computed(() => !isEmpty(organizationSetting.value?.bankingClientCode)),
      organizationSetting
    }
  },
  head: () => ({
    title: '事業者別設定情報'
  })
})
</script>
