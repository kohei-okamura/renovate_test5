/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HomeVisitLongTermCareSpecifiedOfficeAddition } from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsCalcCycle } from '@zinger/enums/lib/ltcs-calc-cycle'
import { LtcsCalcType } from '@zinger/enums/lib/ltcs-calc-type'
import { LtcsCompositionType } from '@zinger/enums/lib/ltcs-composition-type'
import { LtcsNoteRequirement } from '@zinger/enums/lib/ltcs-note-requirement'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { Seq } from 'immutable'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '~/models/ltcs-home-visit-long-term-care-dictionary-entry'
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { rows } from '~~/stubs/create-ltcs-home-visit-long-term-care-dictionary-entry-rows'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, SEEDS } from '~~/stubs/index'

type Entry = LtcsHomeVisitLongTermCareDictionaryEntry

const stubs = Seq(rows.map((row, i) => {
  const id = i + 1
  const faker = createFaker(SEEDS[i])
  return {
    id,
    dictionaryId: 1,
    serviceCode: row[0],
    name: row[3],
    category: row[4] as LtcsServiceCodeCategory,
    headcount: row[5],
    compositionType: row[6] as LtcsCompositionType,
    specifiedOfficeAddition: row[7] as HomeVisitLongTermCareSpecifiedOfficeAddition,
    noteRequirement: row[8] as LtcsNoteRequirement,
    isLimited: !!row[9],
    isBulkSubtractionTarget: !!row[10],
    isSymbioticSubtractionTarget: !!row[11],
    score: {
      value: row[12],
      calcType: row[13] as LtcsCalcType,
      calcCycle: row[14] as LtcsCalcCycle
    },
    extraScore: {
      isAvailable: !!row[15],
      baseMinutes: row[16],
      unitScore: row[17],
      unitMinutes: row[18],
      specifiedOfficeAdditionCoefficient: row[19],
      timeframeAdditionCoefficient: row[20]
    },
    timeframe: row[21] as Timeframe,
    totalMinutes: {
      start: row[22],
      end: row[23]
    },
    physicalMinutes: {
      start: row[24],
      end: row[25]
    },
    houseworkMinutes: {
      start: row[26],
      end: row[27]
    },
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}))

export const LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY_ID_MAX = rows.length
export const LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY_ID_MIN = 1
export const LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY_STUB_COUNT =
  LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY_ID_MAX - LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY_ID_MIN + 1

export const createLtcsHomeVisitLongTermCareDictionaryStubs: CreateStubs<Entry> = (
  n = LTCS_HOME_VISIT_LONG_TERM_CARE_DICTIONARY_ENTRY_STUB_COUNT,
  skip = 0
) => {
  return stubs.slice(skip, n).toArray()
}

export const createLtcsHomeVisitLongTermCareDictionaryStubByServiceCode = (serviceCode: string): Entry | undefined => {
  return stubs.find(x => x.serviceCode === serviceCode)
}

export const getLtcsHomeVisitLongTermCareServiceCodeList = () => rows.map(x => x[0])

export const createLtcsHomeVisitLongTermCareDictionaryStubsForSuggestion = (
  params: LtcsHomeVisitLongTermCareDictionaryApi.GetIndexParams
): Entry[] => {
  const map: Partial<Record<LtcsProjectServiceCategory, LtcsServiceCodeCategory>> = {
    [LtcsProjectServiceCategory.physicalCare]: LtcsServiceCodeCategory.physicalCare,
    [LtcsProjectServiceCategory.housework]: LtcsServiceCodeCategory.housework,
    [LtcsProjectServiceCategory.physicalCareAndHousework]: LtcsServiceCodeCategory.physicalCareAndHousework
  }
  // テストのため、特定事業所加算はなしと仮定する
  return stubs
    .filter(x => {
      return (params.q === undefined || params.q === '' || x.serviceCode.startsWith(params.q)) &&
        (params.timeframe === undefined || x.timeframe === params.timeframe) &&
        (params.category === undefined || x.category === map[params.category]) &&
        (
          params.physicalMinutes === undefined ||
          (x.physicalMinutes.start < params.physicalMinutes && x.physicalMinutes.end >= params.physicalMinutes)
        ) &&
        (
          params.houseworkMinutes === undefined ||
          (x.houseworkMinutes.start < params.houseworkMinutes && x.houseworkMinutes.end >= params.houseworkMinutes)
        ) &&
        (params.headcount === undefined || x.headcount === params.headcount) &&
        (x.specifiedOfficeAddition === HomeVisitLongTermCareSpecifiedOfficeAddition.none)
    })
    .take(10)
    .toArray()
}
