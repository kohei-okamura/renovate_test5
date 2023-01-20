<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-dws-provision-report :breadcrumbs="breadcrumbs">
    <v-row class="align-stretch">
      <v-col class="align-stretch d-flex py-0" cols="12" sm="6">
        <z-data-card title="基本情報">
          <z-data-card-item
            data-office-abbr
            label="事業所"
            :icon="$icons.office"
          >
            <nuxt-link
              v-if="isAuthorized([permissions.viewInternalOffices, permissions.viewExternalOffices])"
              :to="`/offices/${officeId}`"
            >
              {{ resolveOfficeAbbr(officeId) }}
            </nuxt-link>
            <template v-else>{{ resolveOfficeAbbr(officeId) }}</template>
          </z-data-card-item>
          <z-data-card-item
            label="状態"
            :icon="statusIcon"
            :value="displayStatus"
          />
          <v-card-actions>
            <v-spacer />
            <v-btn color="primary" data-download-preview text @click.stop="() => previewDownloader.download(!canSave)">
              <v-icon left>{{ $icons.download }}</v-icon>
              サービス提供実績記録票をダウンロード
            </v-btn>
          </v-card-actions>
        </z-data-card>
      </v-col>
      <v-col class="py-0" cols="12" sm="6">
        <z-user-card :user="user" />
      </v-col>
    </v-row>
    <z-data-card title="予定・実績">
      <v-container>
        <v-row v-if="isPlansSelected" dense justify="center">
          <v-col cols="6" lg="3" md="4" sm="5" xl="2">
            <v-btn block color="primary" :disabled="isFixed || planSelectedCount > 1" @click="copyDialog.copyPlan">
              <v-icon left>{{ $icons.copy }}</v-icon>
              予定をコピー
            </v-btn>
          </v-col>
          <v-col cols="6" lg="3" md="4" sm="5" xl="2">
            <v-btn block color="primary" :disabled="isFixed" @click="deletePlan">
              <v-icon left>{{ $icons.delete }}</v-icon>
              予定を削除
            </v-btn>
          </v-col>
        </v-row>
        <v-row v-else-if="isResultsSelected" dense justify="center">
          <v-col cols="6" lg="3" md="4" sm="5" xl="2">
            <v-btn block color="primary" :disabled="isFixed || resultSelectedCount > 1" @click="copyDialog.copyResult">
              <v-icon left>{{ $icons.copy }}</v-icon>
              実績をコピー
            </v-btn>
          </v-col>
          <v-col cols="6" lg="3" md="4" sm="5" xl="2">
            <v-btn block color="primary" :disabled="isFixed" @click="deleteResult">
              <v-icon left>{{ $icons.delete }}</v-icon>
              実績を削除
            </v-btn>
          </v-col>
        </v-row>
        <v-row v-else dense justify="center">
          <v-col cols="12" lg="3" md="4" sm="5" xl="2">
            <z-flex>
              <v-btn color="primary" data-prev-month text :disabled="isMinProvidedIn" @click="prevMonth">前月</v-btn>
              <z-date-field
                ref="selectMonth"
                class="flex-grow-1"
                hide-details
                label="サービス提供年月"
                type="month"
                :clearable="false"
                :max="maxProvidedIn"
                :value="providedIn"
                @input="toMonth"
              />
              <v-btn color="primary" data-next-month text :disabled="isMaxProvidedIn" @click="nextMonth">次月</v-btn>
            </z-flex>
          </v-col>
          <v-col class="mt-6 mt-sm-0" cols="12" lg="3" md="4" sm="5" xl="2">
            <z-flex>
              <v-btn color="primary" data-prev-user text :disabled="isFirstUser" @click="prevUser">前へ</v-btn>
              <z-select
                ref="selectUser"
                class="ma-0"
                label="利用者"
                :items="userOptions"
                :value="userId"
                @input="toUser"
              />
              <v-btn color="primary" data-next-user text :disabled="isLastUser" @click="nextUser">次へ</v-btn>
            </z-flex>
          </v-col>
        </v-row>
        <v-row v-if="!isPlansSelected && !isResultsSelected" dense justify="center">
          <v-col cols="6" lg="3" md="4" sm="5" xl="2">
            <v-btn block color="primary" data-add-plan :disabled="isFixed" @click="addPlan">
              <v-icon left>{{ $icons.add }}</v-icon>
              予定を追加
            </v-btn>
          </v-col>
          <v-col cols="6" lg="3" md="4" sm="5" xl="2">
            <v-btn block color="primary" data-add-result :disabled="isFixed" @click="addResult">
              <v-icon left>{{ $icons.add }}</v-icon>
              実績を追加
            </v-btn>
          </v-col>
        </v-row>
        <v-row dense>
          <v-col cols="12">
            <z-overflow-shadow>
              <v-simple-table :class="$style.itemTable" dense>
                <thead>
                  <tr>
                    <th class="text-center" rowspan="2" style="min-width: 38px">
                      <span :class="$style.writingVertical">日付</span>
                    </th>
                    <th class="text-center" rowspan="2" style="min-width: 36px">
                      <span :class="$style.writingVertical">曜日</span>
                    </th>
                    <th colspan="4" style="min-width: 50px">予定</th>
                    <th></th>
                    <th colspan="4" style="min-width: 50px">実績</th>
                    <th class="text-right" rowspan="2" style="min-height: 50px; min-width: 34px">
                      <span :class="$style.writingVertical">提供人数</span>
                    </th>
                    <th class="text-center" rowspan="2" style="width: 36px">
                      <span :class="$style.writingVertical">備考</span>
                    </th>
                  </tr>
                  <tr>
                    <th class="text-center" :class="$style.checkbox">
                      <z-flex class="justify-center" :class="$style.checkboxWrapper">
                        <v-simple-checkbox
                          :disabled="isFixed || isResultsSelected"
                          :indeterminate="isPlansIndeterminate"
                          :ripple="false"
                          :value="isPlansFilled"
                          @input="setPlanSelections"
                        />
                      </z-flex>
                    </th>
                    <th style="min-width: 284px">
                      <div>提供時間</div>
                      <div>サービス区分</div>
                    </th>
                    <th class="text-right" style="min-height: 38px; min-width: 42px">
                      <span :class="$style.writingVertical">時間数</span>
                    </th>
                    <th class="text-right" style="min-height: 38px; min-width: 42px">
                      <span :class="$style.writingVertical">移動</span>
                    </th>
                    <th style="min-width: 40px">
                      <v-btn v-if="!isFixed" color="primary" icon small text :disabled="!hasPlans" @click="copyAll">
                        <v-icon>{{ $icons.moveRight }}</v-icon>
                      </v-btn>
                    </th>
                    <th class="text-center" :class="$style.checkbox">
                      <z-flex class="justify-center" :class="$style.checkboxWrapper">
                        <v-simple-checkbox
                          :disabled="isFixed || isPlansSelected"
                          :indeterminate="isResultsIndeterminate"
                          :ripple="false"
                          :value="isResultsFilled"
                          @input="setResultSelections"
                        />
                      </z-flex>
                    </th>
                    <th style="min-width: 284px">
                      <div>提供時間</div>
                      <div>サービス区分</div>
                    </th>
                    <th class="text-right" style="min-height: 38px; min-width: 42px">
                      <span :class="$style.writingVertical">時間数</span>
                    </th>
                    <th class="text-right" style="min-height: 38px; min-width: 42px">
                      <span :class="$style.writingVertical">移動</span>
                    </th>
                  </tr>
                  <tr>
                    <th colspan="13" style="height: 0"><!-- Vuetify のスタイルを適用させるためのダミーです --></th>
                  </tr>
                </thead>
                <tbody v-if="hasEntries">
                  <template v-for="({ key, plan, result }, i) in rows">
                    <z-with :key="`tr-${i}`" v-slot="{ data }" tag="tr" :value="getDateData(key)">
                      <td class="text-center" :class="$style[data.dayOfWeekClass]">
                        {{ data.dayOfMonth }}
                      </td>
                      <td class="text-center" :class="$style[data.dayOfWeekClass]">
                        {{ data.dayOfWeekName }}
                      </td>
                      <template v-if="!!plan">
                        <td class="text-center">
                          <z-flex class="justify-center" :class="$style.checkboxWrapper">
                            <v-simple-checkbox
                              v-model="planSelections[key]"
                              :disabled="isFixed || isResultsSelected"
                              :ripple="false"
                            />
                          </z-flex>
                        </td>
                        <td
                          :class="['pt-1', 'pb-1', $style.clickable]"
                          @click="() => editPlan(key)"
                        >
                          <div>
                            <z-time :value="plan.schedule.start" />
                            <span>〜</span>
                            <z-time :value="plan.schedule.end" />
                          </div>
                          <div>
                            <v-icon v-if="!isFixed" small>{{ $icons.edit }}</v-icon>
                            {{ resolveDwsProjectServiceCategory(plan.category) }}
                          </div>
                        </td>
                        <td class="text-right">{{ calculateWorkingHours(plan.schedule) }}</td>
                        <td class="text-right">{{ getMovingDurationHours(plan.movingDurationMinutes) }}</td>
                      </template>
                      <td v-else colspan="4"></td>
                      <td>
                        <v-btn
                          v-if="!isFixed"
                          color="secondary"
                          icon
                          small
                          text
                          :disabled="!(!!plan) || !!result"
                          @click="() => copyPlanToResult(key)"
                        >
                          <v-icon>{{ $icons.moveRight }}</v-icon>
                        </v-btn>
                      </td>
                      <template v-if="!!result">
                        <td class="text-center">
                          <z-flex class="justify-center" :class="$style.checkboxWrapper">
                            <v-simple-checkbox
                              v-model="resultSelections[key]"
                              :disabled="isFixed || isPlansSelected"
                              :ripple="false"
                            />
                          </z-flex>
                        </td>
                        <td
                          :class="['pt-1', 'pb-1', $style.clickable]"
                          @click="() => editResult(key)"
                        >
                          <div>
                            <z-time :value="result.schedule.start" />
                            <span>〜</span>
                            <z-time :value="result.schedule.end" />
                          </div>
                          <div>
                            <v-icon v-if="!isFixed" small>{{ $icons.edit }}</v-icon>
                            {{ resolveDwsProjectServiceCategory(result.category) }}
                          </div>
                        </td>
                        <td class="text-right">{{ calculateWorkingHours(result.schedule) }}</td>
                        <td class="text-right">{{ getMovingDurationHours(result.movingDurationMinutes) }}</td>
                      </template>
                      <td v-else colspan="4"></td>
                      <td class="text-center">{{ (result || plan).headcount }}</td>
                      <td>
                        <v-tooltip
                          v-if="hasNote(key)"
                          color="secondary"
                          nudge-left="150"
                          top
                          :max-width="noteWidth"
                          :min-width="noteWidth"
                          :open-on-click="true"
                          :open-on-hover="false"
                        >
                          {{ getNote(key) }}
                          <template #activator="{ on, attrs }">
                            <v-btn icon v-bind="attrs" v-on="on">
                              <v-icon>{{ $icons.note }}</v-icon>
                            </v-btn>
                          </template>
                        </v-tooltip>
                      </td>
                    </z-with>
                  </template>
                  <tr :class="$style.dummyRow"><!-- Vuetify のスタイルを適用させるためのダミーです --></tr>
                </tbody>
                <tbody v-else :class="$style.noData">
                  <tr>
                    <td class="pt-2 text-center" colspan="11">データがありません</td>
                  </tr>
                </tbody>
              </v-simple-table>
            </z-overflow-shadow>
          </v-col>
        </v-row>
        <v-alert
          v-if="errors.length"
          class="text-caption text-sm-body-2 mt-6"
          data-errors
          dense
          type="error"
        >
          <template v-for="(error, i) in errors">
            {{ error }}<br :key="i">
          </template>
        </v-alert>
        <v-row>
          <v-spacer />
          <v-col cols="12" md="6">
            <v-simple-table dense>
              <thead>
                <tr>
                  <td>サービス区分</td>
                  <td class="text-right">予定時間数計</td>
                  <td class="text-right">実績時間数計</td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(v, k) in timeSummary" :key="k">
                  <td>{{ k }}</td>
                  <td class="text-right">{{ v.plan }}</td>
                  <td class="text-right">{{ v.result }}</td>
                </tr>
              </tbody>
            </v-simple-table>
          </v-col>
        </v-row>
        <v-row class="pt-4" dense justify="end">
          <v-col class="d-flex justify-center" cols="12" md="3" sm="6">
            <v-btn block color="primary" data-copy-plans :disabled="isFixed" @click="copyPlans">
              <v-icon left>{{ $icons.copy }}</v-icon>
              前月から予定をコピー
            </v-btn>
          </v-col>
          <v-col class="d-flex justify-center mt-2 mt-sm-0" cols="12" md="3" sm="6">
            <v-btn block color="accent" data-save :disabled="!canSave" @click="save">
              <v-icon left>{{ $icons.save }}</v-icon>
              保存
            </v-btn>
          </v-col>
          <v-col class="d-flex justify-center mt-2 mt-sm-0" cols="12" md="3" sm="6">
            <v-btn v-if="isFixed" block color="accent" data-remand @click="remand">
              <v-icon left>{{ $icons.edit }}</v-icon>
              予実を作成中にする
            </v-btn>
            <v-btn
              v-else
              class="flex-grow-1"
              color="accent"
              data-confirm
              :disabled="!canConfirm"
              @click="confirm"
            >
              <v-icon left>{{ $icons.confirmed }}</v-icon>
              予実を確定する
            </v-btn>
          </v-col>
          <v-col v-if="isShowDeleteButton" class="d-flex mt-2 mt-md-0" cols="12" order-md="first" md="3">
            <v-btn data-delete-dws-provision-report-button color="danger" text @click="deleteDwsProvisionReport">
              <v-icon left>{{ $icons.delete }}</v-icon>
              削除
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </z-data-card>
    <z-dws-provision-report-item-browsing-dialog
      v-if="isFixed"
      ref="dwsProvisionReportItemBrowsingDialog"
      :show="isDialogActive"
      :target="editingTarget"
      :value="currentItemData.item"
      :width="dialogWidth"
      @click:close="closeDialog"
    />
    <z-dws-provision-report-item-form-dialog
      v-else
      ref="dwsProvisionReportItemFormDialog"
      :office-id="officeId"
      :provided-in="providedIn"
      :show="isDialogActive"
      :target="editingTarget"
      :value="currentItemData"
      :width="dialogWidth"
      @click:cancel="closeDialog"
      @click:save="storeItem"
    />
    <z-dws-provision-report-item-copy-dialog
      v-if="!!copyDialog.state.copySource"
      :copyable-dates="copyDialog.state.copyableDates"
      :show="copyDialog.state.isActive"
      :target="copyDialog.state.target"
      :value="copyDialog.state.copySource"
      :width="copyDialog.dialogWidth.value"
      @click:cancel="copyDialog.closeDialog"
      @click:save="copyDialog.storeItem"
    />
    <z-progress :value="inProgress" />
  </z-page>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  nextTick,
  onMounted,
  reactive,
  Ref,
  ref,
  toRefs,
  watch
} from '@nuxtjs/composition-api'
import { DayOfWeek, resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'
import { resolveDwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import {
  DwsProvisionReportStatus,
  resolveDwsProvisionReportStatus
} from '@zinger/enums/lib/dws-provision-report-status'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { Permission } from '@zinger/enums/lib/permission'
import { assert, assign, clone, debounce, distinct, noop, pick, range, tap, wait } from '@zinger/helpers'
import zip from 'lodash.zip'
import { DateTime, Interval } from 'luxon'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { datetime } from '~/composables/datetime'
import { calculateWorkingHours, getMovingDurationHours } from '~/composables/dws-service-unit-time'
import { numeralWithDivision } from '~/composables/numeral'
import { selectOptions } from '~/composables/select-options'
import {
  dwsProvisionReportStateKey,
  dwsProvisionReportStoreKey
} from '~/composables/stores/use-dws-provision-report-store'
import { dwsProvisionReportsStateKey } from '~/composables/stores/use-dws-provision-reports-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useDeleteFunction } from '~/composables/use-delete-function'
import { useInjected } from '~/composables/use-injected'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useProvisionReportStatusIcon } from '~/composables/use-provision-report-status-icon'
import { useSelections } from '~/composables/use-selections'
import { auth } from '~/middleware/auth'
import { ISO_DATETIME_FORMAT, ISO_MONTH_FORMAT, OLDEST_DATE } from '~/models/date'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { UserId } from '~/models/user'
import { VSelectOption } from '~/models/vuetify'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'
import { RefOrValue, unref } from '~/support/reactive'

type DateData = {
  dayOfMonth: number
  dayOfWeek: DayOfWeek
  dayOfWeekClass: ReturnType<typeof DayOfWeek.keysMap> extends Record<any, infer T> ? T : never
  dayOfWeekName: string
}

type ItemDataForInsert = {
  key?: undefined
  item: DwsProvisionReportItem
}
type ItemDataForUpdate = {
  key: string
  item: DwsProvisionReportItem
}
type ItemData = ItemDataForInsert | ItemDataForUpdate

type Form = Partial<DwsProvisionReportsApi.UpdateForm>

type OperationTarget = '予定' | '実績'
const OPERATION_TARGET_PLAN = '予定'
const OPERATION_TARGET_RESULT = '実績'

type ScheduleComparison = 0 | 1 | 2
const SCHEDULE_ISOLATED = 0
const SCHEDULE_OVERLAPPED = 1
const SCHEDULE_DUPLICATED = 2

export default defineComponent({
  name: 'DwsProvisionReportPage',
  middleware: [auth(Permission.updateDwsProvisionReports)],
  setup () {
    const { $api, $confirm, $datetime, $download, $form, $route, $router, $snackbar, $vuetify } = usePlugins()
    const { errors, progress: axiosProgress, withAxios } = useAxios()
    const otherProgress = ref(false)
    const inProgress = computed(() => axiosProgress.value || otherProgress.value)
    const store = useInjected(dwsProvisionReportStoreKey)
    const { dwsProvisionReport: report } = useInjected(dwsProvisionReportStateKey)
    const { dwsProvisionReports: reports } = useInjected(dwsProvisionReportsStateKey)
    const params = $route.params
    const officeId = parseInt(params.officeId)
    const userId = parseInt(params.userId)
    const providedIn = params.providedIn
    const providedInDateTime = $datetime.parse(providedIn)
    const daysInMonth = range(1, providedInDateTime.daysInMonth)

    /*
     * 日付情報を返す関数
     */
    const getDateData = computed(() => {
      const dayOfWeekClassMap = DayOfWeek.keysMap()
      const dayOfWeekNameMap = DayOfWeek.map
      const firstDayOfWeek = providedInDateTime.startOf('month').weekday
      const entries = daysInMonth.map((dayOfMonth, i): [string, DateData] => {
        const dayOfWeek = ((firstDayOfWeek + i) % 7 || 7) as DayOfWeek
        const key = `${providedIn}-` + `${dayOfMonth}`.padStart(2, '0')
        const value = {
          dayOfMonth,
          dayOfWeek,
          dayOfWeekClass: dayOfWeekClassMap[dayOfWeek],
          dayOfWeekName: dayOfWeekNameMap[dayOfWeek]
        }
        return [key, value]
      })
      const dates = Object.fromEntries(entries)
      return (key: string): DateData => {
        const { schedule } = JSON.parse(key)
        return dates[schedule.date]
      }
    })

    /*
     * サービス提供年月、利用者の移動
     */
    const moveTo = ({ toUserId = userId, toProvidedIn = providedIn }) => {
      $form.verifyBeforeLeaving(() => {
        $router.push(`/dws-provision-reports/${officeId}/${toUserId}/${toProvidedIn}`)
      })
    }

    /*
     * サービス提供年月による遷移および制御
     */
    const useMonthNavigation = () => {
      const toMonth = (month: string) => moveTo({ toProvidedIn: month })
      const nextMonth = () => toMonth(providedInDateTime.plus({ months: 1 }).toFormat(ISO_MONTH_FORMAT))
      const prevMonth = () => toMonth(providedInDateTime.minus({ months: 1 }).toFormat(ISO_MONTH_FORMAT))
      const maxProvidedIn = $datetime.now.plus({ months: 3 }).toFormat(ISO_MONTH_FORMAT)
      const minProvidedIn = DateTime.fromISO(OLDEST_DATE).toFormat(ISO_MONTH_FORMAT)
      const isMaxProvidedIn = providedIn === maxProvidedIn
      const isMinProvidedIn = providedIn === minProvidedIn
      return {
        toMonth,
        nextMonth,
        prevMonth,
        maxProvidedIn,
        minProvidedIn,
        isMaxProvidedIn,
        isMinProvidedIn
      }
    }

    /*
     * 利用者による遷移および制御
     */
    const useUserNavigation = () => {
      const userNavigationState = reactive({
        userOptions: [] as VSelectOption<UserId>[],
        nextUser: noop,
        prevUser: noop,
        isFirstUser: false,
        isLastUser: false
      })
      const toUser = (userId: number) => moveTo({ toUserId: userId })
      watch(
        reports,
        xs => {
          const userIds = xs.map(x => x.userId)
          const currentUserIndex = userIds.indexOf(userId)
          userNavigationState.userOptions = selectOptions(xs.map(x => ({
            value: x.userId,
            text: x.name.displayName
          })))
          userNavigationState.nextUser = () => toUser(userIds[currentUserIndex + 1])
          userNavigationState.prevUser = () => toUser(userIds[currentUserIndex - 1])
          userNavigationState.isFirstUser = currentUserIndex === 0
          userNavigationState.isLastUser = currentUserIndex === userIds.length - 1
        },
        { immediate: true }
      )
      return {
        ...toRefs(userNavigationState),
        toUser
      }
    }

    /*
     * 予定・実績
     */
    const createKey = ({ schedule }: DwsProvisionReportItem) => JSON.stringify({ schedule })
    const createItems = (items: DwsProvisionReportItem[] = []) => {
      const xs = ref(items)
      const itemKeys = computed(() => xs.value.map(createKey))
      const hasItems = computed(() => xs.value.length > 0)
      const itemsObject = computed(() => Object.fromEntries(zip(itemKeys.value, xs.value)))
      return [xs, itemKeys, hasItems, itemsObject] as const
    }
    const [plans, planKeys, hasPlans, plansObject] = createItems(report.value?.plans)
    const [results, resultKeys, hasResults, resultsObject] = createItems(report.value?.results)
    const hasEntries = computed(() => hasPlans.value || hasResults.value)
    const rows = computed(() => {
      const currentPlansObject = plansObject.value
      const currentResultsObject = resultsObject.value
      const getRows = (key: string) => {
        const plan = currentPlansObject[key]
        const result = currentResultsObject[key]
        // 提供人数が異なる場合は別の行にする（提供人数に関係なく、予定を上の行にする）
        return plan && result && plan.headcount !== result.headcount
          ? [{ key, plan }, { key, result }]
          : [{ key, plan, result }]
      }
      return distinct(...planKeys.value, ...resultKeys.value).sort().flatMap(getRows)
    })

    /*
     * 状態
     */
    const isEditing = ref(false)
    // 未保存でページ遷移をしようとした際に確認ダイアログを表示する
    $form.preventUnexpectedUnload()
    $form.watch(isEditing)
    const isFixed = computed(() => report.value?.status === DwsProvisionReportStatus.fixed)
    const isInProgress = computed(() => report.value?.status === DwsProvisionReportStatus.inProgress)
    const isNotCreated = computed(() => report.value?.status === DwsProvisionReportStatus.notCreated)
    const displayStatus = computed(() => {
      const status = report.value?.status || DwsProvisionReportStatus.notCreated
      const resolved = resolveDwsProvisionReportStatus(status)
      return isFixed.value ? `${resolved}（${datetime(report.value?.fixedAt || '')}）` : resolved
    })

    /*
     * 合計時間数
     */
    type TimeSummaryResponse = DwsProvisionReportsApi.GetTimeSummaryResponse
    type TimeSummaryKey = keyof DwsProvisionReportsApi.GetTimeSummaryResponseItem
    const timeSummaryTitle = {
      [DwsBillingServiceReportAggregateGroup.physicalCare]: '居宅介護:身体介護',
      [DwsBillingServiceReportAggregateGroup.accompanyWithPhysicalCare]: '居宅介護:通院等介助（身体を伴う）',
      [DwsBillingServiceReportAggregateGroup.housework]: '居宅介護:家事援助',
      [DwsBillingServiceReportAggregateGroup.accompany]: '居宅介護:通院等介助（身体を伴わない）',
      [DwsBillingServiceReportAggregateGroup.visitingCareForPwsd]: '重度訪問介護',
      [DwsBillingServiceReportAggregateGroup.outingSupportForPwsd]: '重度訪問介護（移動介護）'
    }
    // 画面表示用のオブジェクトを作成する
    // in: { plan: { 11: 0, 12: 0, ... }, result: { 11: 0, 12: 0, ... } }
    // out: { 11: { plan: 0, result: 0 }, 12: { plan: 0, result: 0 } }
    const createTimeSummary = (res: Partial<TimeSummaryResponse> = {}) => {
      const temp = DwsBillingServiceReportAggregateGroup.values
        .filter(x => x !== DwsBillingServiceReportAggregateGroup.accessibleTaxi)
        .sort() as TimeSummaryKey[]
      return temp
        .map(x => {
          return {
            [timeSummaryTitle[x]]: {
              plan: numeralWithDivision(res.plan?.[x] ?? 0),
              result: numeralWithDivision(res.result?.[x] ?? 0)
            }
          }
        })
        .reduce((acc, cur) => ({ ...acc, ...cur }), {})
    }
    const timeSummary = reactive(createTimeSummary())
    const refreshTimeSummary = async () => {
      const filter = (xs: Ref<DwsProvisionReportItem[]>) => xs.value
        .filter(x => x.category !== LtcsProjectServiceCategory.ownExpense)
      const targetPlans = filter(plans)
      const targetResults = filter(results)
      if (targetPlans.length === 0 && targetResults.length === 0) {
        assign(timeSummary, createTimeSummary())
      } else {
        const form = {
          officeId,
          userId,
          providedIn,
          plans: targetPlans,
          results: targetResults
        }
        assign(timeSummary, createTimeSummary(await $api.dwsProvisionReports.getTimeSummary({ form })))
      }
    }
    // TODO 介保のように連打はできないので遅延は不要かもしれない
    const debouncedRefreshTimeSummary = debounce({ wait: 200 }, refreshTimeSummary)

    onMounted(() => {
      // 編集を監視しているので plans, results の watch に immediate は使えないため、マウント時に更新する
      refreshTimeSummary()
    })

    watch(
      () => [plans.value, results.value],
      () => {
        isEditing.value = true
        debouncedRefreshTimeSummary()
      },
      { deep: true }
    )

    const planSelections = (() => {
      const x = useSelections(planKeys)
      return {
        planSelections: x.selections,
        planSelectedKeys: x.selectedKeys,
        planSelectedCount: x.selectedCount,
        isPlansSelected: x.isSelected,
        isPlansFilled: x.isFilled,
        isPlansIndeterminate: x.isIndeterminate,
        setPlanSelections: x.setSelections
      }
    })()
    const resultSelections = (() => {
      const x = useSelections(resultKeys)
      return {
        resultSelections: x.selections,
        resultSelectedKeys: x.selectedKeys,
        resultSelectedCount: x.selectedCount,
        isResultsSelected: x.isSelected,
        isResultsFilled: x.isFilled,
        isResultsIndeterminate: x.isIndeterminate,
        setResultSelections: x.setSelections
      }
    })()

    /*
     * 同一時間帯の予定 or 実績の重複があるかを確認する関数
     *
     * 時間帯が重複する場合かつ合計人数が2人を超える場合に重複とみなす
     */
    const compareSchedules = (() => {
      const getItemInterval = ({ schedule }: DwsProvisionReportItem) => {
        return Interval.fromISO(`${schedule.start}/${schedule.end}`)
      }
      const compareRecursive = (intervals: Interval[]): boolean => {
        if (intervals.length <= 1) {
          return false
        } else {
          const [head, ...tail] = intervals
          return tail.some(x => x.overlaps(head)) || compareRecursive(tail)
        }
      }
      return (item: DwsProvisionReportItem, existences: RefOrValue<DwsProvisionReportItem[]>): ScheduleComparison => {
        const itemInterval = getItemInterval(item)
        const overlappedItems = unref(existences)
          .map(x => [getItemInterval(x), x.headcount] as const)
          .filter(([interval]) => interval.overlaps(itemInterval))

        // 完全一致する予実が既に存在している
        if (overlappedItems.some(([interval]) => interval.equals(itemInterval))) {
          return SCHEDULE_DUPLICATED
        }

        // 部分一致 && 合計人数が3人以上となる予実が既に存在している
        if (overlappedItems.some(([, headcount]) => item.headcount + headcount > 2)) {
          return SCHEDULE_OVERLAPPED
        }

        // 同じ時間帯に部分一致する予実が既に複数存在している
        // ＝ itemInterval との重複部分のみの interval 同士で重複する
        const intersections = overlappedItems.map(([interval]) => {
          // overlappedItems の各要素は itemInterval と重複することが保証されている
          return interval.intersection(itemInterval)!
        })
        if (compareRecursive(intersections)) {
          return SCHEDULE_OVERLAPPED
        }

        // 重複する予実は存在しない
        return SCHEDULE_ISOLATED
      }
    })()

    const getDialogWidth = () => {
      if ($vuetify.breakpoint.smAndDown) {
        return '90%'
      } else if ($vuetify.breakpoint.mdOnly) {
        return '75%'
      } else {
        return '50%'
      }
    }
    /*
     * 予定・実績の追加・編集ダイアログ
     */
    const useItemDialog = () => {
      /*
       * ダイアログの制御
       */
      const dialogState = reactive({
        currentItemData: { item: undefined, key: undefined } as Partial<ItemData>,
        editingTarget: '' as OperationTarget | '',
        isDialogActive: false
      })
      const dialogWidth = computed(getDialogWidth)
      const openDialog = (target: OperationTarget, x: Partial<ItemData> = {}) => {
        dialogState.currentItemData.item = x.item ? clone(x.item) : undefined
        dialogState.currentItemData.key = x.key
        dialogState.editingTarget = target
        dialogState.isDialogActive = true
      }
      const closeDialog = async () => {
        dialogState.isDialogActive = false

        // 画面がチラつくので少し遅らせる
        await nextTick()
        await wait(200)
        dialogState.editingTarget = ''
        dialogState.currentItemData.item = undefined
        dialogState.currentItemData.key = undefined
      }
      const addPlan = () => openDialog(OPERATION_TARGET_PLAN)
      const addResult = () => openDialog(OPERATION_TARGET_RESULT)
      const editPlan = (key: string) => {
        openDialog(OPERATION_TARGET_PLAN, { key, item: plansObject.value[key] })
      }
      const editResult = (key: string) => {
        openDialog(OPERATION_TARGET_RESULT, { key, item: resultsObject.value[key] })
      }

      /*
       * 予定・実績の追加・編集
       */
      const storeItem = (() => {
        const ifIsolated = (item: DwsProvisionReportItem, target: DwsProvisionReportItem[], f: () => void) => {
          return tap(compareSchedules(item, target), comparison => {
            if (comparison === SCHEDULE_ISOLATED) {
              f()
            }
          })
        }
        const insert = ({ item }: ItemDataForInsert, target: Ref<DwsProvisionReportItem[]>) => {
          return ifIsolated(item, target.value, () => target.value.push(item))
        }
        const update = (
          { key, item }: ItemDataForUpdate,
          target: Ref<DwsProvisionReportItem[]>,
          keys: Ref<string[]>
        ) => {
          // 自分はチェック対象から外す
          const index = keys.value.indexOf(key)
          return ifIsolated(item, target.value.filter((_, i) => i !== index), () => {
            const newKey = createKey(item)
            if (key === newKey) {
              // key と 新しい key が等しい場合は入れ替え
              target.value.splice(index, 1, item)
            } else {
              // key と 新しい key が異なる場合は削除と追加
              target.value.splice(index, 1)
              target.value.push(item)
            }
          })
        }
        const isInsert = (data: ItemData): data is ItemDataForInsert => data.key === undefined
        const store = (data: ItemData, target: Ref<DwsProvisionReportItem[]>, keys: Ref<string[]>) => {
          return isInsert(data) ? insert(data, target) : update(data, target, keys)
        }
        const onStored = () => {
          closeDialog()
        }
        const onOverlapped = (action: string) => {
          $snackbar.warning(`提供時間が重複している${dialogState.editingTarget}があるため${action}できません。`)
        }
        const onDuplicated = (action: string) => {
          $snackbar.warning(`提供時間が完全に一致する${dialogState.editingTarget}があるため${action}できません。`)
        }
        return (data: ItemData) => {
          const target = dialogState.editingTarget
          assert(target !== '', 'invalid state: editingTarget is empty')
          const comparison = target === OPERATION_TARGET_PLAN
            ? store(data, plans, planKeys)
            : store(data, results, resultKeys)
          switch (comparison) {
            case SCHEDULE_ISOLATED:
              return onStored()
            case SCHEDULE_OVERLAPPED:
              return onOverlapped(isInsert(data) ? '追加' : '更新')
            case SCHEDULE_DUPLICATED:
              return onDuplicated(isInsert(data) ? '追加' : '更新')
            default:
              throw new Error(`Unexpected comparison: ${comparison}`)
          }
        }
      })()

      return {
        ...toRefs(dialogState),
        closeDialog,
        dialogWidth,
        addPlan,
        addResult,
        editPlan,
        editResult,
        storeItem
      }
    }

    /*
     * 予定・実績のコピーダイアログ
     */
    const useItemCopyDialog = () => {
      /*
       * ダイアログの制御
       */
      const state = reactive({
        copySource: undefined as DwsProvisionReportItem | undefined,
        copyableDates: [] as string[],
        target: '' as OperationTarget | '',
        isActive: false
      })
      const dialogWidth = computed(getDialogWidth)
      const copyableItemMap: Map<string, DwsProvisionReportItem> = new Map()
      /*
       * 選択したアイテムをコピー可能な日付の情報を設定する
       */
      const setCopyableDates = (item: DwsProvisionReportItem, items: Ref<DwsProvisionReportItem[]>) => {
        const { start, end } = item.schedule
        const past = $datetime.parse(start)
        // 日跨ぎの考慮が面倒なので時間の差を使う
        const diff = $datetime.parse(end).diff(past)
        const tempDates: string[] = []
        daysInMonth.forEach(x => {
          const start = past.set({ day: x })
          const end = start.plus(diff)
          const date = start.toISODate()
          const tempItem = {
            ...item,
            schedule: {
              date,
              start: start.toFormat(ISO_DATETIME_FORMAT),
              end: end.toFormat(ISO_DATETIME_FORMAT)
            }
          }
          if (compareSchedules(tempItem, items) === SCHEDULE_ISOLATED) {
            tempDates.push(date)
            // せっかく作ったのでコピー時に使えるように保持しておく
            copyableItemMap.set(date, clone(tempItem))
          }
        })
        state.copyableDates = tempDates
      }
      const openDialog = (target: OperationTarget, item: DwsProvisionReportItem) => {
        state.copySource = item
        state.target = target
        state.isActive = true
      }
      const closeDialog = () => {
        state.isActive = false
        state.copySource = undefined
        state.copyableDates = []
        copyableItemMap.clear()
      }
      const copyPlan = () => {
        const item = plansObject.value[planSelections.planSelectedKeys.value[0]]
        setCopyableDates(item, plans)
        openDialog(OPERATION_TARGET_PLAN, item)
      }
      const copyResult = () => {
        const item = resultsObject.value[resultSelections.resultSelectedKeys.value[0]]
        setCopyableDates(item, results)
        openDialog(OPERATION_TARGET_RESULT, item)
      }
      /*
       * 予定・実績の追加
       */
      const storeItem = (date: string[]) => {
        const [ref, setSelections] = state.target === OPERATION_TARGET_PLAN
          ? [plans, planSelections.setPlanSelections]
          : [results, resultSelections.setResultSelections]
        date.forEach(x => {
          const item = copyableItemMap.get(x)
          if (item) {
            ref.value.push(item)
          }
        })
        // コピー元の選択状態を解除する
        setSelections(false)
        closeDialog()
      }

      return {
        closeDialog,
        copyPlan,
        copyResult,
        dialogWidth,
        state,
        storeItem
      }
    }

    /*
     * 予定・実績のコピー
     */
    const useCopyFunctions = () => {
      // 予定から実績への一括コピー
      const copyAll = async () => {
        const confirmed = await $confirm.show({
          message: 'すべての予定を実績にコピーします。現在入力されている実績はすべて消去されます。\n\nよろしいですか？',
          positive: 'コピー'
        })
        if (confirmed) {
          results.value = clone(plans.value)
        }
      }
      // 予定から実績への単体コピー
      const copyPlanToResult = (key: string) => {
        const plan = plansObject.value[key]
        if (compareSchedules(plan, results) === SCHEDULE_ISOLATED) {
          results.value.push({ ...plan })
        } else {
          $snackbar.warning('提供時間が重複している実績があるためコピーできません。')
        }
      }
      return {
        copyAll,
        copyPlanToResult
      }
    }

    /*
     * 予定・実績の削除
     */
    const useDeleteFunctions = () => {
      type Params = {
        target: OperationTarget
        items: Ref<DwsProvisionReportItem[]>
        keys: Ref<string[]>
        selectedKeys: Ref<string[]>
      }
      const createFunction = ({ target, items, keys, selectedKeys }: Params) => {
        return async () => {
          const confirmed = await $confirm.show({
            color: 'danger',
            message: `${target}を削除します。\n\n本当によろしいですか？`,
            positive: '削除'
          })
          if (confirmed) {
            selectedKeys.value.forEach(key => {
              const index = keys.value.indexOf(key)
              if (index !== -1) {
                items.value.splice(index, 1)
              }
            })
          }
        }
      }
      return {
        deletePlan: createFunction({
          target: OPERATION_TARGET_PLAN,
          items: plans,
          keys: planKeys,
          selectedKeys: planSelections.planSelectedKeys
        }),
        deleteResult: createFunction({
          target: OPERATION_TARGET_RESULT,
          items: results,
          keys: resultKeys,
          selectedKeys: resultSelections.resultSelectedKeys
        })
      }
    }

    /*
     * API の 400 エラーの監視
     */
    watch(
      errors,
      () => {
        if (Object.values(errors.value).length !== 0) {
          const message = isEditing.value
            ? '障害福祉サービス予実の保存に失敗しました。'
            : '障害福祉サービス予実の状態変更に失敗しました。'
          $snackbar.error(message)
        }
      }
    )
    /*
     * 前月から予定をコピー
     */
    const copyPlans = async () => {
      try {
        const confirmed = await $confirm.show({
          message: '前月から予定をコピーします。現在入力されている予定・実績はすべて消去されます。\n\nよろしいですか？',
          positive: 'コピー'
        })
        if (confirmed) {
          otherProgress.value = true
          plans.value = await store.getLastPlans({ officeId, userId, providedIn })
          results.value = []
        }
      } catch {
        $snackbar.error('前月の予定が登録されていないためコピーできません。')
      } finally {
        otherProgress.value = false
      }
    }
    /*
     * 予実の登録・更新
     */
    const useSubmitFunctions = () => {
      const save = async () => {
        const form: Form = { plans: plans.value, results: results.value }
        await withAxios(
          async () => {
            await store.update({ officeId, userId, providedIn, form })
            $snackbar.success('障害福祉サービス予実を保存しました。')
            isEditing.value = false
            errors.value = {}
          },
          _ => $snackbar.error('障害福祉サービス予実の保存に失敗しました。')
        )
      }
      /*
       * 予実の状態変更
       */
      const updateStatus = (status: DwsProvisionReportStatus) => withAxios(
        async () => {
          await store.updateStatus({ officeId, userId, providedIn, form: { status } })
          const label = resolveDwsProvisionReportStatus(status)
          $snackbar.success(`障害福祉サービス予実の状態を${label}に変更しました。`)
          errors.value = {}
        },
        _ => $snackbar.error('障害福祉サービス予実の状態変更に失敗しました。')
      )
      const confirm = async () => await updateStatus(DwsProvisionReportStatus.fixed)
      const remand = async () => await updateStatus(DwsProvisionReportStatus.inProgress)
      const canConfirm = computed(() => !isEditing.value && hasEntries.value)
      const canSave = computed(() => !isFixed.value && isEditing.value && hasEntries.value)
      return {
        save,
        confirm,
        remand,
        canConfirm,
        canSave
      }
    }
    /*
     * 保存した予実の削除
     */
    const deleteDwsProvisionReport = useDeleteFunction(report, x => ({
      messageOnConfirm: '予定・実績を削除します。一度削除した予定・実績は元に戻せません。\n\n本当によろしいですか？',
      messageOnSuccess: '予定・実績を削除しました。',
      returnTo: '/dws-provision-reports',
      callback: () => $api.dwsProvisionReports.delete({
        officeId: x.officeId,
        userId: x.userId,
        providedIn
      })
    }))

    /*
     * 備考
     */
    const useNotes = () => {
      const getNote = (key: string) => {
        return resultsObject.value[key]?.note || plansObject.value[key]?.note
      }
      const hasNote = (key: string) => !!getNote(key)
      const noteWidth = computed(() => {
        if ($vuetify.breakpoint.smAndDown) {
          return '90vw'
        } else if ($vuetify.breakpoint.mdOnly) {
          return '60vw'
        } else {
          return '360px'
        }
      })
      return {
        getNote,
        hasNote,
        noteWidth
      }
    }

    // サービス提供実績記録票ダウンロード
    const usePreviewDownloader = () => {
      const { withAxios } = useAxios()
      const { execute } = useJobWithNotification()
      const download = (isSaved: boolean) => {
        if (!isSaved) {
          $snackbar.error('先に予実を保存してください。')
          return
        }
        withAxios(() => {
          const form: DwsProvisionReportsApi.DownloadForm = {
            officeId,
            userId,
            providedIn
          }
          return $form.submit(() => execute({
            notificationProps: {
              text: {
                progress: 'サービス提供実績記録票のダウンロードを準備中です...',
                success: 'サービス提供実績記録票のダウンロードを開始します',
                failure: 'サービス提供実績記録票のダウンロードに失敗しました'
              }
            },
            process: () => {
              return $api.dwsProvisionReports.downloadPreviews({ form })
            },
            success: job => {
              $download.uri(job.data.uri, job.data.filename)
            }
          }))
        })
      }
      return {
        download
      }
    }
    return {
      ...pick(useInjected(userStateKey), ['user']),
      ...planSelections,
      ...resultSelections,
      ...useBreadcrumbs('dwsProvisionReports.edit'),
      ...useCopyFunctions(),
      ...useDeleteFunctions(),
      ...useItemDialog(),
      ...useMonthNavigation(),
      ...useNotes(),
      ...useAuth(),
      ...useOffices({ permission: Permission.updateDwsProvisionReports }),
      ...useProvisionReportStatusIcon(report),
      ...useSubmitFunctions(),
      ...useUserNavigation(),
      calculateWorkingHours,
      copyDialog: useItemCopyDialog(),
      copyPlans,
      deleteDwsProvisionReport,
      displayStatus,
      errors: computed(() => Object.values(errors.value).flat()),
      getDateData,
      getMovingDurationHours,
      hasEntries,
      hasPlans,
      inProgress,
      isFixed,
      isInProgress,
      isShowDeleteButton: computed(() => report.value?.status && !isNotCreated.value),
      officeId,
      previewDownloader: usePreviewDownloader(),
      providedIn,
      report,
      resolveDayOfWeek,
      resolveDwsProjectServiceCategory,
      resolveDwsProvisionReportStatus,
      rows,
      timeSummary,
      userId
    }
  },
  head: () => ({
    title: '障害福祉サービス 予実を登録・編集'
  })
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/settings/colors';

.itemTable {
  tr {
    &.dummyRow {
      display: none;
    }

    &:hover {
      background: inherit !important;
    }
  }

  th {
    padding: 0 8px !important;

    &:not(.checkbox) {
      vertical-align: bottom;
    }
  }

  td {
    padding: 0 8px !important;

    &.sat {
      background-color: rgba(144, 202, 249, 0.6);
      color: map-get($blue, 'darken-2');
    }

    &.sun {
      background-color: rgba(248, 187, 208, 0.6);
      color: map-get($red, 'darken-2');
    }
  }

  tbody:not(.no-data) {
    td.clickable:hover {
      background-color: rgba(224, 224, 224, 0.6);
      cursor: pointer;
    }
  }

  .checkboxWrapper {
    height: 24px;
    margin-right: -8px;
  }
}

.writingVertical {
  writing-mode: vertical-rl;
}
</style>
