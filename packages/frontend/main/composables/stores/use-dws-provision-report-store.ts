/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assert, assign } from '@zinger/helpers/index'
import { DateTime } from 'luxon'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DateLike, ISO_DATE_FORMAT, ISO_DATETIME_FORMAT, ISO_MONTH_FORMAT } from '~/models/date'
import { DwsProvisionReport } from '~/models/dws-provision-report'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { DwsProvisionReportsApi } from '~/services/api/dws-provision-reports-api'

// コピーの中間オブジェクト
type CopyIntermediate = {
  date: DateTime
  plan: DwsProvisionReportItem
}

export const createDwsProvisionReportState = () => ({
  dwsProvisionReport: undefined as DwsProvisionReport | undefined
})

export const useDwsProvisionReportStore = () => {
  const { $api, $datetime } = usePlugins()
  const adjustDate = (thisMonth: DateTime, items: CopyIntermediate[]): CopyIntermediate[] => {
    assert(items.length !== 0, 'Invalid Argument: items must not be empty')
    // 最初の予定日をベースに調整日数を計算する
    const diff = (baseDate => {
      const daysInWeek = 7
      const day = baseDate.day
      const rest = baseDate.daysInMonth - day
      const adjustWeek = (day => {
        for (let i = 1; i <= 4; i++) {
          if (day < daysInWeek * i + 1) {
            return daysInWeek * (i - 1)
          }
        }
        return daysInWeek * 4
      })(day)
      return rest + (daysInWeek - (rest % daysInWeek)) + adjustWeek
    })(items[0].date)
    const limit = thisMonth.endOf('month')
    return items
      .map(({ date, plan }) => {
        const d = date.plus({ days: diff })
        // 日付調整で来月になったものは対象外
        if (d <= limit) {
          const plus = (date: DateLike) => {
            return $datetime.parse(date).plus({ days: diff }).toFormat(ISO_DATETIME_FORMAT)
          }
          const temp = plan.schedule
          return {
            date: d,
            plan: {
              ...plan,
              schedule: {
                date: d.toFormat(ISO_DATE_FORMAT),
                start: plus(temp.start),
                end: plus(temp.end)
              }
            }
          }
        } else {
          return undefined
        }
      })
      .filter(Boolean) as CopyIntermediate[]
  }
  const divideByDayOfWeek = (plans: DwsProvisionReportItem[]): CopyIntermediate[][] => {
    // 曜日ごとに分割する
    // 現状では曜日の情報は必要ないため、値のみを返す
    return Object.values(plans.reduce<Record<number, CopyIntermediate[]>>((acc, cur) => {
      const date = $datetime.parse(cur.schedule.date)
      const weekday = date.weekday
      acc[weekday] = [...(acc[weekday] ?? []), { date, plan: cur }]
      return acc
    }, {}))
  }
  const copyPlans = (providedIn: DateLike, response?: DwsProvisionReportsApi.GetResponse) => {
    if (response) {
      const plans = response.dwsProvisionReport.plans
      if (plans.length <= 0) {
        return []
      }
      const thisMonth = $datetime.parse(providedIn)
      // 曜日ごとに分割し、各曜日ごとに日付を調整後、再度一つの配列にまとめる
      return divideByDayOfWeek(plans)
        .flatMap(x => adjustDate(thisMonth, x))
        .sort((x, y) => x!.date < y!.date ? -1 : 1) // 日付順に並べ替えなくても表示はできていそうだけど念の為やっておく
        .map(x => x?.plan)
    } else {
      throw new Error('The last month does not have report.')
    }
  }
  const state = reactive(createDwsProvisionReportState())
  const actions = {
    async get (params: DwsProvisionReportsApi.GetParams) {
      assign(state, await $api.dwsProvisionReports.get(params))
    },
    async getLastPlans (params: DwsProvisionReportsApi.GetParams) {
      // 先月にする
      const lastMonth = $datetime.parse(params.providedIn).minus({ months: 1 }).toFormat(ISO_MONTH_FORMAT)
      const response = await $api.dwsProvisionReports.get({ ...params, providedIn: lastMonth })
      return copyPlans(params.providedIn, response)
    },
    async update (params: Parameters<typeof $api.dwsProvisionReports.update>[0]) {
      assign(state, await $api.dwsProvisionReports.update(params))
    },
    async updateStatus (params: Parameters<typeof $api.dwsProvisionReports.updateStatus>[0]) {
      assign(state, await $api.dwsProvisionReports.updateStatus(params))
    }
  }
  return createStore({ actions, state })
}

export type DwsProvisionReportData = ReturnType<typeof createDwsProvisionReportState>

export type DwsProvisionReportStore = ReturnType<typeof useDwsProvisionReportStore>

export type DwsProvisionReportState = DwsProvisionReportStore['state']

export const dwsProvisionReportStoreKey: InjectionKey<DwsProvisionReportStore> = Symbol('dwsProvisionReportStore')

export const dwsProvisionReportStateKey: InjectionKey<DwsProvisionReportState> = Symbol('dwsProvisionReportState')
