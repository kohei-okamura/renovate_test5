<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-ltcs-provision-report :breadcrumbs="breadcrumbs">
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
            <v-btn color="primary" data-download-sheet text @click.stop="() => fileDownloadDialog.show(!canSave)">
              <v-icon left>{{ $icons.download }}</v-icon>
              サービス提供票をダウンロード
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
        <v-row class="pb-4" no-gutters>
          <template v-if="isSelected">
            <v-col cols="12">
              <v-btn
                color="primary"
                data-copy-service
                depressed
                :disabled="isFixed || selectedCount > 1"
                @click="() => copyEntry(selectedKeys)"
              >
                <v-icon left>{{ $icons.copy }}</v-icon>
                サービス情報をコピー
              </v-btn>
              <v-btn
                color="primary"
                data-delete-service
                depressed
                :disabled="isFixed"
                @click="() => deleteEntry(selectedKeys)"
              >
                <v-icon left>{{ $icons.delete }}</v-icon>
                サービス情報を削除
              </v-btn>
            </v-col>
          </template>
          <template v-else>
            <v-col class="mt-3 mt-md-0" order="2" order-md="0" cols="12" md="4">
              <v-btn color="primary" data-add-service depressed :disabled="isFixed" @click="addEntry">
                <v-icon left>{{ $icons.add }}</v-icon>
                サービス情報を追加
              </v-btn>
            </v-col>
            <v-col cols="12" md="4" sm="6">
              <z-flex cols="12">
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
            <v-col class="mt-6 mt-sm-0" cols="12" md="4" sm="6">
              <z-flex cols="12">
                <v-btn color="primary" data-prev-user text :disabled="isFirstUser" @click="prevUser">前へ</v-btn>
                <z-select
                  ref="selectUser"
                  class="ma-0"
                  hide-details
                  label="利用者"
                  :items="userOptions"
                  :value="userId"
                  @input="toUser"
                />
                <v-btn color="primary" data-next-user text :disabled="isLastUser" @click="nextUser">次へ</v-btn>
              </z-flex>
            </v-col>
          </template>
        </v-row>
        <v-row no-gutters>
          <v-col cols="12">
            <z-overflow-shadow>
              <v-simple-table :class="$style.entryTable" dense>
                <template #default>
                  <thead>
                    <tr>
                      <th class="text-center">
                        <z-flex class="justify-center">
                          <v-simple-checkbox
                            :disabled="isFixed"
                            :indeterminate="isIndeterminate"
                            :ripple="false"
                            :value="isFilled"
                            @input="setSelections"
                          />
                        </z-flex>
                      </th>
                      <th class="text-start" style="min-width: 200px">
                        <div>提供時間</div>
                        <div>サービス内容</div>
                      </th>
                      <th style="min-width: 60px"></th>
                      <th
                        v-for="date in dateRange"
                        :key="`head_${date.day}`"
                        class="text-center"
                        :class="date.dayOfTheWeekClass"
                      >
                        <div>{{ date.day }}</div>
                        <div>{{ resolveDayOfWeek(date.dayOfTheWeek) }}</div>
                      </th>
                      <th class="text-center">計</th>
                    </tr>
                  </thead>
                  <tbody v-if="!hasEntries" class="no-data">
                    <tr>
                      <td class="pt-2 text-center" :colspan="4 + dateRange.length">データがありません</td>
                    </tr>
                  </tbody>
                  <template v-else>
                    <tbody v-for="({ key, ...item }, i) in entries" :key="`tbody_${i}`">
                      <tr>
                        <td class="text-center" :rowspan="2">
                          <v-simple-checkbox
                            v-model="selections[key]"
                            :disabled="isFixed"
                            :ripple="false"
                          />
                        </td>
                        <td
                          :class="[ 'text-start', $style.clickable]"
                          :rowspan="2"
                          @click="() => editEntry({ key, ...item }, i)"
                        >
                          <div>{{ item.slot.start }} 〜 {{ item.slot.end }}</div>
                          <div class="mt-1">
                            <span v-if="isOwnExpense(item)">
                              {{ resolveOwnExpenseProgramName(item.ownExpenseProgramId) }}
                            </span>
                            <z-promised
                              v-else
                              v-slot="{ data }"
                              tag="span"
                              :data-service-code="item.serviceCode"
                              :promise="lookupLtcsHomeVisitLongTermCareName(item.serviceCode)"
                            >
                              {{ data }}
                            </z-promised>
                            <v-icon v-if="!isFixed" right small>{{ $icons.edit }}</v-icon>
                          </div>
                        </td>
                        <td class="text-center not-clickable">予定</td>
                        <td
                          v-for="(date, j) in dateRange"
                          :key="`plan${j}_${date.day}`"
                          :class="['text-center', date.dayOfTheWeekClass, { clickable: !isFixed }]"
                          v-on="editHandler(() => togglePlan(item, date.date))"
                        >
                          {{ item.plans.includes(date.date) ? '1' : '' }}
                        </td>
                        <td class="text-center">{{ item.plans.length }}</td>
                      </tr>
                      <tr :class="$style.result">
                        <td class="text-center">実績</td>
                        <td
                          v-for="(date, j) in dateRange"
                          :key="`result${j}_${date.day}`"
                          :class="['text-center', date.dayOfTheWeekClass, { clickable: !isFixed }]"
                          v-on="editHandler(() => toggleResult(item, date.date))"
                        >
                          {{ item.results.includes(date.date) ? '1' : '' }}
                        </td>
                        <td class="text-center">{{ item.results.length }}</td>
                      </tr>
                      <tr :class="$style.dummy"><!-- Vuetify のスタイルを適用させるためのダミーです --></tr>
                    </tbody>
                  </template>
                </template>
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
        <validation-observer v-slot="{ errors: observerErrors }" ref="observer" tag="div">
          <v-alert
            v-if="hasObserverErrors(observerErrors)"
            class="text-caption text-sm-body-2 mt-6"
            data-errors
            dense
            type="error"
          >
            <template v-for="(error, i) in identityScoreErrors(observerErrors)">
              {{ error }}<br :key="i">
            </template>
          </v-alert>
          <v-row class="pt-4" dense justify="end">
            <v-col cols="12" sm="6">
              <v-row class="ma-0">
                <v-simple-table data-addition-table :class="$style.additionTable" dense>
                  <tbody>
                    <tr v-for="(v, i) in additionLabels" :key="`addition_${i}`">
                      <th class="text-right text--secondary" :class="$style.key">{{ v[0] }}</th>
                      <td :class="$style.value">
                        <span v-if="additionsProgress">
                          <v-progress-circular indeterminate color="secondary" :size="18" :width="2" />
                          取得中...
                        </span>
                        <span v-else>{{ v[1] }}</span>
                      </td>
                    </tr>
                  </tbody>
                </v-simple-table>
              </v-row>
              <v-row class="ma-0 pt-2" dense>
                <div class="d-flex justify-end" :class="$style.updateAdditionsButtonWrapper">
                  <v-btn color="primary" data-update-additions-button text :disabled="isFixed" @click="updateAdditions">
                    <v-icon left>{{ $icons.refresh }}</v-icon>
                    加算情報を更新する
                  </v-btn>
                </div>
              </v-row>
            </v-col>
            <v-col cols="12" sm="6" class="d-flex justify-sm-end">
              <v-simple-table :class="$style.summaryTable" dense>
                <thead>
                  <tr>
                    <th></th>
                    <th class="text-right">予定</th>
                    <th class="text-right">実績</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th class="text-right text--secondary" :class="$style.key">限度額管理対象単位数</th>
                    <td :class="$style.value">
                      <z-text-field
                        disabled
                        hide-details
                        hide-spin-buttons
                        :loading="scoreSummaryProgress"
                        :value="numeral(managedScores.plan)"
                      />
                    </td>
                    <td :class="$style.value">
                      <z-text-field
                        disabled
                        hide-details
                        hide-spin-buttons
                        :loading="scoreSummaryProgress"
                        :value="numeral(managedScores.result)"
                      />
                    </td>
                  </tr>
                  <tr>
                    <th class="text-right text--secondary" :class="$style.key">種類支給限度基準を超える単位数</th>
                    <td :class="$style.value">
                      <validation-provider
                        v-slot="{ errors: scoreErrors }"
                        data-plan-max-benefit-quota-excess-score
                        tag="div"
                        vid="plan.maxBenefitQuotaExcessScore"
                        :rules="rules.plan.maxBenefitQuotaExcessScore"
                      >
                        <z-text-field
                          v-model="overMaxScoreData.plan.maxBenefitQuotaExcessScore"
                          :error="!!scoreErrors.length"
                          :disabled="isFixed"
                          type="number"
                          hide-details
                          hide-spin-buttons
                        />
                      </validation-provider>
                    </td>
                    <td :class="$style.value">
                      <validation-provider
                        v-slot="{ errors: scoreErrors }"
                        data-result-max-benefit-quota-excess-score
                        tag="div"
                        vid="result.maxBenefitQuotaExcessScore"
                        :rules="rules.result.maxBenefitQuotaExcessScore"
                      >
                        <z-text-field
                          v-model="overMaxScoreData.result.maxBenefitQuotaExcessScore"
                          :error="!!scoreErrors.length"
                          :disabled="isFixed"
                          type="number"
                          hide-details
                          hide-spin-buttons
                        />
                      </validation-provider>
                    </td>
                  </tr>
                  <tr>
                    <th class="text-right text--secondary" :class="$style.key">種類支給限度基準内単位数</th>
                    <td :class="$style.value">
                      <z-text-field
                        disabled
                        hide-details
                        hide-spin-buttons
                        :value="withinMaxBenefitQuotaScores.plan"
                      />
                    </td>
                    <td :class="$style.value">
                      <z-text-field
                        disabled
                        hide-details
                        hide-spin-buttons
                        :value="withinMaxBenefitQuotaScores.result"
                      />
                    </td>
                  </tr>
                  <tr>
                    <th class="text-right text--secondary" :class="$style.key">区分支給限度基準を超える単位数</th>
                    <td :class="$style.value">
                      <validation-provider
                        v-slot="{ errors: scoreErrors }"
                        data-plan-max-benefit-excess-score
                        tag="div"
                        vid="plan.maxBenefitExcessScore"
                        :rules="rules.plan.maxBenefitExcessScore"
                      >
                        <z-text-field
                          v-model="overMaxScoreData.plan.maxBenefitExcessScore"
                          :error="!!scoreErrors.length"
                          :disabled="isFixed"
                          type="number"
                          hide-details
                          hide-spin-buttons
                        />
                      </validation-provider>
                    </td>
                    <td :class="$style.value">
                      <validation-provider
                        v-slot="{ errors: scoreErrors }"
                        data-result-max-benefit-excess-score
                        tag="div"
                        vid="result.maxBenefitExcessScore"
                        :rules="rules.result.maxBenefitExcessScore"
                      >
                        <z-text-field
                          v-model="overMaxScoreData.result.maxBenefitExcessScore"
                          :error="!!scoreErrors.length"
                          :disabled="isFixed"
                          type="number"
                          hide-details
                          hide-spin-buttons
                        />
                      </validation-provider>
                    </td>
                  </tr>
                  <tr>
                    <th class="text-right text--secondary" :class="$style.key">区分支給限度基準内単位数</th>
                    <td :class="$style.value">
                      <z-text-field
                        disabled
                        hide-details
                        hide-spin-buttons
                        :value="numeral(withinMaxBenefitScores.plan)"
                      />
                    </td>
                    <td :class="$style.value">
                      <z-text-field
                        disabled
                        hide-details
                        hide-spin-buttons
                        :value="numeral(withinMaxBenefitScores.result)"
                      />
                    </td>
                  </tr>
                  <tr>
                    <th class="text-right text--secondary" :class="$style.key">給付単位数</th>
                    <td v-for="(x, i) in scoreSummaries" :key="`summary_${i}`" :class="$style.value">
                      <z-text-field
                        disabled
                        hide-details
                        hide-spin-buttons
                        :loading="scoreSummaryProgress"
                        :value="numeral(x)"
                      />
                    </td>
                  </tr>
                </tbody>
              </v-simple-table>
            </v-col>
          </v-row>
        </validation-observer>
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
            <v-btn v-if="isFixed" class="flex-grow-1 flex-shrink-1" color="accent" data-remand @click="remand">
              <v-icon left>{{ $icons.edit }}</v-icon>
              予実を作成中にする
            </v-btn>
            <v-btn v-else class="flex-grow-1" color="accent" data-confirm :disabled="!canConfirm" @click="confirm">
              <v-icon left>{{ $icons.confirmed }}</v-icon>
              予実を確定する
            </v-btn>
          </v-col>
          <v-col v-if="isShowDeleteButton" class="d-flex mt-2 mt-md-0" cols="12" order-md="first" md="3">
            <v-btn color="danger" data-delete-ltcs-provision-report-button text @click="deleteLtcsProvisionReport">
              <v-icon left>{{ $icons.delete }}</v-icon>
              削除
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </z-data-card>
    <z-ltcs-provision-report-entry-browsing-dialog
      v-if="isFixed"
      ref="ltcsProvisionReportEntryBrowsingDialog"
      :is-effective-on="providedIn"
      :office-id="officeId"
      :show="showDialog"
      :value="entryBeingEdited.entry"
      :width="dialogWidth"
      @click:close="closeDialog"
    />
    <z-ltcs-provision-report-entry-form-dialog
      v-else
      ref="ltcsProvisionReportEntryFormDialog"
      :is-effective-on="providedIn"
      :office-id="officeId"
      :show="showDialog"
      :value="entryBeingEdited"
      :width="dialogWidth"
      @click:cancel="closeDialog"
      @click:save="storeEntry"
    >
      <template #title>{{ dialogTitle }}</template>
      <template #positive-label>{{ dialogPositiveLabel }}</template>
    </z-ltcs-provision-report-entry-form-dialog>
    <z-date-confirm-dialog
      data-date-confirm-dialog="download"
      message="印字する作成日を選択してください"
      positive-label="ダウンロード"
      :active="fileDownloadDialog.isActive.value"
      @click:negative="fileDownloadDialog.cancel"
      @click:positive="fileDownloadDialog.run"
    >
      <template #option>
        <div class="mt-4 pl-3">
          <v-checkbox
            v-model="fileDownloadDialog.needsMaskingInsNumber.value"
            class="py-0"
            dense
            hide-details
            label="被保険者番号を伏せ字にする"
          />
          <v-checkbox
            v-model="fileDownloadDialog.needsMaskingInsName.value"
            class="py-0 mt-0"
            dense
            hide-details
            label="被保険者氏名を伏せ字にする"
          />
        </div>
      </template>
    </z-date-confirm-dialog>
    <z-prompt-dialog
      ref="navigationDialog"
      :active="navigationDialog.isActive.value"
      :cancelable="false"
      :options="navigationDialog.options"
      @click:positive="navigationDialog.run"
    />
    <z-progress :value="isShowProgress && inProgress" />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, ref, useCssModule, watch } from '@nuxtjs/composition-api'
import { DayOfWeek, resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import {
  HomeVisitLongTermCareSpecifiedOfficeAddition,
  resolveHomeVisitLongTermCareSpecifiedOfficeAddition
} from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import {
  LtcsBaseIncreaseSupportAddition,
  resolveLtcsBaseIncreaseSupportAddition
} from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import {
  LtcsOfficeLocationAddition,
  resolveLtcsOfficeLocationAddition
} from '@zinger/enums/lib/ltcs-office-location-addition'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import {
  LtcsProvisionReportStatus,
  resolveLtcsProvisionReportStatus
} from '@zinger/enums/lib/ltcs-provision-report-status'
import {
  LtcsSpecifiedTreatmentImprovementAddition,
  resolveLtcsSpecifiedTreatmentImprovementAddition
} from '@zinger/enums/lib/ltcs-specified-treatment-improvement-addition'
import {
  LtcsTreatmentImprovementAddition,
  resolveLtcsTreatmentImprovementAddition
} from '@zinger/enums/lib/ltcs-treatment-improvement-addition'
import { Permission } from '@zinger/enums/lib/permission'
import { assert, assign, clone, debounce, range } from '@zinger/helpers'
import clonedeep from 'lodash.clonedeep'
import { DateTime, Interval } from 'luxon'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { datetime } from '~/composables/datetime'
import { generateUuid } from '~/composables/generate-uuid'
import { numeral } from '~/composables/numeral'
import { selectOptions } from '~/composables/select-options'
import {
  ltcsProvisionReportStateKey,
  ltcsProvisionReportStoreKey
} from '~/composables/stores/use-ltcs-provision-report-store'
import { ltcsProvisionReportsStateKey } from '~/composables/stores/use-ltcs-provision-reports-store'
import { useOwnExpenseProgramResolverStore } from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useDeleteFunction } from '~/composables/use-delete-function'
import { useInjected } from '~/composables/use-injected'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { useLookupLtcsHomeVisitLongTermCareName } from '~/composables/use-ltcs-home-visit-long-term-care-name'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { useProvisionReportStatusIcon } from '~/composables/use-provision-report-status-icon'
import { useSelections } from '~/composables/use-selections'
import { auth } from '~/middleware/auth'
import { DateLike, ISO_MONTH_FORMAT, OLDEST_DATE } from '~/models/date'
import { LtcsProvisionReport } from '~/models/ltcs-provision-report'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { TimeRange } from '~/models/range'
import { scheduleFromTimeRange } from '~/models/schedule'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'
import { $datetime } from '~/services/datetime-service'
import { observerRef } from '~/support/reactive'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Row = LtcsProvisionReportEntry & { key: string }

type EntryBeingEdited = {
  index?: number
  entry?: LtcsProvisionReportEntry & { key?: string }
}

export default defineComponent({
  name: 'LtcsProvisionReportPage',
  middleware: [auth(Permission.updateLtcsProvisionReports)],
  setup () {
    const isShowProgress = ref(true)
    const { $api, $confirm, $download, $form, $route, $router, $snackbar, $vuetify } = usePlugins()
    const { execute } = useJobWithNotification()
    const { errors, progress: axiosProgress, withAxios } = useAxios()
    const { user } = useInjected(userStateKey)
    const store = useInjected(ltcsProvisionReportStoreKey)
    const { ltcsProvisionReport } = useInjected(ltcsProvisionReportStateKey)
    const { ltcsProvisionReports } = useInjected(ltcsProvisionReportsStateKey)
    const userOptions = selectOptions(
      ltcsProvisionReports.value.map(x => ({ value: x.userId, text: x.name.displayName }))
    )
    const getUserIndex = (userId: number) => userOptions.findIndex(x => x.value === userId)
    const params = $route.params
    const officeId = parseInt(params.officeId)
    const userId = parseInt(params.userId)
    const providedIn = params.providedIn
    const providedInDateTime = $datetime.parse(providedIn)
    const maxProvidedIn = $datetime.now.plus({ months: 3 }).toFormat(ISO_MONTH_FORMAT)
    const minProvidedIn = DateTime.fromISO(OLDEST_DATE).toFormat(ISO_MONTH_FORMAT)
    const otherProgress = ref(false)
    const inProgress = computed(() => axiosProgress.value || otherProgress.value)

    /*
     * 日付情報
     */
    const endDayOfMonth = providedInDateTime.daysInMonth
    const firstDayOfTheWeek = providedInDateTime.startOf('month').weekday
    const dayOfWeek = (dayOfMonth: number): DayOfWeek => ((firstDayOfTheWeek + dayOfMonth) % 7 || 7) as DayOfWeek
    const style = useCssModule()
    const dayOfWeekClass = (dayOfWeek: DayOfWeek) => {
      switch (dayOfWeek) {
        case DayOfWeek.sat:
          return style.sat
        case DayOfWeek.sun:
          return style.sun
        default:
          return ''
      }
    }
    const dateRange = computed(() => range(1, endDayOfMonth ?? 1).map((v, i) => {
      const dayOfTheWeek = dayOfWeek(i)
      const dayOfMonth = v.toString().padStart(2, '0')
      return {
        date: `${providedIn}-${dayOfMonth}`,
        day: v,
        dayOfTheWeek,
        dayOfTheWeekClass: dayOfWeekClass(dayOfTheWeek)
      }
    }))
    const moveTo = ({ toUserId = userId, toProvidedIn = providedIn }) => {
      $form.verifyBeforeLeaving(() => {
        $router.push(`/ltcs-provision-reports/${officeId}/${toUserId}/${toProvidedIn}`)
      })
    }

    /*
     * サービス提供年月の移動
     */
    const nextMonth = () => moveTo({
      toProvidedIn: providedInDateTime.plus({ months: 1 }).toFormat(ISO_MONTH_FORMAT)
    })
    const prevMonth = () => moveTo({
      toProvidedIn: providedInDateTime.minus({ months: 1 }).toFormat(ISO_MONTH_FORMAT)
    })
    const toMonth = (month: string) => moveTo({ toProvidedIn: month })

    /*
     * 利用者の移動
     */
    const nextUser = () => moveTo({
      toUserId: userOptions[getUserIndex(userId) + 1].value
    })
    const prevUser = () => moveTo({
      toUserId: userOptions[getUserIndex(userId) - 1].value
    })
    const toUser = (userId: number) => moveTo({ toUserId: userId })

    /*
     * サービス情報
     */
    const entries = ref<Row[]>([])
    const hasEntries = computed(() => entries.value.length >= 1)
    const stopWatchingReport = watch(
      ltcsProvisionReport,
      (value: LtcsProvisionReport | undefined) => {
        /*
         * entries が取得済み の場合、更新はせずに監視を終了する
         * 新規登録時は空だが、0 件では保存できないので、2回目の発火時は entries には値が入っている
         */
        if (hasEntries.value) {
          // immediate: true だと stopWatchingReport が存在しない場合があるので呼び出し前にチェックしている
          stopWatchingReport && stopWatchingReport()
        } else {
          entries.value = value?.entries
            ? clone(value.entries.map(x => ({ ...x, key: generateUuid() })))
            : []
        }
      },
      { deep: true, immediate: true }
    )
    const entryBeingEdited = ref<EntryBeingEdited>({
      index: undefined,
      entry: undefined
    })
    const isOwnExpense = (entry: LtcsProvisionReportEntry) => entry.category === LtcsProjectServiceCategory.ownExpense
    const resolveOwnExpenseProgramName = useOwnExpenseProgramResolverStore().state.resolveOwnExpenseProgramName

    /*
     * 予定・実績の ON/OFF 切り替え
     */
    const toggle = (target: DateLike[], date: string) => {
      const index = target.findIndex(v => v === date)
      if (index !== -1) {
        target.splice(index, 1)
      } else {
        target.push(date)
      }
    }
    const togglePlan = (entry: LtcsProvisionReportEntry, date: string) => toggle(entry.plans, date)
    const toggleResult = (entry: LtcsProvisionReportEntry, date: string) => toggle(entry.results, date)

    /*
     * 重複確認用に slot を Luxon の Interval に変換する.
     */
    const rangeToInterval = (range: TimeRange, date: string = '2021-03-01'): Interval => {
      const { start, end } = scheduleFromTimeRange({ date, ...range })
      return Interval.fromISO(`${start}/${end}`)
    }
    /*
     * 予定、および実績が未選択のエントリー（サービス情報）があるかを確認する.
     * いずれかにひとつでもチェックがあれば OK.
     */
    const hasEmptyEntry = (entries: LtcsProvisionReportEntry[]) => {
      return entries.some(x => x.plans.length === 0 && x.results.length === 0)
    }
    /*
     * 予定 or 実績の重複があるかを確認する.
     */
    const hasOverlapping = (entries: LtcsProvisionReportEntry[]) => {
      const xs = entries.filter(x => x.category !== LtcsProjectServiceCategory.ownExpense)
        .map(({ slot, plans, results }) => ({ interval: rangeToInterval(slot), plans, results }))
      return xs.some((a, index) => xs.slice(index + 1).some(b => {
        return a.interval.overlaps(b.interval) && (
          a.plans.some(x => b.plans.includes(x)) || a.results.some(x => b.results.includes(x))
        )
      }))
    }

    /*
     * 状態
     */
    const isEditing = ref(false)
    const isFixed = computed(() => ltcsProvisionReport.value?.status === LtcsProvisionReportStatus.fixed)
    const isNotCreated = computed(() => ltcsProvisionReport.value?.status === LtcsProvisionReportStatus.notCreated)
    const displayStatus = computed(() => {
      const status = ltcsProvisionReport.value?.status || LtcsProvisionReportStatus.notCreated
      const resolved = resolveLtcsProvisionReportStatus(status)
      return isFixed.value ? `${resolved}（${datetime(ltcsProvisionReport.value?.fixedAt || '')}）` : resolved
    })

    /*
     * API の 400 エラーの監視
     */
    watch(
      errors,
      () => {
        if (Object.values(errors.value).length !== 0) {
          const message = isEditing.value
            ? '介護保険サービス予実の保存に失敗しました。'
            : '介護保険サービス予実の状態変更に失敗しました。'
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
          entries.value = await store.getLastPlans({ officeId, userId, providedIn })
            .then(entries => entries.map(x => ({ ...x, key: generateUuid() })))
        }
      } catch {
        $snackbar.error('前月の予定が登録されていないためコピーできません。')
      } finally {
        otherProgress.value = false
      }
    }

    /*
     * 単位数のバリデーション
     */
    const observer = observerRef()
    const useScoresValidation = () => {
      const rules = computed(() => {
        const customQuotaScore = (planOrResult: 'plan' | 'result') => ({
          message: `「種類支給限度基準を超える単位数（${planOrResult === 'plan' ? '予定' : '実績'}）」は0以上、「限度額管理対象単位数」以下の半角数字で入力してください。`,
          validate: (value: string) => +value <= managedScores.value?.[planOrResult]
        })
        const customScore = (planOrResult: 'plan' | 'result') => ({
          message: `「区分支給限度基準を超える単位数（${planOrResult === 'plan' ? '予定' : '実績'}）」は0以上、「種類支給限度基準内単位数以下」以下の半角数字で入力してください。`,
          validate: (value: string) => +value <= withinMaxBenefitQuotaScores.value[planOrResult]
        })
        return validationRules({
          plan: {
            maxBenefitExcessScore: { required, numeric, custom: customScore('plan') },
            maxBenefitQuotaExcessScore: { required, custom: customQuotaScore('plan') }
          },
          result: {
            maxBenefitExcessScore: { required, numeric, custom: customScore('result') },
            maxBenefitQuotaExcessScore: { required, custom: customQuotaScore('result') }
          }
        })
      })
      const identityScoreErrors = (errors: Record<string, string[]>) => {
        const errorsWithName = Object.entries(errors).map(([key, value]) => {
          const [planOrResult, field] = key.split('.')
          const planOrResultString = planOrResult === 'plan' ? '予定' : '実績'
          if (field === 'maxBenefitExcessScore') {
            return value.map(x => x.startsWith('「') ? x : `「区分支給限度基準を超える単位数（${planOrResultString}）」を${x}`)
          } else if (field === 'maxBenefitQuotaExcessScore') {
            return value.map(x => x.startsWith('「') ? x : `「種類支給限度基準を超える単位数（${planOrResultString}）」を${x}`)
          }
          return value
        })
        return errorsWithName.flat()
      }
      const hasObserverErrors = (errors: Record<string, unknown[]>) => {
        return Object.values(errors).some(x => x.length !== 0)
      }
      return {
        hasObserverErrors,
        identityScoreErrors,
        observer,
        rules
      }
    }
    /*
     * 予実の登録・更新
     */
    const save = async () => {
      if (hasEmptyEntry(entries.value)) {
        $snackbar.error('予定、実績がひとつも存在しないサービス情報があるため保存できません。')
        return
      }
      if (hasOverlapping(entries.value)) {
        $snackbar.error('提供時間が重複している予定、もしくは実績があるため保存できません。')
        return
      }
      if (!await observer.value?.validate()) {
        return
      }
      await withAxios(
        async () => {
          await store.update({
            officeId,
            userId,
            providedIn,
            form: {
              entries: entries.value.map(({ key: _, ...entry }) => entry),
              ...additions,
              ...overMaxScoreData
            }
          })
          $snackbar.success('介護保険サービス予実を保存しました。')
          isEditing.value = false
          errors.value = {}
        },
        _ => $snackbar.error('介護保険サービス予実の保存に失敗しました。')
      )
    }
    /*
     * 予実の状態変更
     */
    const updateStatus = (status: LtcsProvisionReportStatus) => withAxios(
      async () => {
        await store.updateStatus({ officeId, userId, providedIn, form: { status } })
        const label = resolveLtcsProvisionReportStatus(status)
        $snackbar.success(`介護保険サービス予実の状態を${label}に変更しました。`)
        errors.value = {}
      },
      _ => $snackbar.error('介護保険サービス予実の状態変更に失敗しました。')
    )
    const confirm = async () => await updateStatus(LtcsProvisionReportStatus.fixed)
    const remand = async () => await updateStatus(LtcsProvisionReportStatus.inProgress)
    /*
     * 保存した予実を削除
     */
    const deleteLtcsProvisionReport = useDeleteFunction(ltcsProvisionReport, x => ({
      messageOnConfirm: '予定・実績を削除します。一度削除した予定・実績は元に戻せません。\n\n本当によろしいですか？',
      messageOnSuccess: '予定・実績を削除しました。',
      returnTo: '/ltcs-provision-reports',
      callback: () => $api.ltcsProvisionReports.delete({
        officeId: x.officeId,
        userId: x.userId,
        providedIn
      })
    }))

    // 加算区分
    const useAddition = () => {
      // 各種加算区分を保持するための型
      // HomeVisitLongTermCareCalcSpec から取得するケースと LtcsProvisionReport から取得するケースがある
      // また HomeVisitLongTermCareCalcSpec は API 経由で取得するため、取得完了までの初期値として undefined を許容する
      type Data = {
        specifiedOfficeAddition: HomeVisitLongTermCareSpecifiedOfficeAddition | undefined
        treatmentImprovementAddition: LtcsTreatmentImprovementAddition | undefined
        specifiedTreatmentImprovementAddition: LtcsSpecifiedTreatmentImprovementAddition | undefined
        baseIncreaseSupportAddition: LtcsBaseIncreaseSupportAddition | undefined
        locationAddition: LtcsOfficeLocationAddition | undefined
      }
      const data: Data = reactive({
        specifiedOfficeAddition: undefined,
        treatmentImprovementAddition: undefined,
        specifiedTreatmentImprovementAddition: undefined,
        baseIncreaseSupportAddition: undefined,
        locationAddition: undefined
      })
      const setData = (x: Data | undefined = undefined) => {
        if (x) {
          data.specifiedOfficeAddition = x.specifiedOfficeAddition
          data.treatmentImprovementAddition = x.treatmentImprovementAddition
          data.specifiedTreatmentImprovementAddition = x.specifiedTreatmentImprovementAddition
          data.baseIncreaseSupportAddition = x.baseIncreaseSupportAddition
          data.locationAddition = x.locationAddition
        } else {
          data.specifiedOfficeAddition = undefined
          data.treatmentImprovementAddition = undefined
          data.specifiedTreatmentImprovementAddition = undefined
          data.baseIncreaseSupportAddition = undefined
          data.locationAddition = undefined
        }
      }
      /*
       * 算定情報未登録時に表示するダイアログ
       */
      const navigationDialog = (() => {
        const active = ref(false)
        const isActive = computed(() => active.value)
        const options = {
          message: '算定情報が登録されていないため、予実を登録できません。\n\n算定情報の一覧画面に遷移します。'
        }
        const run = () => {
          active.value = false
          $router.push(`/offices/${officeId}#calc-specs`)
        }
        const show = () => { active.value = true }
        return { isActive, options, run, show }
      })()
      const progress = ref<boolean>(false)
      const updateAdditions = async () => {
        try {
          progress.value = true
          const params: HomeVisitLongTermCareCalcSpecsApi.GetOneParams = {
            officeId,
            passthroughErrors: true,
            providedIn
          }
          const { homeVisitLongTermCareCalcSpec } = await $api.homeVisitLongTermCareCalcSpecs.getOne(params)
          isEditing.value = true
          setData(homeVisitLongTermCareCalcSpec)
        } catch {
          navigationDialog.show()
          setData()
        } finally {
          progress.value = false
        }
      }

      // 表示側で v-for したい都合上 [[key, value]] の組み合わせ
      const additionLabels = computed(() => [
        [
          '特定事業所加算',
          resolveHomeVisitLongTermCareSpecifiedOfficeAddition(data.specifiedOfficeAddition)
        ],
        [
          '処遇改善加算',
          resolveLtcsTreatmentImprovementAddition(data.treatmentImprovementAddition)
        ],
        [
          '特定処遇改善加算',
          resolveLtcsSpecifiedTreatmentImprovementAddition(data.specifiedTreatmentImprovementAddition)
        ],
        [
          'ベースアップ等支援加算',
          resolveLtcsBaseIncreaseSupportAddition(data.baseIncreaseSupportAddition)
        ],
        [
          '地域加算',
          resolveLtcsOfficeLocationAddition(data.locationAddition)
        ]
      ])
      // 初期化処理
      $form.preventUnexpectedUnload()
      if (ltcsProvisionReport.value) {
        setData(ltcsProvisionReport.value)
        $form.watch(isEditing)
      } else {
        updateAdditions()
        $form.watch(() => {
          const isEditingValue = isEditing.value
          const hasEntriesValue = hasEntries.value
          return isEditingValue && hasEntriesValue
        })
      }

      return {
        additions: data,
        additionLabels,
        additionsProgress: progress,
        navigationDialog,
        updateAdditions
      }
    }
    const { additions, additionLabels, additionsProgress, navigationDialog, updateAdditions } = useAddition()

    /*
     * 合計単位数
     */
    const useScoreSummary = () => {
      const { progress, withAxios } = useAxios()
      const initialValue = () => ({
        plan: {
          managedScore: 0,
          unmanagedScore: 0
        },
        result: {
          managedScore: 0,
          unmanagedScore: 0
        }
      })
      const overMaxScoreData = reactive({
        plan: ltcsProvisionReport.value?.plan ?? {
          maxBenefitExcessScore: 0,
          maxBenefitQuotaExcessScore: 0
        },
        result: ltcsProvisionReport.value?.result ?? {
          maxBenefitExcessScore: 0,
          maxBenefitQuotaExcessScore: 0
        }
      })
      const data = reactive(initialValue())
      const managedScores = computed(() => ({ plan: data.plan.managedScore, result: data.result.managedScore }))
      // 超過分
      const withinMaxBenefitQuotaScores = computed(() => ({
        plan: data.plan.managedScore - Number(overMaxScoreData.plan?.maxBenefitQuotaExcessScore),
        result: data.result.managedScore - Number(overMaxScoreData.result?.maxBenefitQuotaExcessScore)
      }))
      const withinMaxBenefitScores = computed(() => ({
        plan: data.plan.managedScore - Number(overMaxScoreData.plan?.maxBenefitQuotaExcessScore) -
          Number(overMaxScoreData.plan?.maxBenefitExcessScore),
        result: data.result.managedScore - Number(overMaxScoreData.result?.maxBenefitQuotaExcessScore) -
          Number(overMaxScoreData.result?.maxBenefitExcessScore)
      }))
      const scoreSummaries = computed(() => [
        withinMaxBenefitScores.value.plan + data.plan.unmanagedScore,
        withinMaxBenefitScores.value.result + data.result.unmanagedScore
      ])
      const refreshScoreSummary = () => withAxios(
        async () => {
          const targetEntries = entries.value.filter(x => {
            // 以下は対象外
            // ・自費サービス
            // ・予実が未入力
            return !(x.category === LtcsProjectServiceCategory.ownExpense ||
              (x.plans.length === 0 && x.results.length === 0))
          })
          const specifiedOfficeAddition = additions.specifiedOfficeAddition
          const treatmentImprovementAddition = additions.treatmentImprovementAddition
          const specifiedTreatmentImprovementAddition = additions.specifiedTreatmentImprovementAddition
          const baseIncreaseSupportAddition = additions.baseIncreaseSupportAddition
          const locationAddition = additions.locationAddition
          const isAdditionsNotAvailable = specifiedOfficeAddition === undefined ||
            treatmentImprovementAddition === undefined ||
            specifiedTreatmentImprovementAddition === undefined ||
            baseIncreaseSupportAddition === undefined ||
            locationAddition === undefined
          if (targetEntries.length === 0 || isAdditionsNotAvailable) {
            assign(data, initialValue())
          } else {
            const form: LtcsProvisionReportsApi.GetScoreSummaryForm = {
              officeId,
              userId,
              providedIn,
              entries: targetEntries,
              plan: overMaxScoreData.plan,
              result: overMaxScoreData.result,
              specifiedOfficeAddition: specifiedOfficeAddition!,
              treatmentImprovementAddition: treatmentImprovementAddition!,
              specifiedTreatmentImprovementAddition: specifiedTreatmentImprovementAddition!,
              baseIncreaseSupportAddition: baseIncreaseSupportAddition!,
              locationAddition: locationAddition!
            }
            assign(data, await $api.ltcsProvisionReports.getScoreSummary({ form }))
          }
        }
      )
      const debouncedRefreshScoreSummary = debounce({ wait: 1000 }, refreshScoreSummary)
      return {
        debouncedRefreshScoreSummary,
        managedScores,
        overMaxScoreData,
        withinMaxBenefitQuotaScores,
        withinMaxBenefitScores,
        progress,
        refreshScoreSummary,
        scoreSummaries
      }
    }
    const {
      debouncedRefreshScoreSummary,
      managedScores,
      overMaxScoreData,
      withinMaxBenefitQuotaScores,
      withinMaxBenefitScores,
      progress: scoreSummaryProgress,
      refreshScoreSummary,
      scoreSummaries
    } = useScoreSummary()

    // 加算情報が更新された時は合計単位数を再計算する
    watch(
      additions,
      () => refreshScoreSummary(),
      { immediate: true }
    )

    watch(
      entries,
      () => {
        isEditing.value = true
        debouncedRefreshScoreSummary()
      },
      { deep: true }
    )

    watch(
      overMaxScoreData,
      () => {
        isEditing.value = true
        debouncedRefreshScoreSummary()
      },
      { deep: true }
    )

    // 帳票ダウンロード
    const download = (
      fileName: string,
      issuedOn: DateLike,
      needsMaskingInsNumber: boolean,
      needsMaskingInsName: boolean
    ) => withAxios(() => {
      isShowProgress.value = false
      const form: LtcsProvisionReportsApi.DownloadForm = {
        officeId,
        userId,
        providedIn,
        issuedOn,
        needsMaskingInsNumber,
        needsMaskingInsName
      }
      return $form.submit(() => execute({
        notificationProps: {
          text: {
            progress: `${fileName}のダウンロードを準備中です...`,
            success: `${fileName}のダウンロードを開始します`,
            failure: `${fileName}のダウンロードに失敗しました`
          }
        },
        process: () => {
          return $api.ltcsProvisionReports.downloadSheets({ form })
        },
        success: job => {
          isShowProgress.value = true
          $download.uri(job.data.uri, job.data.filename)
        }
      }))
    })
    const createFileDownloadDialog = () => {
      const needsMaskingInsNumber = ref(false)
      const needsMaskingInsName = ref(false)
      const resetNeedsMasking = () => {
        needsMaskingInsNumber.value = false
        needsMaskingInsName.value = false
      }
      const active = ref(false)
      const isActive = computed(() => active.value)
      const show = (isSaved: boolean) => {
        if (!isSaved) {
          $snackbar.error('先に予実を保存してください。')
          return
        }
        active.value = true
      }
      const cancel = () => {
        active.value = false
        resetNeedsMasking()
      }
      const run = (date: DateLike) => {
        active.value = false
        download('サービス提供票', date, needsMaskingInsNumber.value, needsMaskingInsName.value)
        resetNeedsMasking()
      }
      return {
        isActive,
        cancel,
        needsMaskingInsNumber,
        needsMaskingInsName,
        run,
        show
      }
    }

    const useEntry = () => {
      /*
       * サービス情報の追加・編集ダイアログ
       */
      const showDialog = ref(false)
      const actionText = ref<'追加' | '編集' | 'コピー'>('追加')
      const dialogTitle = computed(() => {
        const text = actionText.value === 'コピー'
          ? 'コピーして追加'
          : actionText.value
        return `サービス情報を${text}`
      })
      const dialogPositiveLabel = computed(() => {
        return actionText.value === 'コピー'
          ? '追加'
          : actionText.value
      })
      const dialogWidth = computed(() => {
        if ($vuetify.breakpoint.smAndDown) {
          return '90%'
        } else if ($vuetify.breakpoint.mdOnly) {
          return '75%'
        } else {
          return '50%'
        }
      })
      const openDialog = (x?: EntryBeingEdited) => {
        entryBeingEdited.value = {
          index: x?.index,
          entry: x?.entry ? { ...x.entry } : undefined
        }
        showDialog.value = true
      }
      const closeDialog = () => {
        showDialog.value = false
        // 画面がチラつくので少し遅らせる
        setTimeout(() => {
          entryBeingEdited.value = {
            index: undefined,
            entry: undefined
          }
        }, 200)
      }
      const storeEntry = ({ index, entry }: EntryBeingEdited) => {
        assert(entry !== undefined, 'entry is a required property in this function.')
        if (index !== undefined && entry.key) {
          entries.value.splice(index, 1, entry as Row)
        } else {
          entries.value.push({ ...entry, key: generateUuid() })
        }
        closeDialog()
      }
      const addEntry = () => {
        actionText.value = '追加'
        openDialog()
      }
      const editEntry = (entry: Row, index: number) => {
        actionText.value = '編集'
        openDialog({ entry, index })
      }

      const keys = computed(() => entries.value.map(x => x.key))
      /*
       * サービス情報のコピー
       */
      const copyEntry = (selectedKeys: string[]) => {
        const key = selectedKeys[0]
        const index = entries.value.findIndex(x => x.key === key)
        // key に別名を付ける必要はないが、使っていないことが分かりやすいため、_ を設定する
        const { key: _, ...entry } = { ...clonedeep(entries.value[index]), plans: [], results: [] }
        actionText.value = 'コピー'
        openDialog({ entry })
      }
      /*
       * サービス情報の削除
       */
      const deleteEntry = async (selectedKeys: string[]) => {
        const confirmed = await $confirm.show({
          color: 'danger',
          message: 'サービス情報を削除します。\n\n本当によろしいですか？',
          positive: '削除'
        })
        if (confirmed) {
          selectedKeys.forEach(key => {
            const index = keys.value.indexOf(key)
            if (index !== -1) {
              entries.value.splice(index, 1)
            }
          })
        }
      }
      return {
        ...useSelections(keys),
        addEntry,
        closeDialog,
        copyEntry,
        deleteEntry,
        dialogPositiveLabel,
        dialogTitle,
        dialogWidth,
        editEntry,
        openDialog,
        showDialog,
        storeEntry
      }
    }
    return {
      ...useAuth(),
      ...useBreadcrumbs('ltcsProvisionReports.edit'),
      ...useEntry(),
      ...useLookupLtcsHomeVisitLongTermCareName(providedIn),
      ...useOffices({ permission: Permission.updateLtcsProvisionReports }),
      ...useProvisionReportStatusIcon(ltcsProvisionReport),
      ...useScoresValidation(),
      additions,
      additionLabels,
      additionsProgress,
      canConfirm: computed(() => !isEditing.value && hasEntries.value),
      canSave: computed(() => !isFixed.value && isEditing.value && hasEntries.value),
      confirm,
      copyPlans,
      dateRange,
      deleteLtcsProvisionReport,
      displayStatus,
      editHandler: (fn: () => void) => isFixed.value ? {} : { click: fn },
      entries,
      entryBeingEdited,
      errors: computed(() => Object.values(errors.value).flat()),
      fileDownloadDialog: createFileDownloadDialog(),
      hasEntries,
      inProgress,
      isFirstUser: computed(() => getUserIndex(userId) === 0),
      isFixed,
      isLastUser: computed(() => getUserIndex(userId) === userOptions.length - 1),
      isMaxProvidedIn: computed(() => providedIn === maxProvidedIn),
      isMinProvidedIn: computed(() => providedIn === minProvidedIn),
      isOwnExpense,
      isShowDeleteButton: computed(() => ltcsProvisionReport.value?.status && !isNotCreated.value),
      isShowProgress,
      managedScores,
      overMaxScoreData,
      withinMaxBenefitQuotaScores,
      withinMaxBenefitScores,
      maxProvidedIn,
      navigationDialog,
      nextMonth,
      nextUser,
      numeral,
      officeId,
      prevMonth,
      prevUser,
      providedIn,
      remand,
      resolveDayOfWeek,
      resolveLtcsProvisionReportStatus,
      resolveOwnExpenseProgramName,
      save,
      scoreSummaries,
      scoreSummaryProgress,
      togglePlan,
      toggleResult,
      toMonth,
      toUser,
      updateAdditions,
      user,
      userId,
      userOptions
    }
  },
  head: () => ({
    title: '介護保険サービス予実を登録・編集'
  })
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/settings/colors';
@import '~vuetify/src/styles/styles.sass';

// SASS と CSS で `min` 関数が衝突するため再定義する
@function min($values...) {
  @return unquote('min(' + $values + ')');
}

.dummy {
  display: none;
}

.result {
  background-image: radial-gradient(map-get($yellow, 'lighten-2') 50%, transparent 50%) !important;
  background-repeat: repeat !important;
  background-size: 4px 4px !important;
}

.sat {
  background-color: rgba(144, 202, 249, 0.6);
  color: map-get($blue, 'darken-2');
}

.sun {
  background-color: rgba(248, 187, 208, 0.6);
  color: map-get($red, 'darken-2');
}

tbody:not(.no-data) {
  td.clickable:hover {
    background-color: rgba(224, 224, 224, 0.6);
    cursor: pointer;
  }
}

.entryTable {
  th,
  td {
    &:first-of-type {
      padding: 0 !important;
    }

    &:not(:first-of-type) {
      padding: 0 8px !important;
    }
  }
}

.additionTable,
.summaryTable {
  td {
    padding: 8px !important;
  }

  .key {
    text-align: right;
  }
}

$addition-table-key-width: 168px;
$addition-table-value-width: 240px;
$addition-table-width: $addition-table-key-width + $addition-table-value-width;
$summary-table-key-width: 216px;
$summary-table-value-width: 96px;
$summary-table-width: $summary-table-key-width + $summary-table-value-width * 2;

.additionTable {
  max-width: $addition-table-width;

  .key {
    width: $addition-table-key-width;
  }

  .value {
    width: $addition-table-value-width;
  }

  @media #{map-get($display-breakpoints, 'xs-only')} {
    max-width: 100%;
    width: 100%;

    .key {
      width: 55%;
    }

    .value {
      width: 45%;
    }
  }
}

.updateAdditionsButtonWrapper {
  // addition table と横幅を揃える
  width: min(100vw, $addition-table-width);
}

.summaryTable {
  max-width: $summary-table-width;

  .key {
    width: $summary-table-key-width;
  }

  .value {
    width: $summary-table-value-width;

    input {
      text-align: right;
    }
  }

  :global {
    .v-input__slot {
      min-height: 32px !important;
    }
  }

  @media #{map-get($display-breakpoints, 'xs-only')} {
    max-width: 100%;
    width: 100%;

    .key {
      width: 55%;
    }

    .value {
      width: 22.5%;
    }
  }
}

.entryTable,
.additionTable,
.summaryTable {
  :global {
    tr:hover {
      background: inherit !important;
    }
  }
}
</style>
