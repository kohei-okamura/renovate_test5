<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-ltcs-billing-index :breadcrumbs="breadcrumbs">
    <z-data-table
      :items="ltcsBillings"
      :items-per-page-props="{
        currentValue: pagination.itemsPerPage,
        onChange: changeItemsPerPage
      }"
      :loading="isLoadingLtcsBillings"
      :options="options"
    >
      <template #item.office="{ item }">{{ item.office.name }}</template>
      <template #item.transactedIn="{ item }">{{ eraMonth(item.transactedIn) }}</template>
      <template #item.status="{ item }">{{ resolveLtcsBillingStatus(item.status) }}</template>
      <template #form>
        <v-form @submit.prevent="submit">
          <v-row>
            <v-col cols="12" sm="6">
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
            <v-col cols="12" sm="6">
              <z-select
                v-model="form.statuses"
                multiple
                hide-details
                label="状態"
                :items="statusOptions"
                :prepend-icon="$icons.statusUnknown"
              />
            </v-col>
            <v-col cols="12" sm="8">
              <v-row class="ma-0">
                <div class="v-input__prepend-outer">
                  <v-icon>{{ $icons.dateRange }}</v-icon>
                </div>
                <v-col class="pa-0" :class="$style.rangeInput" cols="6">
                  <z-date-field v-model="form.start" hide-details label="処理対象年月（開始）" type="month" />
                </v-col>
                <span class="px-2" :class="$style.rangeSeparator">〜</span>
                <v-col class="pa-0" :class="$style.rangeInput" cols="6">
                  <z-date-field v-model="form.end" hide-details label="処理対象年月（終了）" type="month" />
                </v-col>
              </v-row>
            </v-col>
            <v-spacer />
            <v-col class="pl-sm-0" cols="12" sm="4">
              <v-btn block color="primary" depressed type="submit">検索</v-btn>
            </v-col>
          </v-row>
        </v-form>
      </template>
      <template #footer>
        <z-data-table-footer
          :pagination="pagination"
          @update:items-per-page="changeItemsPerPage"
          @update:page="paginate"
        />
      </template>
    </z-data-table>
    <z-fab bottom fixed nuxt right to="/ltcs-billings/new" :icon="$icons.add" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { LtcsBillingStatus, resolveLtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { eraMonth } from '~/composables/era-date'
import { ltcsBillingsStoreKey } from '~/composables/stores/use-ltcs-billings-store'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { auth } from '~/middleware/auth'
import { LtcsBilling } from '~/models/ltcs-billing'
import { Api } from '~/services/api/core'
import { LtcsBillingsApi } from '~/services/api/ltcs-billings-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<LtcsBillingsApi.GetIndexParams>

export default defineComponent({
  name: 'LtcsBillingsIndexPage',
  middleware: [auth(Permission.listBillings)],
  setup () {
    const ltcsBillingsStore = useInjected(ltcsBillingsStoreKey)
    const style = useCssModule()
    const options = dataTableOptions<LtcsBilling>({
      content: '請求',
      headers: [
        { class: 'th-office', sortable: false, text: '事業所名', value: 'office' },
        { class: `${style.thTransactedIn}`, sortable: false, text: '処理対象年月', value: 'transactedIn' },
        { class: `${style.thStatus}`, sortable: false, text: '状態', value: 'status' }
      ],
      itemLink: x => `/ltcs-billings/${x.id}`
    })
    const indexBindings = useIndexBindings({
      onQueryChange: params => ltcsBillingsStore.getIndex({ ...params, desc: true }),
      pagination: ltcsBillingsStore.state.pagination,
      parseQuery: query => parseRouteQuery<QueryParams>(query, {
        ...Api.getIndexParamOptions,
        officeId: { type: Number, default: '' },
        statuses: {
          type: Array,
          default: [LtcsBillingStatus.checking, LtcsBillingStatus.ready, LtcsBillingStatus.fixed],
          map: x => +x
        },
        start: { type: String },
        end: { type: String }
      }),
      restoreQueryParams: () => ltcsBillingsStore.state.queryParams.value
    })
    return {
      ...ltcsBillingsStore.state,
      ...indexBindings,
      ...useBreadcrumbs('ltcsBillings.index'),
      ...useOffices({ permission: Permission.listBillings, internal: true }),
      eraMonth,
      options,
      statusOptions: enumerableOptions(LtcsBillingStatus),
      resolveLtcsBillingStatus
    }
  },
  head: () => ({
    title: '介護保険サービス請求'
  })
})
</script>

<style lang="scss" module>
.rangeInput {
  max-width: calc(50% - 44px);
  // icon(32px) + padding 12px
}

.rangeSeparator {
  margin: auto 0;
}

.thTransactedIn {
  width: 24%;
}

.thStatus {
  width: 20%;
}
</style>
