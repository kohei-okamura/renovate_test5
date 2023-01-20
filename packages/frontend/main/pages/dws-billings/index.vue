<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-dws-billing-index :breadcrumbs="breadcrumbs">
    <z-data-table
      :items="dwsBillings"
      :items-per-page-props="{
        currentValue: pagination.itemsPerPage,
        onChange: changeItemsPerPage
      }"
      :loading="isLoadingDwsBillings"
      :options="options"
    >
      <template #item.office="{ item }">{{ item.office.name }}</template>
      <template #item.transactedIn="{ item }">{{ eraMonth(item.transactedIn) }}</template>
      <template #item.status="{ item }">{{ resolveDwsBillingStatus(item.status) }}</template>
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
    <z-fab bottom fixed nuxt right to="/dws-billings/new" :icon="$icons.add" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { DwsBillingStatus, resolveDwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { eraMonth } from '~/composables/era-date'
import { dwsBillingsStoreKey } from '~/composables/stores/use-dws-billings-store'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { auth } from '~/middleware/auth'
import { DwsBilling } from '~/models/dws-billing'
import { Api } from '~/services/api/core'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<DwsBillingsApi.GetIndexParams>

export default defineComponent({
  name: 'DwsBillingsIndexPage',
  middleware: [auth(Permission.listBillings)],
  setup () {
    const dwsBillingsStore = useInjected(dwsBillingsStoreKey)
    const style = useCssModule()
    const options = dataTableOptions<DwsBilling>({
      content: '請求',
      headers: [
        { class: style.office, sortable: false, text: '事業所名', value: 'office' },
        { class: style.transactedIn, sortable: false, text: '処理対象年月', value: 'transactedIn' },
        { class: style.status, sortable: false, text: '状態', value: 'status' }
      ],
      itemLink: x => `/dws-billings/${x.id}`
    })
    const indexBindings = useIndexBindings({
      onQueryChange: params => dwsBillingsStore.getIndex({ ...params, desc: true }),
      pagination: dwsBillingsStore.state.pagination,
      parseQuery: query => parseRouteQuery<QueryParams>(query, {
        ...Api.getIndexParamOptions,
        officeId: { type: Number, default: '' },
        statuses: {
          type: Array,
          default: [DwsBillingStatus.checking, DwsBillingStatus.ready, DwsBillingStatus.fixed],
          map: x => +x
        },
        start: { type: String },
        end: { type: String }
      }),
      restoreQueryParams: () => dwsBillingsStore.state.queryParams.value
    })
    return {
      ...dwsBillingsStore.state,
      ...indexBindings,
      ...useBreadcrumbs('dwsBillings.index'),
      ...useOffices({ permission: Permission.listBillings, internal: true }),
      eraMonth,
      options,
      statusOptions: enumerableOptions(DwsBillingStatus),
      resolveDwsBillingStatus
    }
  },
  head: () => ({
    title: '障害福祉サービス請求'
  })
})
</script>

<style lang="scss" module>
.rangeInput {
  // icon(32px) + padding 12px
  max-width: calc(50% - 44px);
}

.rangeSeparator {
  margin: auto 0;
}

.office {
  width: auto;
}

.transactedIn {
  width: 24%;
}

.status {
  width: 20%;
}
</style>
