<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-data-card data-data-card title="自治体助成情報">
      <z-data-card-item label="適用期間" :icon="$icons.date" :value="dwsSubsidy.id">
        <z-era-date :value="dwsSubsidy.period.start" />
        <span>〜</span>
        <z-era-date :value="dwsSubsidy.period.end" />
      </z-data-card-item>
      <z-data-card-item label="助成自治体名" :icon="$icons.city" :value="dwsSubsidy.cityName" />
      <z-data-card-item label="助成自治体番号" :value="dwsSubsidy.cityCode" />
      <z-data-card-item
        label="給付方式"
        :icon="$icons.category"
        :value="resolveUserDwsSubsidyType(dwsSubsidy.subsidyType)"
      />
      <template v-if="dwsSubsidy.subsidyType === UserDwsSubsidyType.benefitRate">
        <z-data-card-item
          label="給付率"
          :icon="$icons.ratio"
          :value="dwsSubsidy.benefitRate + '%'"
        >
          {{ resolveUserDwsSubsidyFactor(dwsSubsidy.factor) }}の{{ dwsSubsidy.benefitRate }}%を自治体が負担する
        </z-data-card-item>
        <z-data-card-item label="端数処理" :value="resolveRounding(dwsSubsidy.rounding)" />
      </template>
      <template v-else-if="dwsSubsidy.subsidyType === UserDwsSubsidyType.copayRate">
        <z-data-card-item label="負担率" :icon="$icons.ratio">
          利用者負担額を{{ resolveUserDwsSubsidyFactor(dwsSubsidy.factor) }}の{{ dwsSubsidy.copayRate }}%に軽減する
        </z-data-card-item>
        <z-data-card-item label="端数処理" :value="resolveRounding(dwsSubsidy.rounding)" />
      </template>
      <z-data-card-item
        v-else-if="dwsSubsidy.subsidyType === UserDwsSubsidyType.benefitAmount"
        label="給付額"
        :icon="$icons.yen"
        :value="numeral(dwsSubsidy.benefitAmount) + '円'"
      />
      <z-data-card-item
        v-else-if="dwsSubsidy.subsidyType === UserDwsSubsidyType.copayAmount"
        label="本人負担額"
        :icon="$icons.yen"
        :value="numeral(dwsSubsidy.copayAmount) + '円'"
      />
      <z-data-card-item label="備考" :icon="$icons.text" :value="dwsSubsidy.note" />
    </z-data-card>
    <z-system-meta-card
      :id="dwsSubsidy.id"
      :created-at="dwsSubsidy.createdAt"
      :updated-at="dwsSubsidy.updatedAt"
    />
    <z-fab
      v-if="isAuthorized([permissions.updateUserDwsSubsidies])"
      bottom
      data-fab
      fixed
      nuxt
      right
      :icon="$icons.edit"
      :to="`/users/${user.id}/dws-subsidies/${dwsSubsidy.id}/edit`"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveRounding } from '@zinger/enums/lib/rounding'
import { resolveUserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { resolveUserDwsSubsidyType, UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { numeral } from '~/composables/numeral'
import { dwsSubsidyStateKey } from '~/composables/stores/use-dws-subsidy-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'DwsSubsidyViewPage',
  middleware: [auth(Permission.viewUserDwsSubsidies)],
  setup () {
    const { dwsSubsidy } = useInjected(dwsSubsidyStateKey)
    const { user } = useInjected(userStateKey)
    return {
      ...useAuth(),
      ...useBreadcrumbs('users.dwsSubsidies.view', user),
      ...useOffices({ permission: Permission.viewUserDwsSubsidies }),
      dwsSubsidy,
      numeral,
      resolveRounding,
      resolveUserDwsSubsidyFactor,
      resolveUserDwsSubsidyType,
      user,
      UserDwsSubsidyType
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス自治体助成情報詳細'
  })
})
</script>
