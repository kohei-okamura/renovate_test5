<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-attendances-index fluid :breadcrumbs="breadcrumbs">
    <z-data-table
      aria-expanded="true"
      data-data-table
      dense
      :items="selectableAttendances"
      :items-per-page-props="{
        currentValue: pagination.itemsPerPage,
        optionValues: itemsPerPageOptionValues,
        onChange: changeItemsPerPage
      }"
      :loading="isLoadingAttendances"
      :options="options"
      :selectable="isAuthorized([permissions.updateAttendances])"
      :selected.sync="selected"
    >
      <template #item.user="{ item }">{{ resolveUserName(item.userId) }}</template>
      <template #item.firstAssignee="{ item }">{{ resolveStaffName(item.assignees[0].staffId, '（未定）') }}</template>
      <template #item.secondAssignee="{ item }">
        {{ item.headcount === 2 ? resolveStaffName(item.assignees[1].staffId, '（未定）') : '-' }}
      </template>
      <template #item.task="{ item }">
        <z-task-marker :task="item.task" />
      </template>
      <template #item.scheduleDate="{ item }">
        <z-era-date short :value="item.schedule.date" />
        <span>{{ localeDate(item.schedule.date, { weekday: 'narrow' }) }}</span>
      </template>
      <template #item.scheduleTime="{ item }">
        <z-time :value="item.schedule.start" />
        <span>-</span>
        <z-time :value="item.schedule.end" />
      </template>
      <template #item.isConfirmed="{ item }">
        <v-icon v-if="item.isConfirmed" color="primary" small>{{ $icons.confirmed }}</v-icon>
        <span v-else>-</span>
      </template>
      <template #item.assigner="{ item }">{{ resolveStaffName(item.assignerId) }}</template>
      <template #item.office="{ item }">{{ resolveOfficeAbbr(item.officeId) }}</template>
      <template #form>
        <transition mode="out-in" name="fade">
          <v-form
            v-if="isSelected"
            key="selected"
            :class="$style.actionsForm"
            data-action-form
            @submit.prevent="doAction"
          >
            <z-flex class="align-center justify-start">
              <div>
                <v-btn icon large @click.stop="resetSelected">
                  <v-icon>{{ $icons.close }}</v-icon>
                </v-btn>
              </div>
              <div class="text-body-1" :class="$style.actionsMessage">
                {{ numeral(selectedCount) }}件を選択中：
              </div>
              <v-spacer />
              <div :class="$style.actionsSelect">
                <z-select v-model="action" hide-details single-line :items="actions" />
              </div>
              <div :class="$style.actionsButton">
                <v-btn color="accent" data-action-button depressed type="submit" :disabled="action === ''">実行</v-btn>
              </div>
            </z-flex>
          </v-form>
          <v-form v-else key="not-selected" @submit.prevent="submit">
            <v-row>
              <v-col class="pa-3" cols="12" lg="2" md="4" sm="6">
                <z-select-search-condition
                  v-model="form.userId"
                  hide-details
                  label="利用者"
                  :items="userOptions"
                  :loading="isLoadingUsers"
                  :prepend-icon="$icons.user"
                />
              </v-col>
              <v-col class="pa-3" cols="12" lg="2" md="4" sm="6">
                <z-select-search-condition
                  v-model="form.assigneeId"
                  hide-details
                  label="担当スタッフ"
                  :items="staffOptions"
                  :loading="isLoadingStaffs"
                  :prepend-icon="$icons.staff"
                />
              </v-col>
              <v-col class="pa-3" cols="12" lg="2" md="4" sm="6">
                <z-select-search-condition
                  v-model="form.assignerId"
                  hide-details
                  label="管理スタッフ"
                  :items="staffOptions"
                  :loading="isLoadingStaffs"
                  :prepend-icon="$icons.staff"
                />
              </v-col>
              <v-col class="pa-3" cols="12" lg="3" md="4" sm="6">
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
              <v-col class="pa-3" cols="12" lg="3" md="4" sm="6">
                <z-select-search-condition
                  v-model="form.task"
                  hide-details
                  label="勤務実績区分"
                  :items="tasks"
                  :prepend-icon="$icons.category"
                />
              </v-col>
              <v-col class="pa-3" cols="12" lg="2" md="4" sm="6">
                <z-select-search-condition
                  v-model="form.isConfirmed"
                  hide-details
                  label="状態"
                  :items="isConfirmedOptions"
                  :prepend-icon="$icons.statusUnknown"
                />
              </v-col>
              <v-col class="pa-3" cols="12" lg="2" md="3" sm="6">
                <z-select
                  v-model="form.dateRangeType"
                  hide-details
                  label="勤務日"
                  :items="dateRangeTypes"
                  :prepend-icon="$icons.dateRange"
                />
              </v-col>
              <v-fade-transition>
                <v-col v-if="specifyDateRange" class="pa-3" cols="12" lg="4" sm="6">
                  <z-flex class="align-center">
                    <z-flex-grow>
                      <z-date-field
                        v-model="form.start"
                        hide-details
                        label="勤務日（開始）"
                        :prepend-icon="$vuetify.breakpoint.xsOnly ? $icons.blank : ''"
                      />
                    </z-flex-grow>
                    <z-flex-shrink class="mx-1 text-center" cols="1">〜</z-flex-shrink>
                    <z-flex-grow>
                      <z-date-field
                        v-model="form.end"
                        hide-details
                        label="勤務日（終了）"
                      />
                    </z-flex-grow>
                  </z-flex>
                </v-col>
              </v-fade-transition>
              <v-spacer />
              <v-col class="pa-3" cols="12" lg="2" md="2" sm="4">
                <v-btn block color="primary" depressed type="submit">検索</v-btn>
              </v-col>
            </v-row>
          </v-form>
        </transition>
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
    <z-fab
      v-if="isAuthorized([permissions.createAttendances])"
      bottom
      data-fab
      fixed
      nuxt
      right
      to="/attendances/new"
      :icon="$icons.add"
    />
    <z-progress :value="progress" />
    <z-cancel-confirm-dialog
      :active="showCancelDialog"
      :in-progress="progress"
      :message="cancelMessage"
      @click:negative="onClickNegative"
      @click:positive="onClickPositive"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, ref, toRefs, useCssModule } from '@nuxtjs/composition-api'
import { DateRangeType } from '@zinger/enums/lib/date-range-type'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { Permission } from '@zinger/enums/lib/permission'
import { Task } from '@zinger/enums/lib/task'
import { colors } from '~/colors'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dataTableOptions } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { localeDate } from '~/composables/locale-date'
import { numeral } from '~/composables/numeral'
import { selectOptions } from '~/composables/select-options'
import { attendancesStoreKey } from '~/composables/stores/use-attendances-store'
import { useAuth } from '~/composables/use-auth'
import { useIndexBindings } from '~/composables/use-index-binding'
import { useInjected } from '~/composables/use-injected'
import { useJobPolling } from '~/composables/use-job-polling'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { withProgressState } from '~/composables/with-progress-state'
import { auth } from '~/middleware/auth'
import { Attendance } from '~/models/attendance'
import { expandDateRange } from '~/models/date-range'
import { ItemsPerPageValuesLargeNumber } from '~/models/items-per-page'
import { AttendancesApi } from '~/services/api/attendances-api'
import { Api } from '~/services/api/core'
import { ConfirmDialogParams } from '~/services/confirm-dialog-service'
import { parseRouteQuery } from '~/support/router/parse-route-query'

type Form = Partial<AttendancesApi.GetIndexParams & {
  dateRangeType: DateRangeType
}>

type Action = 'confirm' | 'cancel' | ''

type QueryParams = Required<AttendancesApi.GetIndexParams> & {
  dateRangeType: DateRangeType
}

export default defineComponent({
  name: 'AttendancesIndexPage',
  middleware: [auth(Permission.listAttendances)],
  setup () {
    const { $api, $confirm, $snackbar } = usePlugins()
    const { startJobPolling } = useJobPolling()
    const { isAuthorized, permissions } = useAuth()
    const style = useCssModule()
    const data = reactive({
      action: '' as Action,
      selected: [] as Attendance[]
    })
    const attendancesStore = useInjected(attendancesStoreKey)
    const selectableAttendances = computed(
      () => attendancesStore.state.attendances.value.map(x => {
        return { ...x, isSelectable: !(x.isCanceled || x.isConfirmed) }
      })
    )
    const { form, paginate, changeItemsPerPage, refresh, submit } = useIndexBindings<Form>({
      onQueryChange: params => attendancesStore.getIndex(params),
      pagination: attendancesStore.state.pagination,
      parseQuery: query => {
        const xs = parseRouteQuery<QueryParams>(query, {
          ...Api.getIndexParamOptions,
          userId: { type: Number, default: '' },
          assigneeId: { type: Number, default: '' },
          assignerId: { type: Number, default: '' },
          officeId: { type: Number, default: '' },
          task: { type: Number, default: '' },
          isConfirmed: { type: Boolean, default: '' },
          dateRangeType: { type: Number, default: DateRangeType.thisWeek },
          start: { type: String, default: '' },
          end: { type: String, default: '' }
        })
        return {
          ...xs,
          ...expandDateRange(xs)
        }
      },
      restoreQueryParams: () => attendancesStore.state.queryParams.value
    })
    const useSelectionState = () => {
      const selectedCount = computed(() => data.selected.length)
      const isSelected = computed(() => selectedCount.value !== 0)
      const resetSelected = () => data.selected.splice(0)
      return {
        selectedCount,
        isSelected,
        resetSelected
      }
    }
    const useDoAction = () => {
      const progress = ref(false)
      const execute = (actionText: string, callback: () => Promise<AttendancesApi.BatchResponse>) => {
        return withProgressState(progress, async () => {
          const result = await startJobPolling(async () => {
            return await callback()
          })
          if (result === false) {
            $snackbar.error(`勤務実績の${actionText}に失敗しました。`)
          } else {
            const { job } = result
            if (job.status === JobStatus.failure) {
              $snackbar.error(`勤務実績の${actionText}に失敗しました。`)
            } else if (job.status === JobStatus.success) {
              await refresh()
              data.selected = []
              $snackbar.success(`勤務実績を${actionText}しました。`)
            }
          }
        })
      }
      const showCancelDialog = ref(false)
      const cancel = {
        message: '選択した勤務実績をキャンセルします。\n実行する場合は、キャンセル理由を入力して実行を押してください。',
        showDialog: showCancelDialog,
        show: () => { showCancelDialog.value = true },
        hide: () => { showCancelDialog.value = false },
        execute: async (reason: string) => {
          const ids = data.selected.map(x => x.id)
          await execute('キャンセル', () => $api.attendances.batchCancel({ ids, reason }))
        }
      }
      const doAction = async () => {
        if (data.action === 'confirm') {
          const params: ConfirmDialogParams = {
            color: colors.critical,
            message: '選択した勤務実績を確定します。\n\n本当によろしいですか？',
            positive: '確定'
          }
          if (await $confirm.show(params)) {
            await execute('確定', () => $api.attendances.confirm({ ids: data.selected.map(x => x.id) }))
          }
        } else {
          cancel.show()
        }
      }
      return { cancel, doAction, progress }
    }
    const { cancel, doAction, progress } = useDoAction()
    const useSelectOptions = () => {
      const actions = selectOptions<Action>([
        { text: 'アクションを選択...', value: '' },
        { text: '選択した勤務実績を確定する', value: 'confirm', permissions: [Permission.updateAttendances] },
        { text: '選択した勤務実績をキャンセルする', value: 'cancel', permissions: [Permission.updateAttendances] }
      ])
      return {
        actions: computed(() => actions.filter(action => isAuthorized.value(action.permissions))),
        dateRangeTypes: enumerableOptions(DateRangeType),
        isConfirmedOptions: [
          { text: '未確定', value: false },
          { text: '確定', value: true }
        ],
        tasks: enumerableOptions(Task)
      }
    }
    const options = dataTableOptions<Attendance>({
      content: '勤務実績',
      headers: [
        { text: '利用者', value: 'user', class: `${style.thUser}`, sortable: false },
        { text: '担当スタッフ1', value: 'firstAssignee', class: `${style.thStaff}`, sortable: false },
        { text: '担当スタッフ2', value: 'secondAssignee', class: `${style.thStaff}`, sortable: false },
        { text: '勤務実績区分', value: 'task', class: `${style.thTask}`, sortable: false },
        { text: '勤務日', value: 'scheduleDate', class: `${style.thScheduleDate}`, sortable: false },
        { text: '開始/終了', value: 'scheduleTime', class: `${style.thScheduleTime}`, sortable: false },
        { text: '確定', value: 'isConfirmed', class: `${style.thIsConfirmed}`, sortable: false, align: 'center' },
        { text: '管理スタッフ', value: 'assigner', class: `${style.thStaff}`, sortable: false },
        { text: '事業所', value: 'office', class: `${style.thOffice}`, sortable: false },
        { text: '備考', value: 'note', class: `${style.thNote}`, sortable: false }
      ],
      itemLink: x => `/attendances/${x.id}`,
      itemLinkPermissions: [permissions.viewAttendances]
    })
    const onClickNegative = () => cancel.hide()
    const onClickPositive = async (reason: string) => {
      cancel.hide()
      await cancel.execute(reason)
    }
    return {
      ...attendancesStore.state,
      ...toRefs(data),
      ...useBreadcrumbs('attendances.index'),
      ...useOffices({ permission: Permission.listAttendances, internal: true }),
      ...useSelectionState(),
      ...useSelectOptions(),
      ...useStaffs({ permission: Permission.listAttendances }),
      ...useUsers({ permission: Permission.listAttendances }),
      cancelMessage: cancel.message,
      changeItemsPerPage,
      doAction,
      form,
      isAuthorized,
      itemsPerPageOptionValues: ItemsPerPageValuesLargeNumber,
      localeDate,
      numeral,
      onClickNegative,
      onClickPositive,
      options,
      paginate,
      permissions,
      progress,
      selectableAttendances,
      showCancelDialog: cancel.showDialog,
      specifyDateRange: computed(() => form.dateRangeType === DateRangeType.specify),
      submit
    }
  },
  head: () => ({
    title: '勤務実績'
  })
})
</script>

<style lang="scss" module>
.actionsMessage {
  margin: 0 0 0 4px;
}

.actionsForm {
  padding: 16px 16px 16px 8px;
}

.actionsSelect {
  margin: 0 0 0 4px;
  width: 240px;
}

.actionsButton {
  margin: 0 0 0 4px;
}

.thUser,
.thStaff {
  width: 10em;
}

.thTask {
  width: 12em;
}

.thScheduleDate {
  width: 10em;
}

.thIsConfirmed {
  width: 5em;
}

.thScheduleTime {
  width: 10em;
}

.thOffice {
  width: 12em;
}

.thNote {
  width: auto;
}
</style>
