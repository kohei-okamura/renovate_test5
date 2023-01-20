/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { DWS_VISITING_CARE_FOR_PWSD_DB } from '../constants'
import { DwsVisitingCareForPwsdDictionaryEntry } from '../models/dws-visiting-care-for-pwsd-dictionary-entry'
import { RecordType } from '../types'
import { convertKeysToCamelCase } from '../utils/convert-keys-to-camel-case'
import { determineVersion } from '../utils/determine-version'
import { openDatabase } from '../utils/open-database'

export type FindDwsVisitingCareForPwsdEntryParams = {
  providedIn: Date | string
  category?: DwsServiceCodeCategory
  isCoaching: boolean
  isHospitalized: boolean
  isLongHospitalized: boolean
  isSecondary: boolean
  q?: string
  serviceCodes?: string[]
  timeframe: Timeframe
}

type Params = FindDwsVisitingCareForPwsdEntryParams
type Entry = DwsVisitingCareForPwsdDictionaryEntry
type Row = RecordType<DwsVisitingCareForPwsdDictionaryEntry>

export const findDwsVisitingCareForPwsdEntry = (params: Params): Promise<Entry[]> => {
  const version = determineVersion(params.providedIn)
  const knex = openDatabase(DWS_VISITING_CARE_FOR_PWSD_DB[version])
  const query = Object.entries(params).reduce(
    (q, [key, value]) => {
      if (typeof value === 'undefined') {
        return q
      }
      switch (key as keyof Params) {
        case 'providedIn':
          return q
        case 'category':
        case 'isCoaching':
        case 'isHospitalized':
        case 'isLongHospitalized':
        case 'isSecondary':
        case 'timeframe':
          return q.where(key, '=', value)
        case 'serviceCodes':
          return q.whereIn('serviceCode', (Array.isArray(value) ? value : [value]) as string[])
        case 'q':
          return q.where('serviceCode', 'LIKE', `${value}%`)
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
      isSecondary: !!x.isSecondary,
      isCoaching: !!x.isCoaching,
      isHospitalized: !!x.isHospitalized,
      isLongHospitalized: !!x.isLongHospitalized,
      score: x.score,
      timeframe: x.timeframe,
      duration: {
        start: x.durationStart,
        end: x.durationEnd
      },
      unit: x.unit
    })))
}
