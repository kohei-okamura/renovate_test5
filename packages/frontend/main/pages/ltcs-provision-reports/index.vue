<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page class="page-ltcs-provision-reports-index" :breadcrumbs="breadcrumbs">
    <z-data-table
      data-table
      :items="ltcsProvisionReports"
      :loading="isLoadingLtcsProvisionReports"
      :options="options"
    >
      <template #item.name="{ item }">{{ item.name.displayName }}</template>
      <template #item.transactedIn="{ item }">{{ eraMonth(item.transactedIn) }}</template>
      <template #item.isEnabled="{ item }">{{ resolveUserStatus(item.isEnabled) }}</template>
      <template #item.status="{ item }">{{ resolveLtcsProvisionReportStatus(item.status) }}</template>
      <template #form>
        <v-form @submit.prevent="submit">
          <v-row>
            <v-col class="mt-3 mt-sm-0 py-1" cols="12">
              <v-alert class="text-caption text-sm-body-2 mb-2" type="info" dense>
                事業所を選択して「検索」を押してください。<br>
                さらに絞り込みたい時は、その他の条件を指定してください。
              </v-alert>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="4">
              <z-keyword-filter-autocomplete
                v-model="form.officeId"
                hide-details
                label="事業所 *"
                :clearable="true"
                :items="officeOptions"
                :loading="isLoadingOffices"
                :prepend-icon="$icons.office"
              />
            </v-col>
            <v-col cols="12" sm="4">
              <z-date-field
                v-model="form.providedIn"
                hide-details
                label="サービス提供年月"
                type="month"
                :clearable="false"
                :max="maxProvidedIn"
                :prepend-icon="$icons.month"
              />
            </v-col>
            <v-col cols="12" sm="4">
              <z-select-search-condition
                v-model="form.status"
                hide-details
                label="状態"
                :items="statusOptions"
                :prepend-icon="$icons.statusUnknown"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12" sm="4">
              <z-text-field
                v-model="form.q"
                clearable
                hide-details
                label="キーワード"
                :prepend-icon="$icons.keyword"
              />
            </v-col>
            <v-spacer />
            <v-col cols="12" sm="4">
              <v-btn block color="primary" depressed type="submit">検索</v-btn>
            </v-col>
          </v-row>
        </v-form>
      </template>
    </z-data-table>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, reactive } from '@nuxtjs/composition-api'
import {
  LtcsProvisionReportStatus,
  resolveLtcsProvisionReportStatus
} from '@zinger/enums/lib/ltcs-provision-report-status'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { eraMonth } from '~/composables/era-date'
import { ltcsProvisionReportsIndexStoreKey } from '~/composables/stores/use-ltcs-provision-reports-store'
import { useAuth } from '~/composables/use-auth'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { Api } from '~/services/api/core'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<LtcsProvisionReportsApi.GetIndexParams>

export default defineComponent({
  name: 'LtcsProvisionReportsIndexPage',
  middleware: [auth(Permission.listLtcsProvisionReports)],
  setup () {
    const { $datetime, $routes } = usePlugins()
    const { isAuthorized } = useAuth()
    const ltcsProvisionReportsStore = useInjected(ltcsProvisionReportsIndexStoreKey)
    const today = $datetime.now
    const maxProvidedIn = today.plus({ months: 3 }).toFormat(ISO_MONTH_FORMAT)
    const searchedCondition: Partial<Omit<QueryParams, 'q'>> = reactive({
      officeId: undefined,
      providedIn: $routes.query.value.providedIn as string ?? today.toFormat(ISO_MONTH_FORMAT)
    })
    const options = dataTableOptions<{ userId: number }>({
      content: '介護保険サービス予実',
      headers: appendHeadersCommonProperty([
        { text: '利用者名', value: 'name' },
        { text: '被保険者証番号', value: 'insNumber' },
        { text: '利用者の状態', value: 'isEnabled' },
        { text: '予実の状態', value: 'status' }
      ]),
      itemLink: ({ userId }) => {
        const officeId = searchedCondition.officeId ?? $routes.query.value.officeId
        const providedIn = searchedCondition.providedIn
        return `/ltcs-provision-reports/${officeId}/${userId}/${providedIn}`
      },
      itemLinkPermissions: [Permission.updateLtcsProvisionReports],
      noDataText: computed(() => !searchedCondition.officeId ? '事業所を選択してください' : undefined)
    })
    const { form, submit } = useIndexBindings({
      onQueryChange: params => ltcsProvisionReportsStore.getIndex(params),
      pagination: ltcsProvisionReportsStore.state.pagination,
      parseQuery: query => parseRouteQuery<QueryParams>(query, {
        ...Api.getIndexParamOptions,
        officeId: { type: Number, default: '' },
        providedIn: { type: String, default: today.toFormat(ISO_MONTH_FORMAT) },
        status: { type: Number, default: '' },
        q: { type: String, default: '' }
      }),
      restoreQueryParams: () => ltcsProvisionReportsStore.state.queryParams.value
    })
    const resolveUserStatus = (isEnabled: boolean) => isEnabled ? '利用中' : '利用終了'
    const canLtcsProvisionReport = isAuthorized.value([Permission.updateLtcsProvisionReports])
    return {
      ...ltcsProvisionReportsStore.state,
      ...useBreadcrumbs('ltcsProvisionReports.index'),
      ...useOffices({ permission: Permission.listLtcsProvisionReports, internal: true }),
      eraMonth,
      canLtcsProvisionReport,
      form,
      maxProvidedIn,
      options,
      resolveLtcsProvisionReportStatus,
      resolveUserStatus,
      statusOptions: enumerableOptions(LtcsProvisionReportStatus),
      submit: () => {
        searchedCondition.officeId = form.officeId
        searchedCondition.providedIn = form.providedIn
        submit()
      }
    }
  },
  head: () => ({
    title: '介護保険サービス予実'
  })
})
</script>
