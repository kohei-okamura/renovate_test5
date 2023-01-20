<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-data-card title="公費情報">
      <z-data-card-item label="適用期間" :icon="$icons.date" :value="ltcsSubsidy.id">
        <z-era-date :value="ltcsSubsidy.period.start" />
        <span>〜</span>
        <z-era-date :value="ltcsSubsidy.period.end" />
      </z-data-card-item>
      <z-data-card-item
        label="公費制度（法別番号）"
        :icon="$icons.defrayerCategory"
        :value="resolveDefrayerCategory(ltcsSubsidy.defrayerCategory)"
      />
      <z-data-card-item label="負担者番号" :icon="$icons.city" :value="ltcsSubsidy.defrayerNumber" />
      <z-data-card-item label="受給者番号" :icon="$icons.recipientNumber" :value="ltcsSubsidy.recipientNumber" />
      <z-data-card-item label="給付率" :icon="$icons.ratio" :value="ltcsSubsidy.benefitRate + '%'" />
      <z-data-card-item label="本人負担額" :icon="$icons.yen" :value="ltcsSubsidy.copay + '円'" />
    </z-data-card>
    <z-system-meta-card :id="ltcsSubsidy.id" :created-at="ltcsSubsidy.createdAt" :updated-at="ltcsSubsidy.updatedAt" />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateUserLtcsSubsidies, permissions.deleteUserLtcsSubsidies])"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.updateUserLtcsSubsidies])"
        nuxt
        :icon="$icons.edit"
        :to="`/users/${user.id}/ltcs-subsidies/${ltcsSubsidy.id}/edit`"
      >
        公費情報を編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.deleteUserLtcsSubsidies])"
        :icon="$icons.delete"
        @click="deleteSubsidy"
      >
        公費情報を削除
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { resolveDefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { ltcsSubsidyStateKey } from '~/composables/stores/use-ltcs-subsidy-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useDeleteUserDependant } from '~/composables/use-delete-user-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'LtcsSubsidyViewPage',
  middleware: [auth(Permission.viewUserLtcsSubsidies)],
  setup () {
    const { $api } = usePlugins()
    const { ltcsSubsidy } = useInjected(ltcsSubsidyStateKey)
    const { user } = useInjected(userStateKey)
    const { deleteUserDependant } = useDeleteUserDependant()
    const deleteSubsidy = deleteUserDependant({
      dependant: '公費情報',
      userId: user.value!.id,
      target: ltcsSubsidy,
      callback: params => $api.ltcsSubsidies.delete(params),
      hash: 'ltcs'
    })
    return {
      ...useAuth(),
      ...useBreadcrumbs('users.ltcsSubsidies.view', user),
      resolveDefrayerCategory,
      user,
      ltcsSubsidy,
      deleteSubsidy
    }
  },
  head: () => ({
    title: '利用者介護保険サービス公費情報詳細'
  })
})
</script>
