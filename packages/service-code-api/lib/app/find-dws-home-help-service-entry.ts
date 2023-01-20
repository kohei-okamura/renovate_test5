/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsHomeHelpServiceBuildingType } from '@zinger/enums/lib/dws-home-help-service-building-type'
import { DwsHomeHelpServiceProviderType } from '@zinger/enums/lib/dws-home-help-service-provider-type'
import { DwsServiceCodeCategory } from '@zinger/enums/lib/dws-service-code-category'
import { DWS_HOME_HELP_SERVICE_DB } from '../constants'
import { DwsHomeHelpServiceDictionaryEntry } from '../models/dws-home-help-service-dictionary-entry'
import { RecordType } from '../types'
import { convertKeysToCamelCase } from '../utils/convert-keys-to-camel-case'
import { determineVersion } from '../utils/determine-version'
import { openDatabase } from '../utils/open-database'

export type FindDwsHomeHelpServiceEntryParams = {
  providedIn: Date | string
  category?: DwsServiceCodeCategory
  isExtra: boolean
  isSecondary: boolean
  isPlannedByNovice: boolean
  providerType: DwsHomeHelpServiceProviderType
  buildingType: DwsHomeHelpServiceBuildingType
  daytimeDuration?: number
  morningDuration?: number
  nightDuration?: number
  midnightDuration1?: number
  midnightDuration2?: number
  q?: string
  serviceCodes?: string[]
}

type Params = FindDwsHomeHelpServiceEntryParams
type Entry = DwsHomeHelpServiceDictionaryEntry
type Row = RecordType<DwsHomeHelpServiceDictionaryEntry>

export const findDwsHomeHelpServiceEntry = (params: Params): Promise<Entry[]> => {
  const version = determineVersion(params.providedIn)
  const knex = openDatabase(DWS_HOME_HELP_SERVICE_DB[version])
  const query = Object.entries(params).reduce(
    (q, [key, value]) => {
      if (typeof value === 'undefined') {
        return q
      }
      switch (key as keyof Params) {
        case 'providedIn':
          return q
        case 'isExtra':
        case 'isSecondary':
        case 'isPlannedByNovice':
        case 'buildingType':
        case 'category':
        case 'providerType':
          return q.where(key, '=', value)
        case 'daytimeDuration':
        case 'morningDuration':
        case 'nightDuration':
        case 'midnightDuration1':
        case 'midnightDuration2':
          return q.where(`${key}Start`, '<', value).where(`${key}End`, '>=', value)
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
      isExtra: !!x.isExtra,
      isSecondary: !!x.isSecondary,
      providerType: x.providerType,
      isPlannedByNovice: !!x.isPlannedByNovice,
      buildingType: x.buildingType,
      score: x.score,
      daytimeDuration: {
        start: x.daytimeDurationStart,
        end: x.daytimeDurationEnd
      },
      morningDuration: {
        start: x.morningDurationStart,
        end: x.morningDurationEnd
      },
      nightDuration: {
        start: x.nightDurationStart,
        end: x.nightDurationEnd
      },
      midnightDuration1: {
        start: x.midnightDuration1Start,
        end: x.midnightDuration1End
      },
      midnightDuration2: {
        start: x.midnightDuration2Start,
        end: x.midnightDuration2End
      }
    })))
}
