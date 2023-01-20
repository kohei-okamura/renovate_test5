/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  HomeVisitLongTermCareSpecifiedOfficeAddition
} from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { LTCS_HOME_VISIT_LONG_TERM_CARE_DB } from '../constants'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '../models/ltcs-home-visit-long-term-care-dictionary-entry'
import { RecordType } from '../types'
import { convertKeysToCamelCase } from '../utils/convert-keys-to-camel-case'
import { determineVersion } from '../utils/determine-version'
import { openDatabase } from '../utils/open-database'

export type FindLtcsHomeVisitLongTermCareEntryParams = {
  providedIn: Date | string
  category?: LtcsServiceCodeCategory
  headcount?: number
  houseworkMinutes?: number
  physicalMinutes?: number
  q?: string
  serviceCodes?: string[]
  specifiedOfficeAddition?: HomeVisitLongTermCareSpecifiedOfficeAddition
  timeframe?: Timeframe
  totalMinutes?: number
  limit?: number
}

type Params = FindLtcsHomeVisitLongTermCareEntryParams
type Entry = LtcsHomeVisitLongTermCareDictionaryEntry
type Row = RecordType<LtcsHomeVisitLongTermCareDictionaryEntry>

export const findLtcsHomeVisitLongTermCareEntry = (params: Params): Promise<Entry[]> => {
  const version = determineVersion(params.providedIn)
  const knex = openDatabase(LTCS_HOME_VISIT_LONG_TERM_CARE_DB[version])
  const query = Object.entries(params).reduce(
    (q, [key, value]) => {
      if (typeof value === 'undefined') {
        return q
      }
      switch (key as keyof Params) {
        case 'providedIn':
          return q
        case 'category':
        case 'headcount':
        case 'specifiedOfficeAddition':
        case 'timeframe':
          return q.where(key, '=', value)
        case 'houseworkMinutes':
        case 'physicalMinutes':
        case 'totalMinutes':
          return q.where(`${key}Start`, '<', value).where(`${key}End`, '>=', value)
        case 'serviceCodes':
          return q.whereIn('serviceCode', (Array.isArray(value) ? value : [value]) as string[])
        case 'q':
          return q.where('serviceCode', 'LIKE', `${value}%`)
        case 'limit':
          return q.limit(value as number)
        default:
          throw new Error(`Unsupported parameter: ${key}`)
      }
    },
    knex<Row>('main')
  )
  return query
    .then(rows => rows.map(x => convertKeysToCamelCase<Row>(x)).map<Entry>(x => ({
      serviceCode: x.serviceCode,
      name: x.name,
      category: x.category,
      headcount: x.headcount,
      compositionType: x.compositionType,
      specifiedOfficeAddition: x.specifiedOfficeAddition,
      noteRequirement: x.noteRequirement,
      isLimited: !!x.isLimited,
      isBulkSubtractionTarget: !!x.isBulkSubtractionTarget,
      isSymbioticSubtractionTarget: !!x.isSymbioticSubtractionTarget,
      score: {
        calcCycle: x.scoreCalcCycle,
        calcType: x.scoreCalcType,
        value: x.scoreValue
      },
      extraScore: {
        isAvailable: x.extraScoreIsAvailable,
        baseMinutes: x.extraScoreBaseMinutes,
        unitScore: x.extraScoreUnitScore,
        unitMinutes: x.extraScoreUnitMinutes,
        specifiedOfficeAdditionCoefficient: x.extraScoreSpecifiedOfficeAdditionCoefficient,
        timeframeAdditionCoefficient: x.extraScoreTimeframeAdditionCoefficient
      },
      timeframe: x.timeframe,
      totalMinutes: {
        start: x.totalMinutesStart,
        end: x.totalMinutesEnd
      },
      physicalMinutes: {
        start: x.physicalMinutesStart,
        end: x.physicalMinutesEnd
      },
      houseworkMinutes: {
        start: x.houseworkMinutesStart,
        end: x.houseworkMinutesEnd
      }
    })))
}
