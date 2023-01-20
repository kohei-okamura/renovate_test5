<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-withdrawal-transactions-index :breadcrumbs="breadcrumbs">
    <z-data-table
      clickable
      data-table
      :items="withdrawalTransactions"
      :loading="isLoadingWithdrawalTransactions"
      :options="options"
      @click:row="download"
    >
      <template #item.createdAt="{ item }">
        <v-icon class="mr-2">{{ $icons.document }}</v-icon>
        <span>{{ eraDate(item.createdAt, 'short') }}</span>
      </template>
      <template #item.transactions="{ item }">{{ numeral(item.items.length) }}件</template>
      <template #form>
        <v-form @submit.prevent="submit">
          <v-row>
            <v-spacer />
            <v-col cols="12" sm="8" md="6" lg="4" xl="3">
              <z-flex class="align-center">
                <v-icon class="mr-2">{{ $icons.dateRange }}</v-icon>
                <z-flex-grow>
                  <z-date-field
                    v-model="form.start"
                    hide-details
                    label="作成日（開始）"
                    :prepend-icon="$vuetify.breakpoint.xsOnly ? $icons.blank : ''"
                  />
                </z-flex-grow>
                <z-flex-shrink class="mx-1 text-center" cols="1">〜</z-flex-shrink>
                <z-flex-grow>
                  <z-date-field
                    v-model="form.end"
                    hide-details
                    label="作成日（終了）"
                  />
                </z-flex-grow>
              </z-flex>
            </v-col>
            <v-col cols="12" sm="4" md="3" lg="2" xl="2">
              <v-btn block color="primary" depressed type="submit">検索</v-btn>
            </v-col>
          </v-row>
        </v-form>
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
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { eraDate } from '~/composables/era-date'
import { numeral } from '~/composables/numeral'
import { useWithdrawalTransactionsStore } from '~/composables/stores/use-withdrawal-transactions-store'
import { useAxios } from '~/composables/use-axios'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { ItemsPerPageValuesStandard } from '~/models/items-per-page'
import { WithdrawalTransaction } from '~/models/withdrawal-transaction'
import { Api } from '~/services/api/core'
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<WithdrawalTransactionsApi.GetIndexParams>

export default defineComponent({
  name: 'WithdrawalTransactionsIndexPage',
  middleware: [auth(Permission.listWithdrawalTransactions)],
  setup: () => {
    const withdrawalTransactionsStore = useWithdrawalTransactionsStore()
    const withdrawalTransactionsState = withdrawalTransactionsStore.state
    const options = dataTableOptions<WithdrawalTransaction>({
      content: '全銀ファイル',
      headers: appendHeadersCommonProperty([
        { text: '作成日', value: 'createdAt', width: '50%' },
        { text: '口座振替件数', value: 'transactions', align: 'end', width: '50%' }
      ])
    })
    const { changeItemsPerPage, form, paginate, submit } = useIndexBindings({
      onQueryChange: params => {
        // itemsPerPage が指定されている場合のみ検索を実行する
        if (params.itemsPerPage) {
          withdrawalTransactionsStore.getIndex(params)
        }
      },
      pagination: withdrawalTransactionsState.pagination,
      parseQuery: query => parseRouteQuery<QueryParams>(query, {
        ...Api.getIndexParamOptions,
        start: { type: String, default: undefined },
        end: { type: String, default: undefined }
      }),
      restoreQueryParams: () => withdrawalTransactionsState.queryParams.value
    })

    // 帳票ダウンロード
    const useFileDownload = (): { [key in keyof WithdrawalTransactionsApi.Download]: typeof download } => {
      const { $api, $download, $form } = usePlugins()
      const { withAxios } = useAxios()
      const { execute } = useJobWithNotification()
      const download = (withdrawalTransaction: WithdrawalTransaction) => withAxios(() => {
        const form = { id: withdrawalTransaction.id }
        return $form.submit(() => execute({
          notificationProps: {
            text: {
              progress: '全銀ファイルのダウンロードを準備中です...',
              success: '全銀ファイルのダウンロードを開始します',
              failure: '全銀ファイルのダウンロードに失敗しました'
            }
          },
          process: () => $api.withdrawalTransactions.download({ form }),
          success: job => {
            $download.uri(job.data.uri, job.data.filename)
          }
        }))
      })

      return {
        download
      }
    }

    return {
      ...useBreadcrumbs('userBillings.download'),
      ...useFileDownload(),
      ...withdrawalTransactionsState,
      changeItemsPerPage,
      eraDate,
      form,
      isLoadingWithdrawalTransactions: withdrawalTransactionsState.isLoadingWithdrawalTransactions,
      itemsPerPageOptionValues: ItemsPerPageValuesStandard,
      numeral,
      options,
      paginate,
      submit
    }
  },
  head: () => ({
    title: '全銀ファイル'
  })
})
</script>
