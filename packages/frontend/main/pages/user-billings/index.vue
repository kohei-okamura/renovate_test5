<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-user-billings-index :breadcrumbs="breadcrumbs">
    <z-data-table
      data-table
      :items="userBillings"
      :loading="isLoadingUserBillings"
      :options="options"
      :selectable="true"
      :selected.sync="selected"
    >
      <template #item.providedIn="{ item }">{{ eraMonth(item.providedIn) }}</template>
      <template #item.issuedOn="{ item }">{{ eraDate(item.issuedOn, 'short') }}</template>
      <template #item.user="{ item }">{{ item.user.name.displayName }}</template>
      <template #item.office="{ item }">{{ resolveOfficeAbbr(item.officeId) }}</template>
      <template #item.totalAmount="{ item }">{{ numeral(item.totalAmount) }}円</template>
      <template #item.paymentMethod="{ item }">
        {{ resolvePaymentMethod(item.user.billingDestination.paymentMethod) }}
      </template>
      <template #item.result="{ item }">{{ resolveUserBillingResult(item.result) }}</template>
      <template #item.depositedAt="{ item }">{{ eraDate(item.depositedAt, 'short') }}</template>
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
              v-if="isSelected && alertTexts.length"
              class="text-sm-body-2 mb-3"
              data-local-errors
              dense
              type="error"
            >
              <template v-for="x in alertTexts">
                {{ x }}<br :key="x">
              </template>
            </v-alert>
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
                <v-btn color="accent" data-action-button depressed type="submit" :disabled="action === ''">実行</v-btn>
              </div>
            </z-flex>
          </v-form>
          <v-form v-else key="not-selected" @submit.prevent="submit">
            <v-row class="mt-1 mt-sm-0">
              <v-col class="py-0" cols="12">
                <v-alert v-show="hasTooManyResults" class="text-caption text-sm-body-2" dense type="error">
                  検索結果が {{ numberOfLimit }} 件を超えているため表示できません。<br>
                  検索条件を追加してしてください。
                </v-alert>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12" sm="6" md="4" xl="3">
                <z-keyword-filter-autocomplete
                  v-model="form.officeId"
                  hide-details
                  label="事業所"
                  :clearable="true"
                  :items="officeOptions"
                  :loading="isLoadingOffices"
                  :prepend-icon="$icons.office"
                />
              </v-col>
              <v-col cols="12" sm="6" md="4" xl="3">
                <z-date-field
                  v-model="form.providedIn"
                  hide-details
                  label="サービス提供年月"
                  type="month"
                  :prepend-icon="$icons.month"
                />
              </v-col>
              <v-col cols="12" sm="6" md="4" xl="3">
                <z-date-field
                  v-model="form.issuedIn"
                  hide-details
                  label="請求年月"
                  type="month"
                  :prepend-icon="$icons.month"
                />
              </v-col>
              <v-col cols="12" sm="6" md="4" xl="3">
                <z-select-search-condition
                  v-model="form.paymentMethod"
                  hide-details
                  label="支払方法"
                  :items="paymentMethodOptions"
                  :prepend-icon="$icons.wallet"
                />
              </v-col>
              <v-col cols="12" sm="6" md="4" xl="3">
                <z-select-search-condition
                  v-model="form.usedService"
                  hide-details
                  label="利用サービス"
                  :items="usedServiceOptions"
                  :prepend-icon="$icons.serviceOption"
                />
              </v-col>
              <v-col cols="12" sm="6" md="4" xl="3">
                <z-select-search-condition
                  v-model="form.result"
                  hide-details
                  label="請求結果"
                  :items="resultOptions"
                  :prepend-icon="$icons.billing"
                />
              </v-col>
              <v-col class="ml-auto" cols="12" sm="6" md="4" xl="2">
                <v-btn block color="primary" depressed type="submit">検索</v-btn>
              </v-col>
            </v-row>
          </v-form>
        </transition>
      </template>
    </z-data-table>
    <z-fab-speed-dial
      v-if="canUploadWithdrawal || canDownloadWithdrawal"
      data-fab
      :icon="$icons.add"
    >
      <z-fab-speed-dial-button
        v-if="canDownloadWithdrawal"
        data-withdrawal-transactions-download
        to="/withdrawal-transactions"
        :icon="$icons.download"
      >
        全銀ファイルダウンロード
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="canUploadWithdrawal"
        data-withdrawal-transactions-upload
        to="/user-billing-bulk-update/new"
        :icon="$icons.upload"
      >
        全銀ファイルアップロード
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
    <z-progress :value="progress" />
    <z-date-confirm-dialog
      data-date-confirm-dialog="depositedOn"
      message="入金日を選択してください"
      positive-label="登録"
      :active="dateRegistrationDialog.isActive.value"
      :max="today"
      @click:negative="dateRegistrationDialog.cancel"
      @click:positive="dateRegistrationDialog.run"
    />
    <z-date-confirm-dialog
      data-date-confirm-dialog="download"
      message="印字する発行日を選択してください"
      positive-label="ダウンロード"
      :active="fileDownloadDialog.isActive.value"
      @click:negative="fileDownloadDialog.cancel"
      @click:positive="fileDownloadDialog.run"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, ref, toRefs, UnwrapRef, watch } from '@nuxtjs/composition-api'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { PaymentMethod, resolvePaymentMethod } from '@zinger/enums/lib/payment-method'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveUserBillingResult, UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { UserBillingUsedService } from '@zinger/enums/lib/user-billing-used-service'
import deepmerge from 'deepmerge'
import { colors } from '~/colors'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { eraDate, eraMonth } from '~/composables/era-date'
import { numeral } from '~/composables/numeral'
import { selectOptions } from '~/composables/select-options'
import { userBillingsStoreKey } from '~/composables/stores/use-user-billings-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useJobPolling } from '~/composables/use-job-polling'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useUserBillingFileDownloader } from '~/composables/use-user-billing-file-downloader'
import { useUsers } from '~/composables/use-users'
import { auth } from '~/middleware/auth'
import { DateLike } from '~/models/date'
import { UserBilling } from '~/models/user-billing'
import { Api } from '~/services/api/core'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'
import { ConfirmDialogParams } from '~/services/confirm-dialog-service'
import { $datetime } from '~/services/datetime-service'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<Omit<UserBillingsApi.GetIndexParams, 'isTransacted' | 'isDeposited' | 'userId'>>

type Action = ''
  | 'register-deposit-date'
  | 'delete-deposit-date'
  | 'create-withdrawal-transactions'
  | 'download-invoices'
  | 'download-receipts'
  | 'download-notices'
  | 'download-statements'

export default defineComponent({
  name: 'UserBillingsIndexPage',
  middleware: [auth(Permission.listUserBillings)],
  setup: () => {
    const userBillingsStore = useInjected(userBillingsStoreKey)
    const userBillingsState = userBillingsStore.state
    const options = dataTableOptions<UserBilling>({
      content: '利用者請求',
      headers: appendHeadersCommonProperty([
        { text: 'サービス提供年月', value: 'providedIn', width: '13%' },
        { text: '請求年月日', value: 'issuedOn', width: '13%' },
        { text: '利用者', value: 'user', width: '13%' },
        { text: '事業所', value: 'office', width: '12%' },
        { text: '合計金額', value: 'totalAmount', align: 'end', width: '11%' },
        { text: '支払方法', value: 'paymentMethod', width: '12%' },
        { text: '請求結果', value: 'result', width: '13%' },
        { text: '入金日', value: 'depositedAt', width: '13%' }
      ]),
      itemLink: ({ id }) => `/user-billings/${id}`,
      itemLinkPermissions: [Permission.updateUserBillings]
    })
    const { form, refresh, submit } = useIndexBindings({
      onQueryChange: params => {
        // itemsPerPage が指定されている場合のみ検索を実行する
        if (params.itemsPerPage) {
          userBillingsStore.getIndex(params)
        }
      },
      pagination: userBillingsState.pagination,
      parseQuery: query => parseRouteQuery<QueryParams>(query, {
        ...Api.getIndexParamOptions,
        officeId: { type: Number, default: '' },
        providedIn: { type: String, default: undefined },
        issuedIn: { type: String, default: undefined },
        paymentMethod: { type: Number, default: '' },
        usedService: { type: Number, default: '' },
        result: { type: Number, default: '' }
      }),
      restoreQueryParams: () => userBillingsState.queryParams.value
    })
    // 検索結果
    const hasTooManyResults = computed(() => {
      return userBillingsState.pagination.value.count
        ? (userBillingsState.pagination.value.itemsPerPage ?? 0) < userBillingsState.pagination.value.count
        : false
    })
    const userBillings = computed(() => hasTooManyResults.value ? [] : userBillingsState.userBillings.value)

    // 入金日登録／削除、帳票ダウンロード
    const { isAuthorized, permissions } = useAuth()
    const data = reactive({
      action: '' as Action,
      selected: [] as UserBilling[],
      alertTexts: [] as string[]
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
        { text: '入金日を登録する', value: 'register-deposit-date', permissions: [Permission.updateUserBillings] },
        { text: '入金日を削除する', value: 'delete-deposit-date', permissions: [Permission.updateUserBillings] },
        {
          text: '全銀ファイルを作成する',
          value: 'create-withdrawal-transactions',
          permissions: [Permission.createWithdrawalTransactions]
        },
        { text: '請求書をダウンロードする', value: 'download-invoices', permissions: [Permission.viewUserBillings] },
        { text: '領収書をダウンロードする', value: 'download-receipts', permissions: [Permission.viewUserBillings] },
        { text: '代理受領額通知書をダウンロードする', value: 'download-notices', permissions: [Permission.viewUserBillings] },
        {
          text: '介護サービス利用明細書をダウンロードする',
          value: 'download-statements',
          permissions: [Permission.viewUserBillings]
        }
      ])
      return {
        actions: computed(() => actions.filter(action => isAuthorized.value(action.permissions))),
        paymentMethodOptions: enumerableOptions(PaymentMethod).filter(x => x.value !== PaymentMethod.none),
        usedServiceOptions: enumerableOptions(UserBillingUsedService),
        resultOptions: enumerableOptions(UserBillingResult)
      }
    }
    const useDoAction = () => {
      const { $api, $confirm, $form, $snackbar } = usePlugins()
      const { errors: axiosErrors, progress, withAxios } = useAxios()
      const { execute } = useJobWithNotification()
      const { startJobPolling } = useJobPolling()
      const runAction = (actionText: string, callback: () => Promise<UserBillingsApi.BatchResponse>) => {
        return withAxios(async () => {
          const result = await startJobPolling(async () => {
            return await callback()
          })
          if (result === false) {
            $snackbar.error(`入金日の${actionText}に失敗しました。`)
          } else {
            const { job } = result
            if (job.status === JobStatus.failure) {
              $snackbar.error(`入金日の${actionText}に失敗しました。`)
            } else if (job.status === JobStatus.success) {
              await refresh()
              data.selected = []
              $snackbar.success(`入金日を${actionText}しました。`)
            }
          }
        })
      }
      const cancellation = async (form: UserBillingsApi.DepositCancellationForm) => {
        const params: ConfirmDialogParams = {
          color: colors.critical,
          message: '選択した請求の入金日を削除します。\n\n本当によろしいですか？',
          positive: '削除'
        }
        if (await $confirm.show(params)) {
          await runAction('一括削除', () => $api.userBillings.depositCancellation({ form }))
        }
      }
      const createWithdrawalTransaction = async (form: WithdrawalTransactionsApi.CreateForm) => {
        if (data.selected.some(x => x.user.billingDestination.paymentMethod !== PaymentMethod.withdrawal)) {
          data.alertTexts.splice(0, 1, '支払方法が「口座振替」の利用者のみを選択してください。')
        }
        if (data.selected.some(x => x.result !== UserBillingResult.pending)) {
          data.alertTexts.splice(1, 1, '全銀ファイルの作成対象になったことのない利用者のみを選択してください。')
        }
        if (!data.alertTexts.length) {
          const params: ConfirmDialogParams = {
            color: colors.critical,
            message: '選択した請求の全銀ファイルを作成します。\n\n本当によろしいですか？',
            positive: '作成'
          }
          if (await $confirm.show(params)) {
            return withAxios(() => {
              return $form.submit(() => execute({
                notificationProps: {
                  linkToOnFailure: '/user-billings',
                  text: {
                    progress: '全銀ファイルの作成を準備中です...',
                    success: '全銀ファイルを作成しました',
                    failure: '全銀ファイルの作成に失敗しました'
                  }
                },
                process: () => $api.withdrawalTransactions.create({ form }),
                started: () => { data.selected = [] },
                success: () => { refresh() }
              }))
            })
          }
        }
      }
      watch(() => data.selected, () => {
        clearErrors()
      })
      const createDateConfirmationDialog = <T, > (action: (date: DateLike) => Promise<T>) => {
        const active = ref(false)
        const isActive = computed(() => active.value)
        const show = () => { active.value = true }
        const cancel = () => { active.value = false }
        const run = (date: DateLike) => {
          active.value = false
          return action(date)
        }
        return { isActive, cancel, run, show }
      }
      const dateRegistrationDialog = createDateConfirmationDialog((date: DateLike) => {
        const ids = data.selected.map(x => x.id)
        const form: UserBillingsApi.DepositRegistrationForm = { ids, depositedOn: date }
        return runAction('一括登録', () => $api.userBillings.depositRegistration({ form }))
      })
      const fileDownloadDialog = (() => {
        const dialog = createDateConfirmationDialog<UserBillingsApi.DownloadForm>((date: DateLike) => {
          const ids = data.selected.map(x => x.id)
          return Promise.resolve({ ids, issuedOn: date })
        })
        const fn = ref<((form: UserBillingsApi.DownloadForm) => Promise<void>) | undefined>(undefined)
        const retain = (func: NonNullable<UnwrapRef<typeof fn>>) => {
          if (data.action === 'download-receipts' && data.selected.some(x => x.result !== UserBillingResult.paid)) {
            data.alertTexts.splice(0, 1, '請求結果が「入金済み」の利用者のみを選択してください。')
          } else if (data.action === 'download-invoices' && data.selected.some(x => x.result === UserBillingResult.none)) {
            data.alertTexts.splice(0, 1, '請求結果が「請求なし」ではない利用者のみを選択してください。')
          } else {
            fn.value = func
            dialog.show()
          }
        }
        const run = (date: DateLike) => {
          if (!fn.value) {
            throw new Error('IllegalStateException')
          }
          return dialog.run(date).then(fn.value)
        }
        return { isActive: dialog.isActive, cancel: dialog.cancel, retain, run }
      })()
      const changeAction = () => {
        clearErrors()
      }
      const doAction = async () => {
        const ids = data.selected.map(x => x.id)
        switch (data.action) {
          case 'register-deposit-date':
            dateRegistrationDialog.show()
            break
          case 'delete-deposit-date':
            await cancellation({ ids })
            break
          case 'create-withdrawal-transactions':
            await createWithdrawalTransaction({ userBillingIds: ids })
            break
          case 'download-invoices':
            await fileDownloadDialog.retain(downloader.downloadInvoices)
            break
          case 'download-receipts':
            await fileDownloadDialog.retain(downloader.downloadReceipts)
            break
          case 'download-notices':
            await fileDownloadDialog.retain(downloader.downloadNotices)
            break
          case 'download-statements':
            await fileDownloadDialog.retain(downloader.downloadStatements)
            break
          default:
        }
      }
      const downloader = useUserBillingFileDownloader({ started: () => { data.selected = [] } })
      const errors = computed(() => Object.values(deepmerge(axiosErrors, downloader.errors).value).flat())
      const hasError = computed(() => Object.keys(errors.value).length >= 1)
      const clearErrors = () => {
        data.alertTexts = []
        axiosErrors.value = {}
        downloader.errors.value = {}
      }
      return {
        changeAction,
        clearErrors,
        dateRegistrationDialog,
        doAction,
        errors,
        fileDownloadDialog,
        hasError,
        progress
      }
    }

    return {
      ...toRefs(data),
      ...useBreadcrumbs('userBillings.index'),
      ...useDoAction(),
      ...useOffices({ permission: Permission.listUserBillings, internal: true }),
      ...useUsers({ permission: Permission.listUserBillings }),
      ...useSelectionState(),
      ...useSelectOptions(),
      canUploadWithdrawal: computed(() => isAuthorized.value([permissions.createWithdrawalTransactions])),
      canDownloadWithdrawal: computed(() => isAuthorized.value([permissions.listWithdrawalTransactions])),
      eraDate,
      eraMonth,
      form,
      hasTooManyResults,
      isLoadingUserBillings: userBillingsState.isLoadingUserBillings,
      numberOfLimit: numeral(userBillingsState.pagination.value.itemsPerPage ?? 0),
      numeral,
      options,
      resolvePaymentMethod,
      resolveUserBillingResult,
      submit,
      today: $datetime.now,
      userBillings
    }
  },
  head: () => ({
    title: '利用者請求'
  })
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
