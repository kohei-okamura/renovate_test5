<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-offices-index :class="$style.root" :breadcrumbs="breadcrumbs">
    <z-data-table
      :items="offices"
      :items-per-page-props="{
        currentValue: pagination.itemsPerPage,
        onChange: changeItemsPerPage
      }"
      :loading="isLoadingOffices"
      :options="options"
    >
      <template #item.purpose="{ item }">{{ resolvePurpose(item.purpose) }}</template>
      <template #item.qualifications="{ item }">
        <div :class="$style.qualificationsWrapper">
          <v-chip v-for="x in item.qualifications" :key="x" label small>{{ resolveQualification(x) }}</v-chip>
        </div>
      </template>
      <template #form>
        <v-form @submit.prevent="submit">
          <v-row class="pa-md-0 px-md-3" :dense="$vuetify.breakpoint.smAndUp">
            <v-col class="pt-sm-4" cols="12" md="4" lg="3">
              <z-text-field
                v-model="form.q"
                clearable
                hide-details
                label="キーワード"
                :prepend-icon="$icons.keyword"
              />
            </v-col>
            <v-col class="pt-sm-4 pl-md-3" cols="12" md="4" lg="2">
              <z-select-search-condition
                v-model="form.prefecture"
                hide-details
                label="都道府県"
                :items="prefectureOptions"
                :prepend-icon="$icons.addr"
              />
            </v-col>
            <v-col class="pt-sm-4 pl-md-3" cols="12" md="4" lg="3">
              <z-select
                v-model="form.status"
                hide-details
                label="状態"
                multiple
                :items="statusOptions"
                :prepend-icon="$icons.statusUnknown"
              />
            </v-col>
            <v-col class="pt-sm-4 pl-lg-3" cols="12" md="4" lg="2">
              <z-select-search-condition
                v-if="showPurpose"
                v-model="form.purpose"
                data-purpose
                hide-details
                label="事業所区分"
                :items="purposeOptions"
                :prepend-icon="$icons.office"
              />
            </v-col>
            <v-spacer />
            <v-col class="pt-sm-4 pl-md-6" cols="12" md="4" lg="2">
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
    <z-fab
      v-if="isAuthorized([permissions.createInternalOffices, permissions.createExternalOffices])"
      bottom
      data-fab
      fixed
      nuxt
      right
      to="/offices/new"
      :icon="$icons.add"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { OfficeQualification, resolveOfficeQualification } from '@zinger/enums/lib/office-qualification'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Permission } from '@zinger/enums/lib/permission'
import { Prefecture } from '@zinger/enums/lib/prefecture'
import { Purpose, resolvePurpose } from '@zinger/enums/lib/purpose'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { officesStoreKey } from '~/composables/stores/use-offices-store'
import { useAuth } from '~/composables/use-auth'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { auth } from '~/middleware/auth'
import { Office } from '~/models/office'
import { Api } from '~/services/api/core'
import { OfficesApi } from '~/services/api/offices-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<OfficesApi.GetIndexParams>

export default defineComponent({
  name: 'OfficesIndexPage',
  middleware: [auth(Permission.listInternalOffices, Permission.listExternalOffices)],
  setup () {
    // const { isAuthorized } = useAuth()
    const officesStore = useInjected(officesStoreKey)
    const options = dataTableOptions<Office>({
      content: '事業所',
      headers: appendHeadersCommonProperty([
        { text: '事業所名', value: 'name', width: 300 },
        { text: '事業者区分', value: 'purpose', width: 100 },
        { text: '指定区分', value: 'qualifications' }
      ]),
      itemLink: x => `/offices/${x.id}`,
      itemLinkPermissions: [Permission.viewInternalOffices, Permission.viewExternalOffices]
    })
    const prefectureOptions = enumerableOptions(Prefecture)
    const resolveQualification = (x: OfficeQualification) => {
      return resolveOfficeQualification(x).replace(/（.*）$/, '')
    }
    // const showPurpose = computed(() => isAuthorized.value([Permission.listInternalOffices]) &&
    //   isAuthorized.value([Permission.listExternalOffices])
    // )
    const showPurpose = computed(() => true)
    return {
      ...officesStore.state,
      ...useAuth(),
      ...useIndexBindings({
        onQueryChange: params => officesStore.getIndex(params),
        pagination: officesStore.state.pagination,
        parseQuery: query => parseRouteQuery<QueryParams>(query, {
          ...Api.getIndexParamOptions,
          prefecture: { type: Number, default: '' },
          q: { type: String, default: '' },
          status: { type: Array, default: [OfficeStatus.inPreparation, OfficeStatus.inOperation], map: x => +x },
          purpose: { type: Number, default: '' }
        }),
        restoreQueryParams: () => officesStore.state.queryParams.value
      }),
      ...useBreadcrumbs('offices.index'),
      options,
      prefectureOptions,
      purposeOptions: enumerableOptions(Purpose).filter(x => x.value !== Purpose.unknown),
      showPurpose,
      resolveQualification,
      resolvePurpose,
      statusOptions: enumerableOptions(OfficeStatus)
    }
  },
  head: () => ({
    title: '事業所'
  })
})
</script>

<style lang="scss" module>
.root {
  table {
    table-layout: fixed;
  }

  .qualificationsWrapper {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

    > *:not(:first-of-type) {
      margin-left: 4px;
    }
  }
}
</style>
