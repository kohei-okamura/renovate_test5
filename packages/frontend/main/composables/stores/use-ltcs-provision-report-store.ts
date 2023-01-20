/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { assert, assign } from '@zinger/helpers/index'
import { DateTime } from 'luxon'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DateLike, ISO_DATE_FORMAT, ISO_MONTH_FORMAT } from '~/models/date'
import { LtcsProvisionReport } from '~/models/ltcs-provision-report'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { LtcsProvisionReportsApi } from '~/services/api/ltcs-provision-reports-api'

type Plans = LtcsProvisionReportEntry['plans']

export const createLtcsProvisionReportState = () => ({
  ltcsProvisionReport: undefined as LtcsProvisionReport | undefined
})

export const useLtcsProvisionReportStore = () => {
  const { $api, $datetime } = usePlugins()
  const adjustDate = (thisMonth: DateTime, plans: DateTime[]): Plans => {
    assert(plans.length !== 0, 'Invalid Argument: plans must not be empty')
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
    })(plans[0])
    const limit = thisMonth.endOf('month')
    return plans
      .map(x => {
        const d = x.plus({ days: diff })
        // 日付調整で来月になったものは対象外
        return d <= limit
          ? d.toFormat(ISO_DATE_FORMAT)
          : undefined
      })
      .filter(Boolean) as DateLike[]
  }
  const divideByDayOfWeek = (plans: Plans): DateTime[][] => {
    // DateTime に変換して曜日ごとに分割する
    // 現状では曜日の情報は必要ないため、値のみを返す
    return Object.values(plans.reduce<Record<number, DateTime[]>>((acc, cur) => {
      const plan = $datetime.parse(cur)
      const weekday = plan.weekday
      acc[weekday] = [...(acc[weekday] ?? []), plan]
      return acc
    }, {}))
  }
  const copyPlans = (providedIn: DateLike, response?: LtcsProvisionReportsApi.GetResponse) => {
    if (response) {
      const thisMonth = $datetime.parse(providedIn)
      return response.ltcsProvisionReport.entries
        .filter(x => x.plans.length !== 0)
        .map(x => {
          // 曜日ごとに分割し、各曜日ごとに日付を調整後、再度一つの配列にまとめる
          const plans = divideByDayOfWeek(x.plans).flatMap(x => adjustDate(thisMonth, x)).sort()
          return { ...x, plans, results: [] }
        })
    } else {
      throw new Error('The last month does not have report.')
    }
  }
  const state = reactive(createLtcsProvisionReportState())
  const actions = {
    async get (params: LtcsProvisionReportsApi.GetParams) {
      assign(state, await $api.ltcsProvisionReports.get(params))
    },
    async getLastPlans (params: LtcsProvisionReportsApi.GetParams) {
      // 先月にする
      const lastMonth = $datetime.parse(params.providedIn).minus({ months: 1 }).toFormat(ISO_MONTH_FORMAT)
      const response = await $api.ltcsProvisionReports.get({ ...params, providedIn: lastMonth })
      return copyPlans(params.providedIn, response)
    },
    async update (params: Parameters<typeof $api.ltcsProvisionReports.update>[0]) {
      assign(state, await $api.ltcsProvisionReports.update(params))
    },
    async updateStatus (params: Parameters<typeof $api.ltcsProvisionReports.updateStatus>[0]) {
      assign(state, await $api.ltcsProvisionReports.updateStatus(params))
    }
  }
  return createStore({ actions, state })
}

export type LtcsProvisionReportData = ReturnType<typeof createLtcsProvisionReportState>

export type LtcsProvisionReportStore = ReturnType<typeof useLtcsProvisionReportStore>

export type LtcsProvisionReportState = LtcsProvisionReportStore['state']

export const ltcsProvisionReportStoreKey: InjectionKey<LtcsProvisionReportStore> = Symbol('ltcsProvisionReportStore')

export const ltcsProvisionReportStateKey: InjectionKey<LtcsProvisionReportState> = Symbol('ltcsProvisionReportState')
