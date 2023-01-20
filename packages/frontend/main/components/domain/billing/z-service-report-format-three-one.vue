<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-overflow-shadow :class="$style.root">
    <v-simple-table class="service-report" dense>
      <template #default>
        <thead>
          <tr>
            <th class="text-right" style="min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">日付</span>
            </th>
            <th class="text-center" style="min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">曜日</span>
            </th>
            <th style="min-width: 36px" :rowspan="3"></th>
            <th class="text-center" style="min-width: 320px" :rowspan="3">サービス提供の状況</th>
            <th class="text-center" style="height: 52px" :colspan="4">重度訪問介護計画</th>
            <th class="text-center" :colspan="2" :rowspan="2">サービス<br>提供時間</th>
            <th class="text-center" :colspan="2" :rowspan="2">算定時間数</th>
            <th class="text-center" style="min-height: 52px; min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">派遣人数</span>
            </th>
            <th class="text-center" style="min-height: 52px; min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">同行支援</span>
            </th>
            <th class="text-center" style="min-height: 52px; min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">初回加算</span>
            </th>
            <th class="text-center" style="min-height: 88px; min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">緊急時対応加算</span>
            </th>
            <th class="text-center" style="min-height: 124px; min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">行動障害支援連携加算</span>
            </th>
            <th class="text-center" style="min-height: 138px; min-width: 42px" :rowspan="3">
              <span :class="$style.writingVertical">移動介護緊急時支援加算</span>
            </th>
            <th class="text-center" :rowspan="3">備考</th>
          </tr>
          <tr>
            <th class="text-center" :rowspan="2">開始<br>時間</th>
            <th class="text-center" :rowspan="2">終了<br>時間</th>
            <th class="text-center" :colspan="2">計画時間数</th>
          </tr>
          <tr>
            <th style="min-width: 48px" class="text-right">時間</th>
            <th style="min-width: 48px" class="text-right">移動</th>
            <th class="text-center">開始<br>時間</th>
            <th class="text-center">終了<br>時間</th>
            <th style="min-width: 48px" class="text-right">時間</th>
            <th style="min-width: 48px" class="text-right">移動</th>
          </tr>
          <tr>
            <th style="height: 4px" :colspan="20"><!-- Vuetify のスタイルを適用させるためのダミーです --></th>
          </tr>
        </thead>
        <tbody v-if="report.items">
          <tr v-for="(item, i) in report.items" :key="`tr_${i}`">
            <template v-for="(x, j) in [getDateInfo(item.providedOn)]">
              <td :key="`day_${j}`" class="text-right">{{ x.day }}</td>
              <td :key="`dayOfWeek_${j}`" class="text-center">{{ x.dayOfWeek }}</td>
            </template>
            <td></td>
            <td>{{ resolveDwsGrantedServiceCode(item.serviceType) }}</td>
            <td class="text-center">{{ item.plan && time(item.plan.period.start) }}</td>
            <td class="text-center">{{ item.plan && time(item.plan.period.end) }}</td>
            <td class="text-right">{{ item.plan && calcHours(item.plan.serviceDurationHours) }}</td>
            <td class="text-right">{{ item.plan && calcHours(item.plan.movingDurationHours) }}</td>
            <td class="text-center">{{ item.result && time(item.result.period.start) }}</td>
            <td class="text-center">{{ item.result && time(item.result.period.end) }}</td>
            <td class="text-right">{{ item.result && calcHours(item.result.serviceDurationHours) }}</td>
            <td class="text-right">{{ item.result && calcHours(item.result.movingDurationHours) }}</td>
            <td class="text-center">{{ item.headcount }}</td>
            <td class="text-center">{{ item.isCoaching ? 1 : '' }}</td>
            <td class="text-center">{{ item.isFirstTime ? 1 : '' }}</td>
            <td class="text-center">{{ item.isEmergency ? 1 : '' }}</td>
            <td class="text-center">{{ item.isBehavioralDisorderSupportCooperation ? 1 : '' }}</td>
            <td class="text-center">{{ item.isMovingCareSupport ? 1 : '' }}</td>
            <td class="text-center">
              <v-tooltip
                v-if="item.note !== undefined"
                color="secondary"
                nudge-left="150"
                top
                :max-width="noteWidth"
                :min-width="noteWidth"
                :open-on-click="true"
                :open-on-hover="false"
              >
                {{ item.note }}
                <template #activator="{ on, attrs }">
                  <v-btn icon v-bind="attrs" v-on="on">
                    <v-icon>{{ $icons.note }}</v-icon>
                  </v-btn>
                </template>
              </v-tooltip>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="no-data">
          <tr>
            <td class="pt-2 text-center" :colspan="20">データがありません</td>
          </tr>
        </tbody>
      </template>
    </v-simple-table>
    <v-simple-table class="mt-4 summary" dense>
      <thead>
        <tr>
          <th rowspan="2" style="width: 35px"></th>
          <th rowspan="2" style="width: auto; min-width: 216px"></th>
          <th class="text-center" colspan="2">計画時間数計</th>
          <th class="text-center" colspan="2">算定時間数計</th>
          <th class="text-right" rowspan="2" style="width: 120px">初回加算</th>
          <th class="text-right" rowspan="2" style="width: 120px">緊急時<br>対応加算</th>
          <th class="text-right" rowspan="2" style="width: 120px">行動障害支援<br>連携加算</th>
          <th class="text-right" rowspan="2" style="width: 120px">移動介護<br>緊急時支援加算</th>
        </tr>
        <tr>
          <th class="text-right" style="height: 22px; width: 80px">時間</th>
          <th class="text-right" style="height: 22px; width: 80px">移動</th>
          <th class="text-right" style="height: 22px; width: 80px">時間</th>
          <th class="text-right" style="height: 22px; width: 80px">移動</th>
        </tr>
        <tr>
          <th colspan="13" style="height: 0"><!-- Vuetify のスタイルを適用させるためのダミーです --></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td rowspan="2" :class="$style.noBorderBottom">
            <span :class="$style.writingVertical">合計</span>
          </td>
          <td class="text-left">移動介護</td>
          <td :class="$style.noUseCell"></td>
          <td>{{ totalHours[0][0] }}</td>
          <td :class="$style.noUseCell"></td>
          <td>{{ totalHours[0][1] }}</td>
          <td rowspan="2" :class="$style.noBorderBottom">{{ report.firstTimeCount }} 回</td>
          <td rowspan="2" :class="$style.noBorderBottom">{{ report.emergencyCount }} 回</td>
          <td rowspan="2" :class="$style.noBorderBottom">{{ report.behavioralDisorderSupportCooperationCount }} 回</td>
          <td rowspan="2" :class="$style.noBorderBottom">{{ report.movingCareSupportCount }} 回</td>
        </tr>
        <tr>
          <td class="text-left">重度訪問介護</td>
          <td>{{ totalHours[1][0] }}</td>
          <td :class="$style.noUseCell"></td>
          <td>{{ totalHours[1][1] }}</td>
          <td :class="$style.noUseCell"></td>
        </tr>
      </tbody>
    </v-simple-table>
  </z-overflow-shadow>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import {
  DwsBillingServiceReportAggregateCategory
} from '@zinger/enums/lib/dws-billing-service-report-aggregate-category'
import { DwsBillingServiceReportAggregateGroup } from '@zinger/enums/lib/dws-billing-service-report-aggregate-group'
import { resolveDwsGrantedServiceCode } from '@zinger/enums/lib/dws-granted-service-code'
import { isEmpty } from '@zinger/helpers'
import { numeralWithDivision } from '~/composables/numeral'
import { time } from '~/composables/time'
import { usePlugins } from '~/composables/use-plugins'
import { DateLike } from '~/models/date'
import { DwsBillingServiceReport } from '~/models/dws-billing-service-report'

type Props = {
  report: DwsBillingServiceReport
}

export default defineComponent<Props>({
  name: 'ZServiceReportFormatThreeOne',
  props: {
    report: { type: Object, required: true }
  },
  setup (props: Props) {
    const { $datetime, $vuetify } = usePlugins()
    const dateInfoMap = new Map()
    const getDateInfo = (date: DateLike) => {
      if (dateInfoMap.has(date)) {
        return dateInfoMap.get(date)
      } else {
        const datetime = $datetime.parse(date)
        const info = { day: datetime.day, dayOfWeek: resolveDayOfWeek(datetime.weekday) }
        dateInfoMap.set(date, info)
        return info
      }
    }
    /*
     * 備考
     */
    const noteWidth = computed(() => {
      if ($vuetify.breakpoint.smAndDown) {
        return '90vw'
      } else if ($vuetify.breakpoint.mdOnly) {
        return '60vw'
      } else {
        return '360px'
      }
    })
    /*
     * 合計
     */
    const useTotalHours = () => {
      const group = DwsBillingServiceReportAggregateGroup
      const category = DwsBillingServiceReportAggregateCategory
      const convert = (v?: number) => isEmpty(v) ? v : numeralWithDivision(v, '0,0.0')
      const totalHours = computed(() => {
        const plan = props.report.plan
        const result = props.report.result
        const getHours = (group: DwsBillingServiceReportAggregateGroup) => [
          convert(plan[group][category.categoryTotal]),
          convert(result[group][category.categoryTotal])
        ]
        return [
          getHours(group.outingSupportForPwsd),
          getHours(group.visitingCareForPwsd)
        ]
      })
      return {
        totalHours
      }
    }
    return {
      ...useTotalHours(),
      calcHours: (v?: number) => v ? numeralWithDivision(v, '0,0.0') : '',
      getDateInfo,
      noteWidth,
      resolveDwsGrantedServiceCode,
      time
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/settings/colors';

.root {
  :global {
    .v-data-table {
      &.service-report,
      &.summary {
        > .v-data-table__wrapper > table {
          th,
          td {
            padding: 0 6px;

            &:first-of-type {
              padding-left: 8px;
            }

            &:last-of-type {
              padding-right: 8px;
            }
          }

          th {
            vertical-align: bottom;
          }

          tr {
            &.dummy-row {
              display: none;
            }

            &:hover {
              background: inherit !important;
            }
          }
        }
      }
      &.summary {
        text-align: right;
      }
    }
  }
}

.noUseCell {
  background-color: map-get($grey, 'lighten-3');
}

.noBorderBottom {
  border-bottom: none !important;
}

.writingVertical {
  writing-mode: vertical-rl;
}
</style>
