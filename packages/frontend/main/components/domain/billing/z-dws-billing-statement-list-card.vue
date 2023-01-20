<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div>
    <z-data-table
      :selectable="billingStatus !== DwsBillingStatus.fixed"
      :items="units"
      :options="billingUnitTableOptions"
      :selected.sync="selected"
    >
      <template #title>サービス提供年月: {{ eraMonth(providedIn) }}</template>
      <template #form>
        <transition mode="out-in" name="fade">
          <v-form
            v-if="isSelected"
            key="selected"
            :class="$style.actionsForm"
            data-action-form
            @submit.prevent="doAction"
          >
            <v-alert
              v-if="hasError"
              class="text-sm-body-2 mb-3"
              data-action-errors
              dense
              type="error"
            >
              <template v-for="x in errors">
                {{ x }}<br :key="x">
              </template>
            </v-alert>
            <z-flex class="align-center justify-start">
              <div>
                <v-btn icon large @click.stop="resetSelected">
                  <v-icon>{{ $icons.close }}</v-icon>
                </v-btn>
              </div>
              <div class="text-body-1" :class="$style.actionsMessage">{{ numeral(selectedCount) }}件を選択中：</div>
              <v-spacer />
              <div :class="$style.actionsSelect">
                <z-select
                  v-model="action"
                  hide-details
                  single-line
                  :items="actions"
                  @change="changeAction"
                />
              </div>
              <div :class="$style.actionsButton">
                <v-btn
                  color="accent"
                  data-action-button
                  depressed
                  type="submit"
                  :disabled="action === ''"
                >
                  実行
                </v-btn>
              </div>
            </z-flex>
          </v-form>
          <template v-else>
            <v-row>
              <v-col class="py-0" cols="12" sm="8">
                <z-text-field
                  clearable
                  label="利用者名 or 市町村名"
                  :prepend-icon="$icons.keyword"
                  @input="updateKeyword"
                />
              </v-col>
              <v-col class="py-0" cols="12" sm="4">
                <z-select
                  v-model="status"
                  clearable
                  label="明細書の状態"
                  :items="statusSelectOptions"
                  :prepend-icon="$icons.statusUnknown"
                />
              </v-col>
            </v-row>
          </template>
        </transition>
      </template>
      <template #item.userName="{ item }">{{ item.userName }}</template>
      <template #item.dwsNumber="{ item }">{{ item.dwsNumber }}</template>
      <template #item.city="{ item }">{{ item.cityName }}</template>
      <template #item.statement="{ item: { statement } }">
        <component
          :is="canUpdate ? 'nuxt-link' : 'span'"
          v-if="statement"
          :to="canUpdate && createStatementUrl(statement)"
        >
          <v-icon small :color="canUpdate && 'primary'">{{ statusIcons[statement.status] }}</v-icon>
          {{ resolveDwsBillingStatus(statement.status) }}
        </component>
        <span v-else>-</span>
      </template>
      <template #item.homeHelpServiceReport="{ item: { homeHelpServiceReport } }">
        <component
          :is="canUpdate ? 'nuxt-link' : 'span'"
          v-if="homeHelpServiceReport"
          :to="canUpdate && createServiceReportUrl(homeHelpServiceReport)"
        >
          <v-icon small :color="canUpdate && 'primary'">{{ statusIcons[homeHelpServiceReport.status] }}</v-icon>
          {{ resolveDwsBillingStatus(homeHelpServiceReport.status) }}
        </component>
        <span v-else>-</span>
      </template>
      <template #item.visitingCareForPwsdReport="{ item: { visitingCareForPwsdReport } }">
        <component
          :is="canUpdate ? 'nuxt-link' : 'span'"
          v-if="visitingCareForPwsdReport"
          :to="canUpdate && createServiceReportUrl(visitingCareForPwsdReport)"
        >
          <v-icon small :color="canUpdate && 'primary'">{{ statusIcons[visitingCareForPwsdReport.status] }}</v-icon>
          {{ resolveDwsBillingStatus(visitingCareForPwsdReport.status) }}
        </component>
        <span v-else>-</span>
      </template>
      <template #item.copayCoordination="{ item: { statement, copayCoordination } }">
        <component
          :is="canUpdate ? 'nuxt-link' : 'span'"
          v-if="needCopayCoordination(statement.copayCoordinationStatus)"
          :to="canUpdate && createCopayCoordinationUrl(statement, copayCoordination)"
        >
          <v-icon small :color="canUpdate && 'primary'">
            {{ statusIcons[statement.copayCoordinationStatus] }}
          </v-icon>
          {{ resolveDwsBillingStatementCopayCoordinationStatus(statement.copayCoordinationStatus) }}
        </component>
        <span v-else>-</span>
      </template>
    </z-data-table>
    <z-prompt-dialog
      :active="copayListDialog.active.value"
      :options="{ message: '送付先の事業所ごとにファイルを分割しますか？', positive: 'ダウンロード', width: 450 }"
      @click:negative="copayListDialog.cancel"
      @click:positive="copayListDialog.downloadCopayLists"
    >
      <template #form>
        <validation-observer ref="observer" tag="div">
          <z-form-card-item
            v-slot="{ errors }"
            data-is-divided-form
            vid="isDivided"
            :rules="copayListDialog.rules.isDivided"
          >
            <v-radio-group v-model="copayListDialog.isDivided.value" :error-messages="errors" dense>
              <v-radio label="分割する - メールや電子 FAX で送信する場合におすすめ" :value="true" />
              <v-radio label="分割しない - 印刷する場合におすすめ" :value="false" />
            </v-radio-group>
          </z-form-card-item>
        </validation-observer>
      </template>
    </z-prompt-dialog>
    <z-progress :value="progress && isShowProgress" />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, ref, toRefs, watch } from '@nuxtjs/composition-api'
import {
  DwsBillingStatementCopayCoordinationStatus,
  resolveDwsBillingStatementCopayCoordinationStatus
} from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus, resolveDwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { Permission } from '@zinger/enums/lib/permission'
import { debounce, isEmpty } from '@zinger/helpers'
import toKatakana from 'jaco/fn/toKatakana'
import qs from 'qs'
import { colors } from '~/colors'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { eraMonth } from '~/composables/era-date'
import { numeral } from '~/composables/numeral'
import { selectOptions } from '~/composables/select-options'
import { dwsBillingStoreKey, DwsBillingUnit } from '~/composables/stores/use-dws-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { useJobPolling } from '~/composables/use-job-polling'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { DateString } from '~/models/date'
import { DwsBillingCopayCoordination } from '~/models/dws-billing-copay-coordination'
import { DwsBillingServiceReport } from '~/models/dws-billing-service-report'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { $icons } from '~/plugins/icons'
import { DwsBillingServiceReportsApi } from '~/services/api/dws-billing-service-reports-api'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { ConfirmDialogParams } from '~/services/confirm-dialog-service'
import { observerRef } from '~/support/reactive'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = {
  items: DwsBillingUnit[]
  providedIn: DateString
  billingStatus: DwsBillingStatus
}

type Action = ''
  | 'confirm-statements'
  | 'confirm-reports'
  | 'refresh-statements'
  | 'copay-lists'

export default defineComponent({
  name: 'ZDwsBillingStatementListCard',
  props: {
    items: { type: Array, required: true },
    providedIn: { type: String, required: true },
    billingStatus: { type: Number, required: true }
  },
  setup (props: Props) {
    const { isAuthorized } = useAuth()
    const { $download } = usePlugins()
    const store = useInjected(dwsBillingStoreKey)
    const observer = observerRef()
    const { billing } = store.state
    const canUpdate = computed(() => isAuthorized.value([Permission.updateBillings]))

    const state = reactive({
      keyword: '',
      status: undefined as DwsBillingStatus | undefined | null
    })
    const updateKeyword = debounce({ wait: 250 }, (value: string | null) => {
      state.keyword = value ?? ''
    })
    const matchWithKeyword = (keywords: string[], item: DwsBillingUnit) => {
      const targets = [
        item.userName,
        item.userPhoneticName,
        item.cityName
      ]
      return targets.some(x => keywords.some(keyword => x.includes(keyword)))
    }
    const units = computed(() => {
      const status = state.status
      const keyword = state.keyword.trim()
      const keywords = isEmpty(keyword) ? [] : [keyword, toKatakana(keyword)]
      return props.items.filter(x => {
        return (isEmpty(status) || x.statement!.status === status) &&
          (isEmpty(keyword) || matchWithKeyword(keywords, x))
      })
    })

    const statusText = {
      checking: resolveDwsBillingStatus(DwsBillingStatus.checking),
      ready: resolveDwsBillingStatus(DwsBillingStatus.ready),
      fixed: resolveDwsBillingStatus(DwsBillingStatus.fixed),
      disabled: resolveDwsBillingStatus(DwsBillingStatus.disabled)
    }
    const statusSelectOptions = [
      { text: statusText.checking, value: DwsBillingStatus.checking },
      { text: statusText.ready, value: DwsBillingStatus.ready },
      { text: statusText.fixed, value: DwsBillingStatus.fixed },
      { text: statusText.disabled, value: DwsBillingStatus.disabled }
    ]

    const billingUnitTableOptions = dataTableOptions({
      content: '請求単位詳細',
      headers: appendHeadersCommonProperty([
        { text: '利用者名', value: 'userName', width: 94 },
        { text: '受給者証番号', value: 'dwsNumber', width: 120 },
        { text: '市町村名', value: 'city', width: 160 },
        { text: '明細書', value: 'statement', align: 'center', width: 94 },
        { text: '実績記録票（居宅）', value: 'homeHelpServiceReport', align: 'center', width: 94 },
        { text: '実績記録票（重訪）', value: 'visitingCareForPwsdReport', align: 'center', width: 94 },
        { text: '上限額管理結果', value: 'copayCoordination', align: 'center', width: 94 }
      ])
    })
    const statusIcons = {
      [DwsBillingStatus.checking]: $icons.edit,
      [DwsBillingStatus.ready]: $icons.statusReady,
      [DwsBillingStatus.fixed]: $icons.statusResolved,
      [DwsBillingStatus.disabled]: $icons.statusDisabled,
      [DwsBillingStatementCopayCoordinationStatus.uncreated]: $icons.edit,
      [DwsBillingStatementCopayCoordinationStatus.unfilled]: $icons.statusProgress,
      [DwsBillingStatementCopayCoordinationStatus.checking]: $icons.edit,
      [DwsBillingStatementCopayCoordinationStatus.fulfilled]: $icons.statusResolved
    }
    const createLinkTo = computed(() => (
      categoryPath: string,
      item: { dwsBillingBundleId: number, id?: number, query?: Dictionary }
    ) => {
      if (!billing.value) {
        return ''
      } else {
        const fixed = `/dws-billings/${billing.value.id}/bundles/${item.dwsBillingBundleId}/${categoryPath}`
        const q = qs.stringify(item.query)
        return `${item.id ? `${fixed}/${item.id}` : `${fixed}/new`}${q ? `?${q}` : ''}`
      }
    })
    const createStatementUrl = (item: DwsBillingStatement) => createLinkTo.value('statements', item)
    /*
     * 上限管理事業所が自事業所かを返す
     * 判定条件は下記の通り
     * 上限額管理結果が「未作成」 => 自事業所
     * 上限額管理結果が「入力中」 => 自事業所
     * 上限額管理結果が「未入力」 => 他事業所
     * 上限額管理結果が「入力済」
     *   => 請求の事業所と上限管理事業所が同じ => 自事業所
     *   => 請求の事業所と上限管理事業所が異なる => 他事業所
     */
    const isSelfManagement = (statement: DwsBillingStatement) => {
      const { copayCoordination, copayCoordinationStatus: status } = statement
      if (status === DwsBillingStatementCopayCoordinationStatus.fulfilled) {
        const billingOfficeId = billing.value?.office.officeId
        return billingOfficeId === copayCoordination?.office.officeId
      } else {
        return status === DwsBillingStatementCopayCoordinationStatus.uncreated ||
          status === DwsBillingStatementCopayCoordinationStatus.checking
      }
    }
    const createCopayCoordinationUrl = (statement: DwsBillingStatement, item?: DwsBillingCopayCoordination) => {
      if (isSelfManagement(statement)) {
        const { dwsBillingBundleId, user } = statement
        const param = item ?? { dwsBillingBundleId, query: { userId: user.userId } }
        return createLinkTo.value(`statements/${statement.id}/copay-coordinations`, param)
      } else {
        return `${createStatementUrl(statement)}#copayCoordination`
      }
    }
    const needCopayCoordination = (status: DwsBillingStatementCopayCoordinationStatus) => {
      const xs: DwsBillingStatementCopayCoordinationStatus[] = [
        DwsBillingStatementCopayCoordinationStatus.uncreated,
        DwsBillingStatementCopayCoordinationStatus.unfilled,
        DwsBillingStatementCopayCoordinationStatus.checking,
        DwsBillingStatementCopayCoordinationStatus.fulfilled
      ]
      return xs.includes(status)
    }

    const data = reactive({
      action: '' as Action,
      selected: [] as DwsBillingUnit[]
    })
    const useSelectionState = () => {
      const selectedCount = computed(() => data.selected.length)
      const isSelected = computed(() => selectedCount.value !== 0)
      const resetSelected = () => data.selected.splice(0)
      return {
        selectedCount,
        isSelected,
        resetSelected
      }
    }
    const useSelectOptions = () => {
      const actions = selectOptions<Action>([
        { text: 'アクションを選択...', value: '' },
        { text: '明細書を確定する', value: 'confirm-statements', permissions: [Permission.updateBillings] },
        { text: 'サービス提供実績記録票を確定する', value: 'confirm-reports', permissions: [Permission.updateBillings] },
        { text: '請求情報を更新する', value: 'refresh-statements', permissions: [Permission.updateBillings] },
        { text: '利用者負担額一覧表をダウンロードする', value: 'copay-lists', permissions: [Permission.updateBillings] }
      ])
      return {
        actions
      }
    }
    const useDoAction = () => {
      const isShowProgress = ref(true)
      const { $api, $confirm, $form, $snackbar } = usePlugins()
      const { errors, progress, withAxios } = useAxios()
      const { execute } = useJobWithNotification()
      const { startJobPolling } = useJobPolling()
      const confirmStatements = async (
        billingId: number,
        form: DwsBillingStatementsApi.BulkUpdateStatusForm
      ) => {
        const params: ConfirmDialogParams = {
          color: colors.critical,
          message: '選択した明細書を確定します。\n\n本当によろしいですか？',
          positive: '確定'
        }
        if (await $confirm.show(params)) {
          await withAxios(async () => {
            const result = await startJobPolling(async () => {
              return await $api.dwsBillingStatements.bulkUpdateStatus({ billingId, form })
            })
            if (result === false) {
              $snackbar.error('明細書の確定に失敗しました。')
            } else {
              const { job } = result
              if (job.status === JobStatus.failure) {
                $snackbar.error('明細書の確定に失敗しました。')
              } else if (job.status === JobStatus.success) {
                await store.get({ id: billingId }).then(
                  () => $snackbar.success('明細書を確定しました。'),
                  () => $snackbar.warning('画面情報を更新できませんでした。最新の情報を見るにはブラウザをリロードしてください。')
                )
                data.selected = []
              }
            }
          })
        }
      }
      const confirmReports = async (
        billingId: number,
        form: DwsBillingServiceReportsApi.BulkUpdateStatusForm
      ) => {
        const params: ConfirmDialogParams = {
          color: colors.critical,
          message: '選択したサービス提供実績記録票を確定します。\n\n本当によろしいですか？',
          positive: '確定'
        }
        if (await $confirm.show(params)) {
          await withAxios(async () => {
            const result = await startJobPolling(async () => {
              return await $api.dwsBillingServiceReports.bulkUpdateStatus({ billingId, form })
            })
            if (result === false) {
              $snackbar.error('サービス提供実績記録票の確定に失敗しました。')
            } else {
              const { job } = result
              if (job.status === JobStatus.failure) {
                $snackbar.error('サービス提供実績記録票の確定に失敗しました。')
              } else if (job.status === JobStatus.success) {
                await store.get({ id: billingId }).then(
                  () => $snackbar.success('サービス提供実績記録票を確定しました。'),
                  () => $snackbar.warning('画面情報を更新できませんでした。最新の情報を見るにはブラウザをリロードしてください。')
                )
                data.selected = []
              }
            }
          })
        }
      }
      const refreshStatements = async (
        billingId: number,
        form: DwsBillingStatementsApi.RefreshForm
      ) => {
        const params: ConfirmDialogParams = {
          color: colors.critical,
          message: '選択した利用者の明細書・サービス提供実績記録票・利用者負担上限額管理結果票を最新の予実を使って更新します。\n\n更新が完了すると、確定済の明細書・サービス提供実績記録票・利用者負担上限額管理結果票は未確定状態となります。\n\n本当によろしいですか？',
          positive: '更新'
        }
        if (await $confirm.show(params)) {
          isShowProgress.value = false
          return withAxios(() => {
            return $form.submit(() => execute({
              notificationProps: {
                text: {
                  progress: '請求情報の更新を準備中です...',
                  success: '請求情報を更新しました',
                  failure: '請求情報の更新に失敗しました'
                }
              },
              process: () => $api.dwsBillingStatements.refresh({ billingId, form }),
              success: async () => {
                await store.get({ id: billingId })
                data.selected = []
              }
            })).finally(() => { isShowProgress.value = true })
          })
        }
      }
      const useCopayListDialog = () => {
        const isDivided = ref<boolean | undefined>(undefined)
        const active = ref(false)
        const show = () => {
          active.value = true
        }
        const hide = () => {
          active.value = false
        }
        const reset = () => {
          observer.value?.reset()
          isDivided.value = undefined
        }
        const cancel = () => {
          hide()
          reset()
        }
        const rules = validationRules({
          isDivided: { required }
        })
        const downloadCopayLists = async () => {
          const passed = await observer.value?.validate() ?? false
          if (!passed) {
            return
          }
          hide()
          isShowProgress.value = false
          return withAxios(() => {
            return $form
              .submit(() => execute({
                notificationProps: {
                  text: {
                    progress: '利用者負担額一覧表のダウンロードを準備中です...',
                    success: '利用者負担額一覧表をダウンロードしました',
                    failure: '利用者負担額一覧表のダウンロードに失敗しました'
                  }
                },
                process: () => $api.copayLists.download({
                  billingId: data.selected[0].statement!.dwsBillingId,
                  form: { ids: data.selected.map(x => x.id), isDivided: isDivided.value! }
                }),
                success: job => {
                  $download.uri(job.data.uri, job.data.filename)
                  data.selected = []
                }
              }))
              .finally(() => {
                isShowProgress.value = true
                reset()
              })
          })
        }
        return {
          downloadCopayLists,
          cancel,
          active,
          isDivided,
          rules,
          show
        }
      }
      const copayListDialog = useCopayListDialog()
      watch(() => data.selected, () => {
        clearErrors()
      })
      const changeAction = () => {
        clearErrors()
      }
      const doAction = async () => {
        const billingId = data.selected[0].statement!.dwsBillingId
        const ids = data.selected.map(x => x.id)
        switch (data.action) {
          case 'confirm-statements':
            await confirmStatements(
              billingId,
              { ids, status: DwsBillingStatus.fixed })
            break
          case 'confirm-reports':
            await confirmReports(
              billingId,
              {
                ids: data.selected.flatMap(x => [x.homeHelpServiceReport, x.visitingCareForPwsdReport])
                  .filter(Boolean)
                  .map(x => x!.id),
                status: DwsBillingStatus.fixed
              }
            )
            break
          case 'refresh-statements':
            await refreshStatements(billingId, { ids })
            break
          case 'copay-lists':
            copayListDialog.show()
            break
          default:
        }
      }
      const hasError = computed(() => Object.keys(errors.value).length >= 1)
      const clearErrors = () => {
        errors.value = {}
      }
      return {
        changeAction,
        doAction,
        errors: computed(() => Object.values(errors.value).flat()),
        hasError,
        numeral,
        copayListDialog,
        isShowProgress,
        progress
      }
    }

    return {
      ...toRefs(data),
      ...toRefs(state),
      ...useDoAction(),
      ...useSelectionState(),
      ...useSelectOptions(),
      billingUnitTableOptions,
      canUpdate,
      createCopayCoordinationUrl,
      createServiceReportUrl: (item: DwsBillingServiceReport) => createLinkTo.value('reports', item),
      createStatementUrl,
      eraMonth,
      DwsBillingStatus,
      needCopayCoordination,
      observer,
      resolveDwsBillingStatementCopayCoordinationStatus,
      resolveDwsBillingStatus,
      statusIcons,
      statusSelectOptions,
      units,
      updateKeyword
    }
  }
})
</script>

<style lang="scss" module>
.actionsMessage {
  margin: 0 0 0 4px;
}

.actionsForm {
  padding: 16px 16px 16px 8px;
}

.actionsSelect {
  max-width: 50%;
  margin: 0 0 0 4px;
  width: 400px;
}

.actionsButton {
  margin: 0 0 0 4px;
}
</style>
