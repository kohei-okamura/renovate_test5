<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page v-if="statement" data-page-dws-billing-statement :class="$style.root" compact :breadcrumbs="breadcrumbs">
    <z-data-card title="基本情報">
      <z-data-card-item label="市町村番号" :icon="$icons.office" :value="bundle.cityCode" />
      <z-data-card-item label="助成自治体番号" :value="statement.subsidyCityCode" />
      <z-data-card-item label="サービス提供年月" :icon="$icons.date">
        <z-era-date :value="bundle.providedIn" />
      </z-data-card-item>
      <z-data-card-item label="明細書の状態" :icon="statusIcon" :value="resolveDwsBillingStatus(statement.status)" />
      <v-card-actions v-if="canUpdateStatus" data-update-status>
        <v-spacer />
        <v-btn v-if="isReady" ref="determineButton" color="primary" text :loading="progress" @click="determine">
          明細書を確定する
        </v-btn>
        <v-btn v-else ref="remandButton" color="primary" text :loading="progress" @click="remand">
          明細書を未確定にする
        </v-btn>
      </v-card-actions>
    </z-data-card>
    <z-data-card title="受給者情報">
      <z-data-card-item label="受給者証番号" :icon="$icons.dwsNumber" :value="statement.user.dwsNumber" />
      <z-data-card-item label="支給決定障害者等氏名" :icon="$icons.dws" :value="statement.user.name.displayName" />
      <z-data-card-item label="支給決定に係る障害児氏名" :value="statement.user.childName.displayName" />
    </z-data-card>
    <z-data-card title="請求事業者">
      <z-data-card-item label="指定事業所番号" :icon="$icons.office" :value="billing.office.code" />
      <z-data-card-item label="事業者及びその事業所の名称" :value="billing.office.name" />
      <z-data-card-item label="地域区分" :value="statement.dwsAreaGradeName" />
      <z-data-card-item label="就労継続支援Ａ型事業者負担減免措置実施" value="1" />
      <z-data-card-item
        label="利用者負担上限月額"
        :icon="$icons.copayLimit"
        :value="getDisplayAmount(statement.user.copayLimit)"
      />
      <z-data-card-item label="就労継続支援Ａ型減免対象者" value="-" :icon="$icons.dws" />
    </z-data-card>
    <z-data-card
      ref="copayCoordinationCard"
      v-intersect.once.quiet="copayCoordinationCardIntersect"
      title="利用者負担上限額管理事業所"
    >
      <z-data-card-item
        label="上限管理区分"
        :icon="$icons.category"
        :value="resolveCopayCoordinationStatus(statement.copayCoordinationStatus)"
      />
      <template v-if="copayCoordination.necessary">
        <z-data-card-item label="指定事業所番号" :icon="$icons.office" :value="copayCoordination.data.office.code" />
        <z-data-card-item label="事業所名称" :value="copayCoordination.data.office.name" />
        <z-data-card-item
          label="管理結果"
          :icon="$icons.text"
          :value="resolveCopayCoordinationResult(copayCoordination.data.result, '-')"
        />
        <z-data-card-item
          label="管理結果額"
          :icon="$icons.yen"
          :value="getDisplayAmount(copayCoordination.data.amount)"
        />
        <v-card-actions v-if="canUpdateContent">
          <v-spacer />
          <v-btn
            ref="editCopayCoordinationButton"
            color="primary"
            text
            @click="copayCoordinationEditor.openDialog"
          >
            {{ copayCoordinationEditor.editButtonLabel.value }}
          </v-btn>
        </v-card-actions>
      </template>
    </z-data-card>
    <z-data-table :items="statement.aggregates" :options="serviceTypeTableOptions">
      <template #item.serviceDivisionCode="{ item }">{{ createDisplayServiceType(item.serviceDivisionCode) }}</template>
      <template #item.startedOn="{ item }">
        <z-era-date short :value="item.startedOn" />
      </template>
      <template #item.terminatedOn="{ item }">
        <z-era-date short :value="item.terminatedOn" />
      </template>
      <template #item.serviceDays="{ item }">{{ item.serviceDays }}</template>
    </z-data-table>
    <z-data-table :items="statement.items" :options="detailsTableOptions">
      <template #item.service="{ item }">{{ resolveServiceContentAbbr(item.serviceCode) }}</template>
      <template #item.serviceCode="{ item }">{{ item.serviceCode }}</template>
      <template #item.unitScore="{ item }">{{ numeralOrHyphen(item.unitScore) }}</template>
      <template #item.count="{ item }">{{ item.count }}</template>
      <template #item.totalScore="{ item }">{{ numeralOrHyphen(item.totalScore) }}</template>
    </z-data-table>

    <z-dws-billings-statements-form
      :errors="errors"
      :progress="progress"
      :value="formValue"
      :statement="statement"
      :can-update-content="canUpdateContent"
      @submit="submit"
    />

    <z-dws-billing-copay-coordination-form-dialog
      v-if="canUpdateContent"
      ref="copayCoordinationForm"
      :dialog="copayCoordinationEditor.dialog.value"
      :errors="errors"
      :progress="progress"
      :value="copayCoordinationEditor.form"
      :amount="totalCoordinatedCopay"
      @submit="copayCoordinationEditor.submit"
      @update:dialog="copayCoordinationEditor.toggleDialog"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, reactive, ref } from '@nuxtjs/composition-api'
import { resolveCopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import {
  DwsBillingStatementCopayCoordinationStatus,
  resolveDwsBillingStatementCopayCoordinationStatus
} from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus, resolveDwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { DwsServiceDivisionCode, resolveDwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { Permission } from '@zinger/enums/lib/permission'
import { isEmpty } from '@zinger/helpers'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { numeral } from '~/composables/numeral'
import { dwsBillingStatementStoreKey } from '~/composables/stores/use-dws-billing-statement-store'
import { dwsBillingStoreKey } from '~/composables/stores/use-dws-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useDialogBindings } from '~/composables/use-dialog-bindings'
import { useDwsBillingStatusIcon } from '~/composables/use-dws-billing-status-icon'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBillingStatementCopayCoordination } from '~/models/dws-billing-statement-copay-coordination'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { componentRef } from '~/support/reactive'

export default defineComponent({
  name: 'DwsBillingStatementViewPage',
  setup () {
    const { $route, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { isAuthorized } = useAuth()
    const billingStore = useInjected(dwsBillingStoreKey)
    const store = useInjected(dwsBillingStatementStoreKey)
    /*
     * _statementId.vue で取得しているので、この時点で dwsBillingStatementStore.state から情報が取れることは保証されているため、
     * billing, bundle, statement は基本的には value! で使用している
     * また、不変の項目（e.g. id）は computed にしていない
     */
    const state = store.state
    const billing = state.billing
    const bundle = state.bundle
    const statement = state.statement
    const serviceTypeTableOptions = dataTableOptions({
      content: 'サービス種別',
      headers: appendHeadersCommonProperty([
        { text: 'サービス種別', value: 'serviceDivisionCode' },
        { text: '開始年月日', value: 'startedOn' },
        { text: '終了年月日', value: 'terminatedOn' },
        { text: '利用日数', value: 'serviceDays', width: 100, align: 'end' }
      ], { align: 'start' }),
      title: 'サービス種別'
    })
    const detailsTableOptions = dataTableOptions({
      content: '給付費明細欄',
      headers: appendHeadersCommonProperty([
        { text: 'サービス内容', value: 'service', width: 280, align: 'start' },
        { text: 'サービスコード', value: 'serviceCode', width: 124, align: 'start' },
        { text: '単位数', value: 'unitScore', width: 100 },
        { text: '回数', value: 'count', width: 60 },
        { text: 'サービス単位数', value: 'totalScore', width: 124 }
      ], { align: 'end' }),
      title: '給付費明細欄'
    })
    const createDisplayServiceType = (code: DwsServiceDivisionCode) => {
      return `${code}: ${resolveDwsServiceDivisionCode(code)}`
    }
    const numeralOrHyphen = (num?: number) => isEmpty(num) ? '-' : numeral(num)

    const isBillingFixed = computed(() => billing.value!.status === DwsBillingStatus.fixed)
    const isFixed = computed(() => statement.value!.status === DwsBillingStatus.fixed)
    const isReady = computed(() => statement.value!.status === DwsBillingStatus.ready)
    const hasUpdatePermission = computed(() => isAuthorized.value([Permission.updateBillings]))
    const canUpdateStatus = computed(() => {
      return hasUpdatePermission.value && (isFixed.value || isReady.value) && !isBillingFixed.value
    })
    const canUpdateContent = computed(() => hasUpdatePermission.value && !isFixed.value)

    // 明細書状態更新
    const updateStatus = (status: DwsBillingStatus) => withAxios(
      async () => {
        await store.updateStatus({
          billingId: billing.value!.id,
          bundleId: bundle.value!.id,
          form: { status },
          id: statement.value!.id
        })
        await billingStore.get({ id: billing.value!.id })
        $snackbar.success('明細の状態を変更しました。')
      },
      () => $snackbar.error('明細の状態変更に失敗しました。')
    )
    // 明細書更新
    const formValue = reactive(Object.fromEntries(statement.value!.aggregates.map(x => {
      return [
        x.serviceDivisionCode,
        {
          managedCopay: String(x.managedCopay),
          subtotalSubsidy: isEmpty(x.subtotalSubsidy) ? undefined : String(x.subtotalSubsidy)
        }
      ]
    })))
    type Aggregates = DwsBillingStatementsApi.UpdateForm['aggregates']
    const submit = (formValue: { [p: string]: { managedCopay: string, subtotalSubsidy?: string } }) => {
      if (!canUpdateContent.value) {
        return
      }
      return withAxios(
        async () => {
          const aggregates = Object.entries(formValue)
            .map(([k, { managedCopay, subtotalSubsidy }]) => ({
              serviceDivisionCode: k,
              managedCopay: parseInt(managedCopay),
              ...subtotalSubsidy && { subtotalSubsidy: parseInt(subtotalSubsidy) }
            })) as Aggregates
          await store.update({
            billingId: billing.value!.id,
            bundleId: bundle.value!.id,
            form: { aggregates },
            id: statement.value!.id
          })
          $snackbar.success('明細書を編集しました。')
        },
        () => {
          $snackbar.error('明細書の編集に失敗しました。')
        }
      )
    }
    // 明細書：上限管理結果
    const copayCoordination = (() => {
      const status = DwsBillingStatementCopayCoordinationStatus
      return computed(() => {
        const copayCoordination = statement.value?.copayCoordination
        const currentStatus = statement.value?.copayCoordinationStatus
        const data: DeepPartial<DwsBillingStatementCopayCoordination> = {
          office: { code: '-', name: '-' },
          result: undefined,
          amount: undefined,
          ...(copayCoordination ?? {})
        }
        const necessary = status.unapplicable !== currentStatus && status.unclaimable !== currentStatus
        const uncreated = !copayCoordination &&
          (status.uncreated === currentStatus || status.unfilled === currentStatus)
        return {
          data,
          necessary,
          uncreated
        }
      })
    })()
    // 明細書：上限管理結果登録、更新
    const useEditCopayCoordination = () => {
      type UpdateCopayCoordinationForm = Partial<DwsBillingStatementsApi.UpdateCopayCoordinationForm>
      const dialog = useDialogBindings()
      dialog.disableRouterBack()
      const action = ref<'登録' | '編集'>(copayCoordination.value.uncreated ? '登録' : '編集')
      const state = reactive<UpdateCopayCoordinationForm>({
        amount: copayCoordination.value.data.amount,
        result: copayCoordination.value.data.result
      })
      const submit = (form: UpdateCopayCoordinationForm) => withAxios(
        async () => {
          await store.updateCopayCoordination({
            billingId: billing.value!.id,
            bundleId: bundle.value!.id,
            form,
            id: statement.value!.id
          })
          dialog.closeDialog()
          $snackbar.success(`利用者負担上限額管理結果を${action.value}しました。`)
          // 登録に成功したら各値を更新する
          action.value = '編集'
          state.amount = form.amount
          state.result = form.result
        },
        () => {
          dialog.closeDialog()
          $snackbar.error(`利用者負担上限額管理結果の${action.value}に失敗しました。`)
        }
      )
      return {
        editButtonLabel: computed(() => `利用者負担上限額管理結果を${action.value}`),
        dialog: dialog.dialog,
        form: state,
        openDialog: dialog.openDialog,
        submit,
        toggleDialog: dialog.toggleDialog
      }
    }
    const totalCoordinatedCopay = computed(() => {
      const totalAdjustedCopay = statement.value?.totalAdjustedCopay
      const totalCappedCopay = statement.value?.totalCappedCopay
      return totalAdjustedCopay ?? totalCappedCopay
    })
    const copayCoordinationEditor = useEditCopayCoordination()
    // 遷移後の自動スクロールとダイアログ表示
    const copayCoordinationCard = componentRef()
    const copayCoordinationCardIntersect = (() => {
      if ($route.hash.includes('copayCoordination') &&
        statement.value!.copayCoordinationStatus === DwsBillingStatementCopayCoordinationStatus.unfilled) {
        return {
          // 現状では最初に交差した時のみ実行されるため引数は見ていない
          handler: () => {
            // スクロールが終わってから出るように少し遅らせる
            setTimeout(() => {
              copayCoordinationEditor.openDialog()
            }, 300)
          },
          options: {
            rootMargin: '-50% 0px 0px 0px'
          }
        }
      } else {
        return undefined
      }
    })()
    onMounted(() => {
      if ($route.hash.includes('copayCoordination')) {
        copayCoordinationCard.value?.$el.scrollIntoView({ behavior: 'smooth', block: 'center' })
      }
    })
    return {
      ...useBreadcrumbs('dwsBillings.statement.view', billing),
      ...useDwsBillingStatusIcon(statement),
      billing,
      bundle,
      canUpdateContent,
      canUpdateStatus,
      copayCoordination,
      copayCoordinationCard,
      copayCoordinationCardIntersect,
      copayCoordinationEditor,
      createDisplayServiceType,
      detailsTableOptions,
      determine: () => updateStatus(DwsBillingStatus.fixed),
      errors,
      formValue,
      getDisplayAmount: (v?: number) => `${numeralOrHyphen(v)} 円`,
      isFixed,
      isReady,
      numeralOrHyphen,
      totalCoordinatedCopay,
      progress,
      remand: () => updateStatus(DwsBillingStatus.ready),
      resolveCopayCoordinationResult,
      resolveCopayCoordinationStatus: resolveDwsBillingStatementCopayCoordinationStatus,
      resolveDwsBillingStatus,
      resolveServiceContentAbbr: state.resolveServiceContentAbbr,
      serviceTypeTableOptions,
      statement,
      submit
    }
  },
  head: () => ({
    title: '障害福祉サービス請求 明細書'
  })
})
</script>

<style lang="scss" module>
.root {
  :global {
    tr:hover {
      background: inherit !important;
    }
  }
}
</style>
