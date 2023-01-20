<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div>
    <z-data-table
      :selectable="billingStatus !== LtcsBillingStatus.fixed"
      :items="statements"
      :options="tableOptions"
      :selected.sync="selected"
    >
      <template #title>
        <span>サービス提供年月:</span>
        <z-era-month :value="providedIn" />
      </template>
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
                <v-btn color="accent" data-action-button depressed type="submit" :disabled="action === ''">実行</v-btn>
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
                  :items="statusOptions"
                  :prepend-icon="$icons.statusUnknown"
                />
              </v-col>
            </v-row>
          </template>
        </transition>
      </template>
      <template #item.userName="{ item }">{{ item.user.name.displayName }}</template>
      <template #item.dwsNumber="{ item }">{{ item.user.insNumber }}</template>
      <template #item.city="{ item }">{{ item.insurerName }}</template>
      <template #item.statement="{ item }">{{ resolveLtcsBillingStatus(item.status) }}</template>
    </z-data-table>
    <z-progress :value="isShowProgress && progress" />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, ref, toRefs, watch } from '@nuxtjs/composition-api'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { LtcsBillingStatus, resolveLtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { debounce, isEmpty } from '@zinger/helpers'
import toKatakana from 'jaco/fn/toKatakana'
import { colors } from '~/colors'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { eraMonth } from '~/composables/era-date'
import { numeral } from '~/composables/numeral'
import { selectOptions } from '~/composables/select-options'
import { ltcsBillingStoreKey } from '~/composables/stores/use-ltcs-billing-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { useJobPolling } from '~/composables/use-job-polling'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { DateString } from '~/models/date'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { LtcsBillingStatementsApi } from '~/services/api/ltcs-billing-statements-api'
import { ConfirmDialogParams } from '~/services/confirm-dialog-service'

type Props = {
  items: LtcsBillingStatement[]
  providedIn: DateString
}

type Action = ''
  | 'confirm-statements'
  | 'refresh-statements'

export default defineComponent<Props>({
  name: 'ZLtcsBillingStatementListCard',
  props: {
    items: { type: Array, required: true },
    providedIn: { type: String, required: true },
    billingStatus: { type: Number, required: true }
  },
  setup (props: Props) {
    const store = useInjected(ltcsBillingStoreKey)
    const state = reactive({
      keyword: '',
      status: undefined as LtcsBillingStatus | undefined | null
    })

    const updateKeyword = debounce({ wait: 250 }, (value: string | null) => {
      state.keyword = value ?? ''
    })
    const matchWithKeyword = (keywords: string[], statement: LtcsBillingStatement) => {
      const targets = [
        statement.user.name.displayName,
        statement.user.name.phoneticDisplayName,
        statement.insurerName
      ]
      return targets.some(x => keywords.some(keyword => x.includes(keyword)))
    }
    const statements = computed(() => {
      const status = state.status
      const keyword = state.keyword.trim()
      const keywords = isEmpty(keyword) ? [] : [keyword, toKatakana(keyword)]
      return props.items.filter(x => {
        return (isEmpty(status) || x.status === status) && (isEmpty(keyword) || matchWithKeyword(keywords, x))
      })
    })

    const statusOptions = LtcsBillingStatus.values
      .filter(value => value !== LtcsBillingStatus.disabled)
      .map(value => ({ value, text: resolveLtcsBillingStatus(value) }))

    const tableOptions = dataTableOptions<LtcsBillingStatement>({
      content: '明細書',
      headers: appendHeadersCommonProperty([
        { text: '利用者名', value: 'userName' },
        { text: '被保険者証番号', value: 'dwsNumber', width: 120 },
        { text: '市町村名', value: 'city', width: 160 },
        { text: '明細書', value: 'statement', align: 'center', width: 94 }
      ]),
      itemLink: x => `/ltcs-billings/${x.billingId}/bundles/${x.bundleId}/statements/${x.id}`
    })

    const data = reactive({
      action: '' as Action,
      selected: [] as LtcsBillingStatement[]
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
        { text: '請求情報を更新する', value: 'refresh-statements', permissions: [Permission.updateBillings] }
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
        bundleId: number,
        form: LtcsBillingStatementsApi.BulkUpdateStatusForm
      ) => {
        const params: ConfirmDialogParams = {
          color: colors.critical,
          message: '選択した明細書を確定します。\n\n本当によろしいですか？',
          positive: '確定'
        }
        if (await $confirm.show(params)) {
          await withAxios(async () => {
            const result = await startJobPolling(async () => {
              return await $api.ltcsBillingStatements.bulkUpdateStatus({ billingId, bundleId, form })
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
      const refreshStatements = async (
        billingId: number,
        form: LtcsBillingStatementsApi.RefreshForm
      ) => {
        const params: ConfirmDialogParams = {
          color: colors.critical,
          message: '選択した利用者の明細書を最新の予実を使って更新します。\n\n本当によろしいですか？',
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
              process: () => $api.ltcsBillingStatements.refresh({ billingId, form }),
              success: async () => {
                await store.get({ id: billingId })
                data.selected = []
              }
            })).finally(() => (isShowProgress.value = true))
          })
        }
      }
      watch(() => data.selected, () => {
        clearErrors()
      })
      const changeAction = () => {
        clearErrors()
      }
      const doAction = async () => {
        const billingId = data.selected[0].billingId
        const bundleId = data.selected[0].bundleId
        const ids = data.selected.map(x => x.id)
        switch (data.action) {
          case 'confirm-statements':
            await confirmStatements(billingId, bundleId, { ids, status: LtcsBillingStatus.fixed })
            break
          case 'refresh-statements':
            await refreshStatements(billingId, { ids })
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
      eraMonth,
      numeral,
      LtcsBillingStatus,
      resolveLtcsBillingStatus,
      statements,
      statusOptions,
      tableOptions,
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
