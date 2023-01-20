<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-data-card title="基本情報">
      <z-data-card-item label="市町村番号" :icon="$icons.office" :value="bundle.cityCode" />
      <z-data-card-item label="サービス提供年月" :icon="$icons.date">
        <z-era-date :value="bundle.providedIn" />
      </z-data-card-item>
      <z-data-card-item
        label="利用者負担上限額管理結果票の状態"
        :icon="statusIcon"
        :value="resolveDwsBillingStatus(copayCoordination.status)"
      />
      <v-card-actions v-if="canUpdateStatus">
        <v-spacer />
        <v-btn v-if="isReady" color="primary" text data-determine-btn @click="determine">
          利用者負担上限額管理結果票を確定する
        </v-btn>
        <v-btn v-else color="primary" text data-remand-btn @click="remand">
          利用者負担上限額管理結果票を未確定にする
        </v-btn>
      </v-card-actions>
    </z-data-card>
    <z-data-card title="受給者情報">
      <z-data-card-item label="受給者証番号" :icon="$icons.dwsNumber" :value="copayCoordination.user.dwsNumber" />
      <z-data-card-item label="支給決定障害者等氏名" :icon="$icons.dws" :value="copayCoordination.user.name.displayName" />
      <z-data-card-item label="支給決定に係る障害児氏名" :value="copayCoordination.user.childName.displayName" />
      <z-data-card-item label="利用者負担上限月額" :icon="$icons.copayLimit">
        {{ numeral(copayCoordination.user.copayLimit) }}円
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="管理事業者">
      <z-data-card-item label="指定事業所番号" :icon="$icons.office" :value="copayCoordination.office.code" />
      <z-data-card-item label="事業者及びその事業所の名称" :value="copayCoordination.office.name" />
    </z-data-card>
    <z-data-card title="作成区分">
      <z-data-card-item
        label="作成区分"
        :icon="$icons.category"
        :value="resolveDwsBillingCopayCoordinationExchangeAim(copayCoordination.exchangeAim)"
      />
    </z-data-card>
    <z-data-card title="利用者負担上限額管理結果">
      <z-data-card-item
        label="利用者負担上限額管理結果"
        :icon="$icons.text"
        :value="resolveCopayCoordinationResult(copayCoordination.result)"
      />
    </z-data-card>
    <z-data-card title="利用者負担上限額管理結果（明細）">
      <z-overflow-shadow>
        <v-simple-table :class="$style.itemTable" dense>
          <template #default>
            <thead>
              <tr>
                <th style="min-width: 58px">項番</th>
                <th style="width: 116px">事業所番号</th>
                <th style="min-width: 210px">事業所名</th>
                <th class="text-right" style="min-width: 100px">総費用額</th>
                <th class="text-right" style="min-width: 108px">利用者負担額</th>
                <th class="text-right" style="min-width: 108px">管理結果後<br>利用者負担額</th>
              </tr>
            </thead>
            <tbody v-if="copayCoordination && copayCoordination.items.length >= 1">
              <tr v-for="(item, i) in copayCoordination.items" :key="`tbody_${i}`">
                <td>{{ item.itemNumber }}</td>
                <td>{{ item.office.code }}</td>
                <td>{{ item.office.name }}</td>
                <td class="text-right">{{ numeral(item.subtotal.fee) }}円</td>
                <td class="text-right">{{ numeral(item.subtotal.copay) }}円</td>
                <td class="text-right">{{ numeral(item.subtotal.coordinatedCopay) }}円</td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right">{{ numeral(copayCoordination.total.fee) }}円</td>
                <td class="text-right">{{ numeral(copayCoordination.total.copay) }}円</td>
                <td class="text-right">{{ numeral(copayCoordination.total.coordinatedCopay) }}円</td>
              </tr>
            </tbody>
            <tbody v-else class="no-data">
              <tr>
                <td class="pt-2 text-center" :colspan="6">データがありません</td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </z-overflow-shadow>
    </z-data-card>
    <z-system-meta-card
      :id="copayCoordination.id"
      :created-at="copayCoordination.createdAt"
      :updated-at="copayCoordination.updatedAt"
    />
    <z-fab-speed-dial
      v-if="canEdit || canDownload"
      data-speed-dial
      :icon="$icons.editVariant"
    >
      <z-fab-speed-dial-button
        v-if="canEdit"
        data-edit-button
        nuxt
        :icon="$icons.edit"
        :to="`${copayCoordination.id}/edit`"
      >
        利用者負担上限額管理結果票を編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="canDownload"
        data-download-button
        :icon="$icons.download"
        @click="download"
      >
        利用者負担上限額管理結果票をダウンロード
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, useMeta } from '@nuxtjs/composition-api'
import { resolveCopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { resolveDwsBillingCopayCoordinationExchangeAim } from '@zinger/enums/lib/dws-billing-copay-coordination-exchange-aim'
import { DwsBillingStatus, resolveDwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { numeral } from '~/composables/numeral'
import {
  dwsBillingCopayCoordinationStateKey,
  dwsBillingCopayCoordinationStoreKey
} from '~/composables/stores/use-dws-billing-copay-coordination-store'
import { dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useDwsBillingStatusIcon } from '~/composables/use-dws-billing-status-icon'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'

export default defineComponent({
  name: 'DwsBillingCopayCoordinationViewPage',
  setup () {
    const { isAuthorized } = useAuth()
    const { $download, $snackbar } = usePlugins()
    const { withAxios } = useAxios()
    const { billing, bundle, copayCoordination } = useInjected(dwsBillingCopayCoordinationStateKey)
    const store = useInjected(dwsBillingCopayCoordinationStoreKey)
    const billingStore = useInjected(dwsBillingStoreKey)
    // 利用者負担上限額管理結果票状態更新
    const updateStatus = (status: DwsBillingStatus) => withAxios(
      async () => {
        await store.updateStatus({
          billingId: billing.value!.id,
          bundleId: bundle.value!.id,
          form: { status },
          id: copayCoordination.value!.id
        })
        $snackbar.success('利用者負担上限額管理結果票の状態を変更しました。')
        billingStore.get({ id: billing.value!.id })
      },
      () => $snackbar.error('利用者負担上限額管理結果票の状態変更に失敗しました。')
    )
    const { user } = copayCoordination.value!
    const { breadcrumbs } = useBreadcrumbs(
      'dwsBillings.copayCoordination.view',
      { billingId: billing.value?.id, name: user.name.displayName }
    )
    useMeta(() => ({ title: `障害福祉サービス請求 利用者負担上限額管理結果票（${user.name.displayName}）` }))
    const download = () => {
      $download.uri(`/api/dws-billings/${billing.value!.id}/bundles/${bundle.value!.id}/copay-coordinations/${copayCoordination.value!.id}.pdf`)
    }
    const isFixed = computed(() => copayCoordination.value!.status === DwsBillingStatus.fixed)
    const isReady = computed(() => copayCoordination.value!.status === DwsBillingStatus.ready)
    const hasUpdatePermission = computed(() => isAuthorized.value([Permission.updateBillings]))
    const canUpdateStatus = computed(() => hasUpdatePermission.value && (isFixed.value || isReady.value))
    return {
      ...useDwsBillingStatusIcon(copayCoordination),
      breadcrumbs,
      bundle,
      canEdit: computed(() => hasUpdatePermission.value && !isFixed.value),
      canDownload: computed(() => isAuthorized.value([Permission.downloadBillings])),
      canUpdateStatus,
      copayCoordination,
      determine: () => updateStatus(DwsBillingStatus.fixed),
      download,
      isFixed,
      isReady,
      numeral,
      remand: () => updateStatus(DwsBillingStatus.ready),
      resolveCopayCoordinationResult,
      resolveDwsBillingCopayCoordinationExchangeAim,
      resolveDwsBillingStatus
    }
  },
  head: {}
})
</script>

<style lang="scss" module>
.itemTable {
  :global {
    th,
    td {
      padding: 0 12px !important;
    }

    tr:hover {
      background: inherit !important;
    }
  }
}
</style>
