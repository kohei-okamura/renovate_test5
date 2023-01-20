<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div>
    <z-data-table
      data-table
      :class="$style.billingTable"
      :items="userBillings"
      :loading="isLoadingUserBillings"
      :options="options"
    >
      <template #item.providedIn="{ item }">{{ eraMonth(item.providedIn) }}</template>
      <template #item.issuedOn="{ item }">{{ eraDate(item.issuedOn, 'short') }}</template>
      <template #item.totalAmount="{ item }">{{ numeral(item.totalAmount) }}円</template>
      <template #item.result="{ item }">{{ resolveUserBillingResult(item.result) }}</template>
      <template #item.billing="{ item }">
        <v-btn
          v-if="item.result !== UserBillingResult.none"
          color="secondary"
          data-download-button="invoice"
          icon
          @click.stop="()=> fileDownloadDialog.show(item, 'invoice')"
        >
          <v-icon>{{ $icons.download }}</v-icon>
        </v-btn>
      </template>
      <template #item.receipt="{ item }">
        <v-btn
          v-if="item.result === UserBillingResult.paid"
          color="secondary"
          data-download-button="receipt"
          icon
          @click.stop="()=> fileDownloadDialog.show(item, 'receipt')"
        >
          <v-icon>{{ $icons.download }}</v-icon>
        </v-btn>
      </template>
      <template #item.notice="{ item }">
        <v-btn
          v-if="item.dwsItem !== undefined"
          color="secondary"
          data-download-button="notice"
          icon
          @click.stop="() => fileDownloadDialog.show(item, 'notice')"
        >
          <v-icon>{{ $icons.download }}</v-icon>
        </v-btn>
      </template>
      <template #item.statement="{ item }">
        <v-btn
          v-if="!(item.dwsItem === undefined && item.ltcsItem === undefined)"
          color="secondary"
          data-download-button="statement"
          icon
          @click.stop="() => fileDownloadDialog.show(item, 'statement')"
        >
          <v-icon>{{ $icons.download }}</v-icon>
        </v-btn>
      </template>
      <template #footer>
        <z-data-table-footer
          :items-per-page-option-values="itemsPerPageOptionValues"
          :pagination="pagination"
          @update:items-per-page="changeItemsPerPage"
          @update:page="paginate"
        />
      </template>
    </z-data-table>
    <z-date-confirm-dialog
      data-date-confirm-dialog="download"
      message="印字する発行日を選択してください"
      positive-label="ダウンロード"
      :active="fileDownloadDialog.isActive.value"
      @click:negative="fileDownloadDialog.cancel"
      @click:positive="fileDownloadDialog.run"
    />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveUserBillingResult, UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { eraDate, eraMonth } from '~/composables/era-date'
import { numeral } from '~/composables/numeral'
import { useUserBillingsStore } from '~/composables/stores/use-user-billings-store'
import { useIndexBindings } from '~/composables/use-index-binding'
import { DateLike } from '~/models/date'
import { ItemsPerPageValuesStandard } from '~/models/items-per-page'
import { UserId } from '~/models/user'
import { UserBilling } from '~/models/user-billing'
import { Api } from '~/services/api/core'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type FileType = 'invoice' | 'receipt' | 'notice' | 'statement'

type Props = Readonly<{
  userId: UserId
}>

export default defineComponent<Props>({
  name: 'ZUserBillingsView',
  props: {
    userId: { type: Number, required: true }
  },
  setup: (props, context) => {
    const userBillingsStore = useUserBillingsStore()
    const userBillingsState = userBillingsStore.state
    const options = dataTableOptions<UserBilling>({
      content: '利用者請求',
      headers: appendHeadersCommonProperty([
        { text: 'サービス提供年月', value: 'providedIn', width: '16%' },
        { text: '請求年月日', value: 'issuedOn', width: '19%' },
        { text: '合計金額', value: 'totalAmount', align: 'end', width: '17%' },
        { text: '請求結果', value: 'result', width: '17%' },
        { text: '請求書', value: 'billing', align: 'center', width: '7%' },
        { text: '領収書', value: 'receipt', align: 'center', width: '7%' },
        { text: '代理受領額通知書', value: 'notice', align: 'center', width: '7%' },
        { text: '介護サービス利用明細書', value: 'statement', align: 'center', width: '10%' }
      ]),
      itemLink: ({ id }) => `/user-billings/${id}`,
      itemLinkPermissions: [Permission.updateUserBillings]
    })
    const { paginate, changeItemsPerPage } = useIndexBindings({
      onQueryChange: params => userBillingsStore.getIndex({ ...params, userId: props.userId }),
      pagination: userBillingsState.pagination,
      parseQuery: query => parseRouteQuery(query, Api.getIndexParamOptions),
      restoreQueryParams: () => userBillingsState.queryParams.value
    })
    const download = (item: UserBilling, type: FileType) => {
      context.emit(`click:download:${type}`, { ids: [item.id], issuedOn: item.issuedOn })
    }
    const createFileDownloadDialog = () => {
      const billing = ref<UserBilling | undefined>(undefined)
      const type = ref<FileType | undefined>(undefined)
      const active = ref(false)
      const isActive = computed(() => active.value)
      const show = (value: UserBilling, fileType: FileType) => {
        billing.value = value
        type.value = fileType
        active.value = true
      }
      const cancel = () => { active.value = false }
      const run = (date: DateLike) => {
        active.value = false
        if (!(billing.value && type.value)) {
          throw new Error('IllegalStateException')
        }
        // 現状 UserBilling の issuedOn は使っていないので、当該プロパティに指定した日付を設定する
        download({ ...billing.value, issuedOn: date }, type.value)
        billing.value = undefined
      }
      return { isActive, cancel, run, show }
    }

    return {
      ...userBillingsState,
      changeItemsPerPage,
      download,
      eraDate,
      eraMonth,
      fileDownloadDialog: createFileDownloadDialog(),
      itemsPerPageOptionValues: ItemsPerPageValuesStandard,
      isLoadingUserBillings: userBillingsState.isLoadingUserBillings,
      numeral,
      options,
      paginate,
      resolveUserBillingResult,
      UserBillingResult
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/styles.sass';

.billingTable {
  th:nth-last-of-type(-n+4):not(:last-of-type),
  td:nth-last-of-type(-n+4):not(:last-of-type) {
    padding: 0 8px !important;
  }

  th:last-of-type,
  td:last-of-type {
    padding-left: 8px !important;
  }
}

@media #{map-get($display-breakpoints, 'sm-and-down')} {
  .billingTable {
    th,
    td {
      padding: 0 8px !important;
    }
  }
}
</style>
