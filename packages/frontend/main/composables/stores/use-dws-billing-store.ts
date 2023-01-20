/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey, reactive } from '@nuxtjs/composition-api'
import { DwsBillingServiceReportFormat } from '@zinger/enums/lib/dws-billing-service-report-format'
import {
  DwsBillingStatementCopayCoordinationStatus
} from '@zinger/enums/lib/dws-billing-statement-copay-coordination-status'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBilling, DwsBillingId } from '~/models/dws-billing'
import { DwsBillingBundle, DwsBillingBundleId } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordination } from '~/models/dws-billing-copay-coordination'
import { DwsBillingServiceReport } from '~/models/dws-billing-service-report'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { DwsBillingUser } from '~/models/dws-billing-user'
import { Job } from '~/models/job'
import { StructuredName } from '~/models/structured-name'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { toHiragana } from '~/support/jaco'

type CompareParameter = {
  dwsBillingBundleId: DwsBillingBundleId
  user: DwsBillingUser
}

type TempStatusCount = {
  reports: DwsBillingServiceReport[]
  statements: DwsBillingStatement[]
}

type StatusCount = {
  checking: number
  ready: number
  fixed: number
  disabled: number
  total: number
}

const copayCoordinationStatusKeys = Object.values(DwsBillingStatementCopayCoordinationStatus.keysMap())[0]
type CopayCoordinationStatusCountKeys = Exclude<typeof copayCoordinationStatusKeys, 'unapplicable' | 'unclaimable'> | 'total'
type CopayCoordinationStatusCount = {
  [K in CopayCoordinationStatusCountKeys]: number
}

type StatusCountPerMonth = {
  providedIn: string
  report: StatusCount
  statement: StatusCount
  copayCoordination: CopayCoordinationStatusCount
}

// 画面の表示単位（表の1行分）
export type DwsBillingUnit = {
  // ID
  id: number
  // 利用者名
  userName: StructuredName['displayName']
  // 利用者名:カナ
  userPhoneticName: StructuredName['phoneticDisplayName']
  // 受給者証番号
  dwsNumber: DwsBillingUser['dwsNumber']
  // 市区町村名
  cityName: DwsBillingBundle['cityName']
  // 上限額管理結果票
  copayCoordination: DwsBillingCopayCoordination | undefined
  // サービス提供実績記録票（居宅）
  homeHelpServiceReport: DwsBillingServiceReport | undefined
  // サービス提供実績記録票（重訪）
  visitingCareForPwsdReport: DwsBillingServiceReport | undefined
  // 明細書
  statement: DwsBillingStatement | undefined
}

type DwsBillingUnitsGroup = {
  providedIn: DwsBillingBundle['providedIn']
  units: DwsBillingUnit[]
}

/*
 * 上限管理区分を集計する
 * 「不要」は集計対象外とする
 *
 * @param items copayCoordinationStatus を持つオブジェクトの配列
 */
const createCopayCoordinationStatusCount = (
  items: Array<{ copayCoordinationStatus: DwsBillingStatementCopayCoordinationStatus }>
): CopayCoordinationStatusCount => {
  return items.reduce<CopayCoordinationStatusCount>((acc, { copayCoordinationStatus }) => {
    switch (copayCoordinationStatus) {
      case DwsBillingStatementCopayCoordinationStatus.unapplicable:
      case DwsBillingStatementCopayCoordinationStatus.unclaimable:
        return acc
      case DwsBillingStatementCopayCoordinationStatus.uncreated:
        return { ...acc, ...{ uncreated: acc.uncreated + 1, total: acc.total + 1 } }
      case DwsBillingStatementCopayCoordinationStatus.unfilled:
        return { ...acc, ...{ unfilled: acc.unfilled + 1, total: acc.total + 1 } }
      case DwsBillingStatementCopayCoordinationStatus.checking:
        return { ...acc, ...{ checking: acc.checking + 1, total: acc.total + 1 } }
      case DwsBillingStatementCopayCoordinationStatus.fulfilled:
        return { ...acc, ...{ fulfilled: acc.fulfilled + 1, total: acc.total + 1 } }
      default:
        throw new Error('IllegalStateException')
    }
  }, { uncreated: 0, unfilled: 0, checking: 0, fulfilled: 0, total: 0 })
}

/*
 * ステータスを集計する
 *
 * @param items DwsBillingStatus を持つオブジェクトの配列
 */
const createStatusCount = (items: Array<{ status: DwsBillingStatus }>): StatusCount => {
  const temp = items.reduce<StatusCount>((acc, { status }) => {
    switch (status) {
      case DwsBillingStatus.checking:
        return { ...acc, ...{ checking: acc.checking + 1 } }
      case DwsBillingStatus.ready:
        return { ...acc, ...{ ready: acc.ready + 1 } }
      case DwsBillingStatus.fixed:
        return { ...acc, ...{ fixed: acc.fixed + 1 } }
      case DwsBillingStatus.disabled:
        return { ...acc, ...{ disabled: acc.disabled + 1 } }
      default:
        throw new Error('IllegalStateException')
    }
  }, { checking: 0, ready: 0, fixed: 0, disabled: 0, total: 0 })
  return { ...temp, ...{ total: items.length } }
}

/*
 * 給付費明細のステータスと上限管理区分、サービス提供実績記録票のステータスをサービス提供月ごとに集計する
 *
 * @param bundleDetails
 */
const createStatusCounts = (billingUnitsGroups: DwsBillingUnitsGroup[]): StatusCountPerMonth[] => {
  return billingUnitsGroups.map(({ providedIn, units }) => {
    const { reports, statements } = units.reduce<TempStatusCount>(
      (
        { reports, statements },
        { homeHelpServiceReport, visitingCareForPwsdReport, statement }
      ) => {
        return {
          reports: [
            ...reports,
            ...([homeHelpServiceReport, visitingCareForPwsdReport].filter(x => x) as DwsBillingServiceReport[])
          ],
          statements: statement ? [...statements, statement] : statements
        }
      },
      { reports: [], statements: [] }
    )
    return {
      providedIn,
      report: createStatusCount(reports),
      statement: createStatusCount(statements),
      copayCoordination: createCopayCoordinationStatusCount(statements)
    }
  })
}

/*
 * 各項目を DwsBillingUnit 単位にまとめる
 *
 * @param bundleId
 * @param data
 */
const createBillingUnits = (
  bundleId: DwsBillingBundleId,
  cityName: DwsBillingBundle['cityName'],
  data: Pick<DwsBillingsApi.GetResponse, 'copayCoordinations' | 'reports' | 'statements'>
): DwsBillingUnit[] => {
  const { copayCoordinations, reports, statements } = data
  const isSame = (p1: CompareParameter, p2: CompareParameter) => {
    return p1.dwsBillingBundleId === p2.dwsBillingBundleId && p1.user.userId === p2.user.userId
  }
  return statements
    .filter(({ dwsBillingBundleId }) => bundleId === dwsBillingBundleId)
    .map(statement => {
      return {
        id: statement.id,
        userName: statement.user.name.displayName,
        userPhoneticName: toHiragana(statement.user.name.phoneticDisplayName),
        dwsNumber: statement.user.dwsNumber,
        cityName,
        copayCoordination: copayCoordinations.find(v => isSame(v, statement)),
        homeHelpServiceReport: reports.find(
          v => v.format === DwsBillingServiceReportFormat.homeHelpService && isSame(v, statement)
        ),
        visitingCareForPwsdReport: reports.find(
          v => v.format === DwsBillingServiceReportFormat.visitingCareForPwsd && isSame(v, statement)
        ),
        statement
      }
    })
}

/*
 * DwsBillingUnit をサービス提供年月の降順に下記のようにまとめる
 * [{ サービス提供年月, 利用者名の五十音順でソートした DwsBillingUnit の配列 }]
 *
 * @param data
 */
const createBillingUnitsGroup = (data: Omit<DwsBillingsApi.GetResponse, 'billing'>) => {
  const { bundles, ...rest } = data

  /*
   * DwsBillingUnit を下記のようにまとめる
   * { サービス提供年月: DwsBillingUnit の配列 }
   */
  const tmp = bundles.reduce((acc, bundle) => {
    const units = createBillingUnits(bundle.id, bundle.cityName, rest)
    const key = bundle.providedIn
    return { ...acc, ...{ [key]: acc[key] ? [...acc[key], ...units] : units } }
  }, {} as Record<DwsBillingBundle['providedIn'], DwsBillingUnit[]>)

  return Object.entries(tmp)
    .sort(([k1], [k2]) => k1 < k2 ? 1 : -1)
    .reduce<DwsBillingUnitsGroup[]>((acc, [k, v]) => {
      return [
        ...acc,
        {
          providedIn: k,
          units: v.sort(({ userPhoneticName: name1 }, { userPhoneticName: name2 }) => name2 < name1 ? 1 : -1)
        }
      ]
    }, [])
}

/**
 * API のレスポンスを store 用に整形する
 *
 * @param response
 * @return state
 */
export function convertResponseToState (response: DwsBillingsApi.GetResponse) {
  const { billing, ...rest } = response
  const billingUnitsGroups = createBillingUnitsGroup(rest)
  return {
    billing,
    bundles: rest.bundles,
    billingUnitsGroups,
    statusCounts: createStatusCounts(billingUnitsGroups)
  }
}

export const createDwsBillingState = () => ({
  billing: undefined as DwsBilling | undefined,
  billingUnitsGroups: [] as DwsBillingUnitsGroup[],
  bundles: [] as DwsBillingBundle[],
  statusCounts: [] as StatusCountPerMonth[] | undefined,
  job: undefined as Job | undefined
})

export function useDwsBillingStore () {
  const { $api } = usePlugins()
  const state = reactive(createDwsBillingState())
  const actions = {
    async get (params: DwsBillingsApi.GetParams) {
      const response = await $api.dwsBillings.get(params)
      assign(state, convertResponseToState(response))
    },
    async updateStatus (id: DwsBillingId, status: DwsBillingStatus) {
      const form = { status }
      const response = await $api.dwsBillings.updateStatus({ id, form })
      assign(state, { ...convertResponseToState(response), job: response.job })
    }
  }
  return createStore({ actions, state })
}

export type DwsBillingData = ReturnType<typeof createDwsBillingState>

export type DwsBillingStore = ReturnType<typeof useDwsBillingStore>

export type DwsBillingState = DwsBillingStore['state']

export const dwsBillingStoreKey: InjectionKey<DwsBillingStore> = Symbol('dwsBillingStore')

export const dwsBillingStateKey: InjectionKey<DwsBillingState> = Symbol('dwsBillingState')
