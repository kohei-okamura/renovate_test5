<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-staffs-index :breadcrumbs="breadcrumbs">
    <z-data-table
      :items="staffs"
      :items-per-page-props="{
        currentValue: pagination.itemsPerPage,
        onChange: changeItemsPerPage
      }"
      :loading="isLoadingStaffs"
      :options="options"
    >
      <template #item.name="{ item }">{{ item.name.displayName }}</template>
      <template #item.phoneticName="{ item }">{{ item.name.phoneticDisplayName }}</template>
      <template #item.status="{ item }">{{ resolveStaffStatus(item.status) }}</template>
      <template #item.sex="{ item }">{{ resolveSex(item.sex) }}</template>
      <template #item.employeeNumber="{ item }">{{ item.employeeNumber }}</template>
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
              <z-select
                v-model="form.status"
                hide-details
                label="状態"
                multiple
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
    <template v-if="isAuthorized([permissions.createStaffs])">
      <z-fab
        ref="fab"
        bottom
        data-fab
        fixed
        nuxt
        right
        :icon="$icons.add"
        @click="openDialog"
      />
      <z-invitation-form
        ref="invitationForm"
        :dialog="dialog"
        :errors="errors"
        :progress="progress"
        :value="invitation"
        @submit="invite"
        @update:dialog="toggleDialog"
      />
    </template>
  </z-page>
</template>

<script lang="ts">
import { defineComponent, reactive, toRefs } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveSex } from '@zinger/enums/lib/sex'
import { resolveStaffStatus, StaffStatus } from '@zinger/enums/lib/staff-status'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { staffsStoreKey } from '~/composables/stores/use-staffs-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useDialogBindings } from '~/composables/use-dialog-bindings'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { Staff } from '~/models/staff'
import { Api } from '~/services/api/core'
import { InvitationsApi } from '~/services/api/invitations-api'
import { StaffsApi } from '~/services/api/staffs-api'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type InvitationForm = Partial<InvitationsApi.Form>
type QueryParams = Required<StaffsApi.GetIndexParams>

export default defineComponent({
  name: 'StaffsIndexPage',
  middleware: [auth(Permission.listStaffs)],
  setup () {
    const auth = useAuth()
    const staffsStore = useInjected(staffsStoreKey)

    const useInvitationDialog = () => {
      const { $alert, $api, $snackbar } = usePlugins()
      const { errors, progress, withAxios } = useAxios()
      const dialog = useDialogBindings()
      dialog.disableRouterBack()
      const data = reactive({
        invitation: {} as InvitationForm
      })
      const invite = (form: InvitationForm) => withAxios(
        async () => {
          await $api.invitations.create({ form })
          dialog.closeDialog()
          $snackbar.success('招待を送信しました。')
          data.invitation = {
            emails: [],
            officeIds: [],
            officeGroupIds: [],
            roleIds: []
          }
        },
        (error: Error) => $alert.error('招待に失敗しました。', error.stack)
      )
      return {
        ...dialog,
        ...toRefs(data),
        errors,
        invite,
        progress
      }
    }

    const options = dataTableOptions<Staff>({
      content: 'スタッフ',
      headers: appendHeadersCommonProperty([
        { text: 'スタッフ名', value: 'name', width: '26%' },
        { text: '氏名：フリガナ', value: 'phoneticName', width: '26%' },
        { text: '状態', value: 'status', width: '18%' },
        { text: '性別', value: 'sex', width: '12%' },
        { text: '社員番号', value: 'employeeNumber', width: '18%' }
      ]),
      itemLink: x => `/staffs/${x.id}`,
      itemLinkPermissions: [auth.permissions.viewStaffs]
    })

    return {
      ...auth,
      ...staffsStore.state,
      ...useBreadcrumbs('staffs.index'),
      ...useIndexBindings({
        onQueryChange: params => staffsStore.getIndex(params),
        pagination: staffsStore.state.pagination,
        parseQuery: query => parseRouteQuery<QueryParams>(query, {
          ...Api.getIndexParamOptions,
          officeId: { type: Number, default: '' },
          q: { type: String, default: '' },
          status: { type: Array, default: [StaffStatus.active], map: x => +x }
        }),
        restoreQueryParams: () => staffsStore.state.queryParams.value
      }),
      ...useOffices({ permission: Permission.listStaffs, internal: true }),
      ...useInvitationDialog(),
      options,
      resolveSex,
      resolveStaffStatus,
      statusOptions: enumerableOptions(StaffStatus)
    }
  },
  head: () => ({
    title: 'スタッフ'
  })
})
</script>
