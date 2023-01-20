<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page class="page-users-index" :breadcrumbs="breadcrumbs">
    <z-data-table
      :items="users"
      :items-per-page-props="{
        currentValue: pagination.itemsPerPage,
        onChange: changeItemsPerPage
      }"
      :loading="isLoadingUsers"
      :options="tableOptions"
    >
      <template #item.name="{ item }">{{ item.name.displayName }}</template>
      <template #item.phoneticName="{ item }">{{ item.name.phoneticDisplayName }}</template>
      <template #item.status="{ item }">{{ resolveUserStatus(item.isEnabled) }}</template>
      <template #item.sex="{ item }">{{ resolveSex(item.sex) }}</template>
      <template #item.birthday="{ item }">{{ eraDate(item.birthday, 'short') }}</template>
      <template #form>
        <v-form @submit.prevent="submit">
          <v-row class="pa-md-0 pt-md-3 px-md-3" :dense="$vuetify.breakpoint.smAndUp">
            <v-col class="pa-md-0 pt-sm-4" cols="12" md="4">
              <z-text-field
                v-model="form.q"
                clearable
                hide-details
                label="キーワード"
                :prepend-icon="$icons.keyword"
              />
            </v-col>
            <v-col class="pa-md-0 pt-sm-4 pl-md-3" cols="12" md="3">
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
            <v-col class="pa-md-0 pt-sm-4 pl-md-3" cols="12" md="3">
              <z-select-search-condition
                v-model="form.isEnabled"
                hide-details
                label="状態"
                :items="statusOptions"
                :prepend-icon="$icons.statusUnknown"
              />
            </v-col>
            <v-col class="pa-md-0 pt-sm-3 pl-md-6" cols="12" md="2">
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
      v-if="isAuthorized([permissions.createUsers])"
      bottom
      data-fab
      fixed
      nuxt
      right
      to="/users/new"
      :icon="$icons.add"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveSex } from '@zinger/enums/lib/sex'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { eraDate } from '~/composables/era-date'
import { usersStoreKey } from '~/composables/stores/use-users-store'
import { useAuth } from '~/composables/use-auth'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { resolveUserStatus } from '~/composables/use-user-status-resolver'
import { auth } from '~/middleware/auth'
import { User } from '~/models/user'
import { Api } from '~/services/api/core'
import { UsersApi } from '~/services/api/users-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type QueryParams = Required<UsersApi.GetIndexParams>

export default defineComponent({
  name: 'UsersIndexPage',
  middleware: [auth(Permission.listUsers)],
  setup () {
    const { isAuthorized, permissions } = useAuth()
    const usersStore = useInjected(usersStoreKey)
    const tableOptions = dataTableOptions<User>({
      content: '利用者',
      headers: appendHeadersCommonProperty([
        { text: '利用者名', value: 'name', width: '26%' },
        { text: '氏名：フリガナ', value: 'phoneticName', width: '26%' },
        { text: '状態', value: 'status', width: '17%' },
        { text: '性別', value: 'sex', width: '12%' },
        { text: '生年月日', value: 'birthday', width: '19%' }
      ]),
      itemLink: x => `/users/${x.id}`,
      itemLinkPermissions: [permissions.viewUsers]
    })
    const statusOptions = [
      { text: '利用中', value: true },
      { text: '利用終了', value: false }
    ]
    return {
      ...usersStore.state,
      ...useBreadcrumbs('users.index'),
      ...useIndexBindings({
        onQueryChange: params => usersStore.getIndex(params),
        pagination: usersStore.state.pagination,
        parseQuery: query => parseRouteQuery<QueryParams>(query, {
          ...Api.getIndexParamOptions,
          isEnabled: { type: Boolean, default: true },
          officeId: { type: Number, default: '' },
          q: { type: String, default: '' }
        }),
        restoreQueryParams: () => usersStore.state.queryParams.value
      }),
      ...useOffices({ permission: Permission.listUsers, internal: true }),
      eraDate,
      isAuthorized,
      permissions,
      resolveSex,
      resolveUserStatus,
      statusOptions,
      tableOptions
    }
  },
  head: () => ({
    title: '利用者'
  })
})
</script>
