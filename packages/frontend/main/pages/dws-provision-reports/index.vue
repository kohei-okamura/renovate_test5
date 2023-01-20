<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page class="page-dws-provision-reports-index" :breadcrumbs="breadcrumbs">
    <z-data-table
      data-table
      :item-class="itemClass"
      :items="dwsProvisionReports"
      :loading="isLoadingDwsProvisionReports"
      :options="options"
    >
      <template #item.name="{ item }">{{ item.name.displayName }}</template>
      <template #item.transactedIn="{ item }">{{ eraMonth(item.transactedIn) }}</template>
      <template #item.isEnabled="{ item }">{{ resolveUserStatus(item.isEnabled) }}</template>
      <template #item.status="{ item }">
        <div class="align-center d-flex">
          <v-icon class="mr-1" small :color="statusIconColors[item.status]">
            {{ resolveProvisionReportStatusIcon(item) }}
          </v-icon>
          <span style="line-height: 1">{{ resolveDwsProvisionReportStatus(item.status) }}</span>
        </div>
      </template>
      <template #form>
        <v-form @submit.prevent="submit">
          <v-row>
            <v-col class="mt-3 mt-sm-0 py-1" cols="12">
              <v-alert class="text-caption text-sm-body-2 mb-2" dense type="info">
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
  DwsProvisionReportStatus,
  resolveDwsProvisionReportStatus
} from '@zinger/enums/lib/dws-provision-report-status'
import { Permission } from '@zinger/enums/lib/permission'
import { colors } from '~/colors'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { eraMonth } from '~/composables/era-date'
import { dwsProvisionReportsIndexStoreKey } from '~/composables/stores/use-dws-provision-reports-store'
import { useAuth } from '~/composables/use-auth'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { resolveProvisionReportStatusIcon } from '~/composables/use-provision-report-status-icon'
import { auth } from '~/middleware/auth'
import { ISO_MONTH_FORMAT } from '~/models/date'
import { DwsProvisionReportDigest } from '~/models/dws-provision-report-digest'
import { Api } from '~/services/api/core'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<DwsProvisionReportsApi.GetIndexParams>
export default defineComponent({
  name: 'DwsProvisionReportsIndexPage',
  middleware: [auth(Permission.listDwsProvisionReports)],
  setup () {
    const { $datetime, $routes } = usePlugins()
    const { isAuthorized } = useAuth()
    const dwsProvisionReportsStore = useInjected(dwsProvisionReportsIndexStoreKey)
    const today = $datetime.now
    const maxProvidedIn = today.plus({ months: 3 }).toFormat(ISO_MONTH_FORMAT)
    const searchedCondition: Partial<Omit<QueryParams, 'q'>> = reactive({
      officeId: undefined,
      providedIn: $routes.query.value.providedIn as string ?? today.toFormat(ISO_MONTH_FORMAT)
    })
    const options = dataTableOptions<DwsProvisionReportDigest>({
      content: '障害福祉サービス予実',
      headers: appendHeadersCommonProperty([
        { text: '利用者名', value: 'name' },
        { text: '受給者証番号', value: 'dwsNumber' },
        { text: '利用者の状態', value: 'isEnabled' },
        { text: '予実の状態', value: 'status' }
      ]),
      itemLink: ({ userId }) => {
        const officeId = searchedCondition.officeId ?? $routes.query.value.officeId
        const providedIn = searchedCondition.providedIn
        return `/dws-provision-reports/${officeId}/${userId}/${providedIn}`
      },
      itemLinkPermissions: [Permission.updateDwsProvisionReports],
      noDataText: computed(() => !searchedCondition.officeId ? '事業所を選択してください' : undefined)
    })
    const { form, submit } = useIndexBindings({
      onQueryChange: params => dwsProvisionReportsStore.getIndex(params),
      pagination: dwsProvisionReportsStore.state.pagination,
      parseQuery: query => parseRouteQuery<QueryParams>(query, {
        ...Api.getIndexParamOptions,
        officeId: { type: Number, default: '' },
        providedIn: { type: String, default: today.toFormat(ISO_MONTH_FORMAT) },
        status: { type: Number, default: '' },
        q: { type: String, default: '' }
      }),
      restoreQueryParams: () => dwsProvisionReportsStore.state.queryParams.value
    })
    const itemClass = (x: DwsProvisionReportDigest) => x.status === DwsProvisionReportStatus.fixed
      ? 'inactive'
      : ''
    const statusIconColors = {
      [DwsProvisionReportStatus.notCreated]: colors.unavailable,
      [DwsProvisionReportStatus.inProgress]: colors.inProgress,
      [DwsProvisionReportStatus.fixed]: colors.done
    }
    const resolveUserStatus = (isEnabled: boolean) => isEnabled ? '利用中' : '利用終了'
    const canDwsProvisionReport = isAuthorized.value([Permission.updateDwsProvisionReports])
    return {
      ...dwsProvisionReportsStore.state,
      ...useBreadcrumbs('dwsProvisionReports.index'),
      ...useOffices({ permission: Permission.listDwsProvisionReports, internal: true }),
      eraMonth,
      canDwsProvisionReport,
      form,
      itemClass,
      maxProvidedIn,
      options,
      statusIconColors,
      statusOptions: enumerableOptions(DwsProvisionReportStatus),
      resolveDwsProvisionReportStatus,
      resolveProvisionReportStatusIcon,
      resolveUserStatus,
      submit: () => {
        searchedCondition.officeId = form.officeId
        searchedCondition.providedIn = form.providedIn
        submit()
      }
    }
  },
  head: () => ({
    title: '障害福祉サービス予実'
  })
})
</script>
