<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-own-expense-programs-index :breadcrumbs="breadcrumbs">
    <z-data-table
      :items="ownExpensePrograms"
      :items-per-page-props="{
        currentValue: pagination.itemsPerPage,
        onChange: changeItemsPerPage
      }"
      :loading="isLoadingOwnExpensePrograms"
      :options="tableOptions"
    >
      <template #form>
        <v-form @submit.prevent="submit">
          <v-row class="pa-md-0 pt-md-3 px-md-3" :dense="$vuetify.breakpoint.smAndUp">
            <v-col class="pa-md-0 pt-sm-4" cols="12" md="5">
              <z-text-field
                v-model="form.q"
                clearable
                hide-details
                label="キーワード"
                :prepend-icon="$icons.keyword"
              />
            </v-col>
            <v-col class="pa-md-0 pt-sm-4 pl-md-3" cols="12" md="4">
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
            <v-col class="pa-md-0 pt-sm-3 pl-md-6" cols="12" md="3">
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
      v-if="isAuthorized([permissions.createOwnExpensePrograms])"
      bottom
      data-fab
      fixed
      nuxt
      right
      to="/own-expense-programs/new"
      :icon="$icons.add"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dataTableOptions } from '~/composables/data-table-options'
import { ownExpenseProgramsStoreKey } from '~/composables/stores/use-own-expense-programs-store'
import { useAuth } from '~/composables/use-auth'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { auth } from '~/middleware/auth'
import { OwnExpenseProgram } from '~/models/own-expense-program'
import { Api } from '~/services/api/core'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<OwnExpenseProgramsApi.GetIndexParams>

export default defineComponent({
  name: 'OwnExpenseProgramsIndexPage',
  middleware: [auth(Permission.listOwnExpensePrograms)],
  setup () {
    const { isAuthorized, permissions } = useAuth()
    const style = useCssModule()
    const ownExpenseProgramsStore = useInjected(ownExpenseProgramsStoreKey)
    const tableOptions = dataTableOptions<OwnExpenseProgram>({
      content: '自費サービス',
      headers: [
        { class: style.name, sortable: false, text: '自費サービス名', value: 'name' }
      ],
      itemLink: x => `/own-expense-programs/${x.id}`,
      itemLinkPermissions: [permissions.viewOwnExpensePrograms]
    })
    return {
      ...ownExpenseProgramsStore.state,
      ...useBreadcrumbs('ownExpensePrograms.index'),
      ...useIndexBindings({
        onQueryChange: params => ownExpenseProgramsStore.getIndex(params),
        pagination: ownExpenseProgramsStore.state.pagination,
        parseQuery: query => parseRouteQuery<QueryParams>(query, {
          ...Api.getIndexParamOptions,
          officeId: { type: Number, default: '' },
          q: { type: String, default: '' }
        }),
        restoreQueryParams: () => ownExpenseProgramsStore.state.queryParams.value
      }),
      ...useOffices({ permission: Permission.listOwnExpensePrograms, internal: true }),
      isAuthorized,
      permissions,
      tableOptions
    }
  },
  head: () => ({
    title: '自費サービス'
  })
})
</script>

<style lang="scss" module>
.name {
  width: auto;
}
</style>
