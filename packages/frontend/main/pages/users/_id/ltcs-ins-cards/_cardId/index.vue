<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-data-card title="基本情報">
      <z-data-card-item label="適用日" :icon="$icons.date">
        <z-era-date :value="ltcsInsCard.effectivatedOn" />
      </z-data-card-item>
      <z-data-card-item label="認定区分" :icon="statusIcon" :value="resolveLtcsInsCardStatus(ltcsInsCard.status)" />
      <z-data-card-item label="被保険者証番号" :icon="$icons.ltcsInsNumber" :value="ltcsInsCard.insNumber" />
      <z-data-card-item label="交付年月日" :icon="$icons.issuedOn">
        <z-era-date :value="ltcsInsCard.issuedOn" />
      </z-data-card-item>
      <z-data-card-item label="保険者の名称" :icon="$icons.city" :value="ltcsInsCard.insurerName" />
      <z-data-card-item label="保険者番号" :value="ltcsInsCard.insurerNumber" />
      <z-data-card-item
        label="要介護状態区分等"
        :icon="$icons.level"
        :value="resolveLtcsLevel(ltcsInsCard.ltcsLevel)"
      />
      <z-data-card-item label="認定年月日" :icon="$icons.issuedOn">
        <z-era-date :value="ltcsInsCard.certificatedOn" />
      </z-data-card-item>
      <z-data-card-item label="認定の有効期間" :icon="$icons.dateRange">
        <z-era-date :value="ltcsInsCard.activatedOn" />
        <span>〜</span>
        <z-era-date :value="ltcsInsCard.deactivatedOn" />
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="居宅サービス計画">
      <z-data-card-item
        label="居宅サービス計画作成区分"
        :icon="$icons.carePlanAuthor"
        :value="resolveLtcsCarePlanAuthorType(ltcsInsCard.carePlanAuthorType)"
      />
      <z-data-card-item
        v-if="isComprehensiveService"
        label="地域包括支援センター"
        :value="resolveOfficeAbbr(ltcsInsCard.communityGeneralSupportCenterId)"
      />
      <template v-if="ltcsInsCard.carePlanAuthorType !== LtcsCarePlanAuthorType.self">
        <z-data-card-item label="居宅介護支援事業所" :value="resolveOfficeAbbr(ltcsInsCard.carePlanAuthorOfficeId)" />
        <z-data-card-item label="担当者の名称" :value="ltcsInsCard.careManagerName" />
      </template>
    </z-data-card>
    <z-data-table :items="ltcsInsCard.maxBenefitQuotas" :options="quotaTableOptions">
      <template #item.ltcsInsCardServiceType="{ item }">
        {{ resolveLtcsInsCardServiceType(item.ltcsInsCardServiceType) }}
      </template>
      <template #item.maxBenefitQuota="{ item }">1月あたり {{ numeral(item.maxBenefitQuota) }} 単位</template>
    </z-data-table>
    <z-data-card title="介護保険負担割合証情報">
      <z-data-card-item label="利用者負担の割合" :icon="$icons.ratio">
        {{ ltcsInsCard.copayRate / 10 }}割
      </z-data-card-item>
      <z-data-card-item label="利用者負担適用期間" :icon="$icons.dateRange">
        <z-era-date :value="ltcsInsCard.copayActivatedOn" />
        <span>〜</span>
        <z-era-date :value="ltcsInsCard.copayDeactivatedOn" />
      </z-data-card-item>
    </z-data-card>
    <z-system-meta-card :id="ltcsInsCard.id" :created-at="ltcsInsCard.createdAt" :updated-at="ltcsInsCard.updatedAt" />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateLtcsInsCards, permissions.deleteLtcsInsCards])"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.updateLtcsInsCards])"
        nuxt
        :icon="$icons.editVariant"
        :to="`/users/${user.id}/ltcs-ins-cards/${ltcsInsCard.id}/edit`"
      >
        被保険者証を編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.deleteLtcsInsCards])"
        :icon="$icons.delete"
        @click="deleteLtcsInsCard"
      >
        被保険者証を削除
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { LtcsCarePlanAuthorType, resolveLtcsCarePlanAuthorType } from '@zinger/enums/lib/ltcs-care-plan-author-type'
import { resolveLtcsInsCardServiceType } from '@zinger/enums/lib/ltcs-ins-card-service-type'
import { resolveLtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsLevel, resolveLtcsLevel } from '@zinger/enums/lib/ltcs-level'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dataTableOptions } from '~/composables/data-table-options'
import { numeral } from '~/composables/numeral'
import { ltcsInsCardStateKey } from '~/composables/stores/use-ltcs-ins-card-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useDeleteUserDependant } from '~/composables/use-delete-user-dependant'
import { useInjected } from '~/composables/use-injected'
import { useLtcsInsCardStatusIcon } from '~/composables/use-ltcs-ins-card-status-icon'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { LtcsInsCardMaxBenefitQuota } from '~/models/ltcs-ins-card-max-benefit-quota'

export default defineComponent({
  name: 'LtcsInsCardsViewPage',
  middleware: [auth(Permission.viewLtcsInsCards)],
  setup () {
    const { $api } = usePlugins()
    const { ltcsInsCard } = useInjected(ltcsInsCardStateKey)
    const { user } = useInjected(userStateKey)
    const quotaTableOptions = dataTableOptions<LtcsInsCardMaxBenefitQuota>({
      content: '種類支給限度基準額',
      headers: [
        {
          text: 'サービスの種類',
          value: 'ltcsInsCardServiceType',
          class: 'th-ltcs-ins-card-service-type',
          align: 'start',
          sortable: false
        },
        {
          text: '種類支給限度基準額',
          value: 'maxBenefitQuota',
          class: 'th-max-benefit-quota',
          align: 'start',
          sortable: false
        }
      ],
      title: '種類支給限度基準額'
    })
    const { deleteUserDependant } = useDeleteUserDependant()
    const deleteLtcsInsCard = deleteUserDependant({
      dependant: '被保険者証情報',
      userId: user.value!.id,
      target: ltcsInsCard,
      callback: params => $api.ltcsInsCards.delete(params),
      hash: 'ltcs'
    })
    const isComprehensiveService = [
      LtcsLevel.target,
      LtcsLevel.supportLevel1,
      LtcsLevel.supportLevel2
    ].includes(ltcsInsCard.value?.ltcsLevel as any)
    return {
      ...useAuth(),
      ...useBreadcrumbs('users.ltcsInsCards.view', user),
      ...useLtcsInsCardStatusIcon(ltcsInsCard),
      ...useOffices({ permission: Permission.viewLtcsInsCards }),
      LtcsCarePlanAuthorType,
      isComprehensiveService,
      ltcsInsCard,
      numeral,
      resolveLtcsCarePlanAuthorType,
      resolveLtcsInsCardServiceType,
      resolveLtcsInsCardStatus,
      resolveLtcsLevel,
      quotaTableOptions,
      user,
      deleteLtcsInsCard
    }
  },
  head: () => ({
    title: '利用者介護保険被保険者証詳細'
  })
})
</script>
