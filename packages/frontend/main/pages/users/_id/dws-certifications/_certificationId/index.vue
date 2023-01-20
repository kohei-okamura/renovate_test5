<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-data-card title="児童情報">
      <z-data-card-item label="氏名" :icon="$icons.user" :value="dwsCertification.child.name.displayName || '-'" />
      <z-data-card-item label="氏名：フリガナ" :value="dwsCertification.child.name.phoneticDisplayName || '-'" />
      <z-data-card-item label="生年月日" :icon="$icons.birthday">
        <z-era-date :value="dwsCertification.child.birthday" />
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="基本情報">
      <z-data-card-item label="適用日" :icon="$icons.date">
        <z-era-date :value="dwsCertification.effectivatedOn" />
      </z-data-card-item>
      <z-data-card-item
        label="認定区分"
        :icon="statusIcon"
        :value="resolveDwsCertificationStatus(dwsCertification.status)"
      />
      <z-data-card-item label="受給者証番号" :icon="$icons.dwsNumber" :value="dwsCertification.dwsNumber" />
      <z-data-card-item label="障害種別" :icon="$icons.category">
        <div v-if="dwsCertification.dwsTypes.length === 0">-</div>
        <template v-else>
          <v-chip v-for="x in dwsCertification.dwsTypes" :key="x" label small>{{ resolveDwsType(x) }}</v-chip>
        </template>
      </z-data-card-item>
      <z-data-card-item label="交付年月日" :icon="$icons.issuedOn">
        <z-era-date :value="dwsCertification.issuedOn" />
      </z-data-card-item>
      <z-data-card-item label="市区町村名" :icon="$icons.city" :value="dwsCertification.cityName" />
      <z-data-card-item label="市区町村番号" :value="dwsCertification.cityCode" />
    </z-data-card>
    <z-data-card title="介護給付費の支給決定内容">
      <z-data-card-item label="障害支援区分" :icon="$icons.level">
        {{ resolveDwsLevel(dwsCertification.dwsLevel) }}
        {{ dwsCertification.isSubjectOfComprehensiveSupport ? '（重度障害者等包括支援対象）' : '' }}
      </z-data-card-item>
      <z-data-card-item label="認定有効期間" :icon="$icons.dateRange">
        <z-era-date :value="dwsCertification.activatedOn" />
        <span>〜</span>
        <z-era-date :value="dwsCertification.deactivatedOn" />
      </z-data-card-item>
    </z-data-card>
    <z-data-card v-for="(grant, i) in dwsCertification.grants" :key="i" class="mt-1">
      <z-data-card-item
        label="サービス種別"
        :icon="$icons.category"
        :value="resolveDwsCertificationServiceType(grant.dwsCertificationServiceType)"
      />
      <z-data-card-item label="支給量等" :icon="$icons.text" :value="grant.grantedAmount" />
      <z-data-card-item label="支給決定期間" :icon="$icons.dateRange">
        <z-era-date :value="grant.activatedOn" />
        <span>〜</span>
        <z-era-date :value="grant.deactivatedOn" />
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="利用者負担に関する事項">
      <z-data-card-item label="負担上限月額" :icon="$icons.copayLimit">
        {{ numeral(dwsCertification.copayLimit) }}円
      </z-data-card-item>
      <z-data-card-item label="利用者負担適用期間" :icon="$icons.dateRange">
        <z-era-date :value="dwsCertification.copayActivatedOn" />
        <span>〜</span>
        <z-era-date :value="dwsCertification.copayDeactivatedOn" />
      </z-data-card-item>
      <z-data-card-item label="上限管理区分" :icon="$icons.level">
        {{ resolveCopayCoordinationType(dwsCertification.copayCoordination.copayCoordinationType) }}
      </z-data-card-item>
      <z-data-card-item v-if="isCopayCoordinationOfficeRequired" label="上限額管理事業所名" :icon="$icons.office">
        {{ resolveOfficeAbbr(dwsCertification.copayCoordination.officeId) }}
      </z-data-card-item>
    </z-data-card>
    <z-subheader class="mt-4">訪問系サービス事業者記入欄</z-subheader>
    <v-container class="pa-0">
      <v-row dense>
        <template v-for="(agreement, i) in dwsCertification.agreements">
          <v-col :key="i" cols="12" sm="6">
            <z-data-card class="ma-0">
              <z-data-card-item label="番号" :icon="$icons.number" :value="agreement.indexNumber" />
              <z-data-card-item label="事業所" :icon="$icons.office" :value="resolveOfficeAbbr(agreement.officeId)" />
              <z-data-card-item
                label="サービス内容"
                :icon="$icons.category"
                :value="agreementType(agreement.dwsCertificationAgreementType)"
              />
              <z-data-card-item label="契約支給量" :icon="$icons.timeAmount">
                <span>{{ convertMinutesToHours(agreement.paymentAmount) }}時間/月</span>
              </z-data-card-item>
              <z-data-card-item label="契約日" :icon="$icons.dateStart">
                <z-era-date :value="agreement.agreedOn" />
              </z-data-card-item>
              <z-data-card-item label="当該契約支給量によるサービス提供終了日" :icon="$icons.dateEnd">
                <z-era-date :value="agreement.expiredOn" />
              </z-data-card-item>
            </z-data-card>
          </v-col>
        </template>
      </v-row>
    </v-container>
    <z-system-meta-card
      :id="dwsCertification.id"
      :created-at="dwsCertification.createdAt"
      :updated-at="dwsCertification.updatedAt"
    />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateDwsCertifications, permissions.deleteDwsCertifications])"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.updateDwsCertifications])"
        nuxt
        :icon="$icons.editVariant"
        :to="`/users/${user.id}/dws-certifications/${dwsCertification.id}/edit`"
      >
        受給者証情報を編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.deleteDwsCertifications])"
        :icon="$icons.delete"
        @click="deleteDwsCertification"
      >
        受給者証情報を削除
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { CopayCoordinationType, resolveCopayCoordinationType } from '@zinger/enums/lib/copay-coordination-type'
import { resolveDwsCertificationAgreementType } from '@zinger/enums/lib/dws-certification-agreement-type'
import { resolveDwsCertificationServiceType } from '@zinger/enums/lib/dws-certification-service-type'
import { resolveDwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { resolveDwsLevel } from '@zinger/enums/lib/dws-level'
import { resolveDwsType } from '@zinger/enums/lib/dws-type'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { numeral } from '~/composables/numeral'
import { dwsCertificationStateKey } from '~/composables/stores/use-dws-certification-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useDeleteUserDependant } from '~/composables/use-delete-user-dependant'
import { useDwsCertificationStatusIcon } from '~/composables/use-dws-certification-status-icon'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'DwsCertificationsViewPage',
  middleware: [auth(Permission.viewDwsCertifications)],
  setup () {
    const { $api } = usePlugins()
    const { dwsCertification } = useInjected(dwsCertificationStateKey)
    const copayCoordinationType = computed(() => dwsCertification.value?.copayCoordination.copayCoordinationType)
    const isCopayCoordinationOfficeRequired = computed(() => (
      copayCoordinationType.value === CopayCoordinationType.internal ||
      copayCoordinationType.value === CopayCoordinationType.external
    ))
    const { user } = useInjected(userStateKey)
    const { deleteUserDependant } = useDeleteUserDependant()
    const deleteDwsCertification = deleteUserDependant({
      dependant: '受給者証情報',
      userId: user.value!.id,
      target: dwsCertification,
      callback: params => $api.dwsCertifications.delete(params),
      hash: 'dws'
    })
    const convertMinutesToHours = (minutes: number) =>
      numeral(Math.floor(minutes / 60)) + String(Math.round(minutes % 60 / 60 * 10) / 10).slice(1)

    return {
      ...useAuth(),
      ...useBreadcrumbs('users.dwsCertifications.view', user),
      ...useDwsCertificationStatusIcon(dwsCertification),
      ...useOffices({ permission: Permission.listDwsCertifications }),
      agreementType: resolveDwsCertificationAgreementType,
      convertMinutesToHours,
      dwsCertification,
      isCopayCoordinationOfficeRequired,
      numeral,
      resolveCopayCoordinationType,
      resolveDwsCertificationStatus,
      resolveDwsLevel,
      resolveDwsType,
      resolveDwsCertificationServiceType,
      user,
      deleteDwsCertification
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス受給者証詳細'
  })
})
</script>
