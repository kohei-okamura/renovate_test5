<template>
  <z-page :breadcrumbs="breadcrumbs">
    <z-data-card title="基本情報">
      <z-data-card-item label="サービス提供年月" :icon="$icons.date">
        <z-era-date :value="bundle.providedIn" />
      </z-data-card-item>
      <z-data-card-item label="受給者証番号" :icon="$icons.dwsNumber" :value="report.user.dwsNumber" />
      <z-data-card-item label="支給決定障害者等氏名" :icon="$icons.user" :value="report.user.name.displayName" />
      <z-data-card-item label="支給決定に係る障害児氏名" :value="report.user.childName.displayName" />
      <z-data-card-item label="事業者及びその事業所の名称" :icon="$icons.office" :value="office.name" />
      <z-data-card-item label="事業所番号" :value="office.code" />
      <z-data-card-item label="サービス提供実績記録票の状態" :icon="statusIcon" :value="resolveDwsBillingStatus(report.status)" />
      <v-card-actions v-if="canUpdateStatus" data-report-status-btn>
        <v-spacer />
        <v-btn v-if="isReady" color="primary" text :loading="progress" @click="determine">
          サービス提供実績記録票を確定する
        </v-btn>
        <v-btn v-else-if="isFixed" color="primary" text :loading="progress" @click="remand">
          サービス提供実績記録票を未確定にする
        </v-btn>
      </v-card-actions>
    </z-data-card>
    <z-data-card :class="$style.reportTitle" data-service-report :title="serviceReportTitle">
      <z-service-report-format-one v-if="isHomeHelpService" ref="formatOne" :report="report" />
      <z-service-report-format-three-one v-else-if="isVisitingCareForPwsd" ref="formatThreeOne" :report="report" />
      <div v-else>{{ report.format }}</div>
    </z-data-card>
    <z-system-meta-card
      :id="report.id"
      :created-at="report.createdAt"
      :updated-at="report.updatedAt"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, useMeta } from '@nuxtjs/composition-api'
import { DwsBillingServiceReportFormat } from '@zinger/enums/lib/dws-billing-service-report-format'
import { DwsBillingStatus, resolveDwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { numeral } from '~/composables/numeral'
import { dwsBillingServiceReportStoreKey } from '~/composables/stores/use-dws-billing-service-report-store'
import { dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useDwsBillingStatusIcon } from '~/composables/use-dws-billing-status-icon'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'

export default defineComponent({
  name: 'DwsBillingServiceReportViewPage',
  setup () {
    const { isAuthorized } = useAuth()
    const { $snackbar } = usePlugins()
    const { withAxios, progress } = useAxios()
    const billingStore = useInjected(dwsBillingStoreKey)
    const store = useInjected(dwsBillingServiceReportStoreKey)
    /*
     * _reportId.vue で取得しているので、この時点で dwsBillingServiceReportStore.state から情報が取れることは保証されているため、
     * billing, bundle, report は value! で使用している
     * また、不変の項目（e.g. id）や 将来的にもこの画面で更新されることがなさそうな項目（e.g. user）は computed にしていない
     */
    const state = store.state
    const billing = state.billing
    const bundle = state.bundle
    const report = state.report
    const { breadcrumbs } = useBreadcrumbs(
      'dwsBillings.reports.view',
      { billingId: billing.value!.id, name: report.value!.user.name.displayName }
    )
    useMeta(() => ({ title: `障害福祉サービス請求 サービス提供実績記録票（${report.value!.user.name.displayName}）` }))
    const isHomeHelpService = computed(() => report.value!.format === DwsBillingServiceReportFormat.homeHelpService)
    const isVisitingCareForPwsd = computed(() => {
      return report.value!.format === DwsBillingServiceReportFormat.visitingCareForPwsd
    })
    const serviceReportTitle = computed(() => {
      if (isHomeHelpService.value) {
        return '居宅介護サービス提供実績記録票'
      } else if (isVisitingCareForPwsd.value) {
        return '重度訪問介護サービス提供実績記録票'
      } else {
        return ''
      }
    })

    const isFixed = computed(() => report.value!.status === DwsBillingStatus.fixed)
    const isReady = computed(() => report.value!.status === DwsBillingStatus.ready)
    const hasUpdatePermission = computed(() => isAuthorized.value([Permission.updateBillings]))
    const canUpdateStatus = computed(() => {
      return hasUpdatePermission.value &&
        (isFixed.value || isReady.value) &&
        DwsBillingStatus.fixed !== billingStore.state.billing.value?.status &&
        DwsBillingStatus.disabled !== billingStore.state.billing.value?.status
    })
    // サービス提供実績記録票状態更新
    const updateStatus = (status: DwsBillingStatus) => withAxios(
      async () => {
        await store.updateStatus({
          billingId: billing.value!.id,
          bundleId: bundle.value!.id,
          form: { status },
          id: report.value!.id
        })
        await billingStore.get({ id: billing.value!.id })
        $snackbar.success('サービス提供実績記録票の状態を変更しました。')
      },
      () => $snackbar.error('サービス提供実績記録票の状態変更に失敗しました。')
    )
    return {
      ...useDwsBillingStatusIcon(report),
      breadcrumbs,
      bundle,
      canUpdateStatus,
      determine: () => updateStatus(DwsBillingStatus.fixed),
      isFixed,
      isHomeHelpService,
      isVisitingCareForPwsd,
      isReady,
      numeral,
      office: billing.value!.office,
      progress,
      remand: () => updateStatus(DwsBillingStatus.ready),
      report,
      resolveDwsBillingStatus,
      serviceReportTitle
    }
  },
  head: {}
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/styles.sass';

@media #{map-get($display-breakpoints, 'xs-only')} {
  .reportTitle {
    :global(.v-subheader) {
      > div {
        font-size: 1.2rem !important;
      }
    }
  }
}
</style>
