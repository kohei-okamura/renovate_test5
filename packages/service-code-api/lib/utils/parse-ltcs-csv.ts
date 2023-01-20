/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  HomeVisitLongTermCareSpecifiedOfficeAddition
} from '@zinger/enums/lib/home-visit-long-term-care-specified-office-addition'
import { LtcsCalcCycle } from '@zinger/enums/lib/ltcs-calc-cycle'
import { LtcsCalcType } from '@zinger/enums/lib/ltcs-calc-type'
import { LtcsCompositionType } from '@zinger/enums/lib/ltcs-composition-type'
import { LtcsNoteRequirement } from '@zinger/enums/lib/ltcs-note-requirement'
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { parse } from 'csv-parse/sync'
import { promises as fs } from 'fs'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '../models/ltcs-home-visit-long-term-care-dictionary-entry'
import { range, Range } from '../models/range'
import { detectLtcsServiceCodeCategory } from './detect-ltcs-service-code-category'
import { fileExists } from './file-exists'
import { normalizeString } from './normalize-string'

type Row = string[]

const readFromCsv = async (source: string): Promise<Row[]> => {
  const content = await fs.readFile(source, { encoding: 'utf-8' })
  return parse(content, { delimiter: ',' })
}

/**
 * 注加減算サービスコードに特定のコードが含まれるかどうかを判定する.
 */
const contains = (row: Row, code: string): boolean => row.slice(40, 43).includes(code)

/**
 * 提供人数を特定する.
 */
const detectHeadcount = (row: Row, category: LtcsServiceCodeCategory): number => {
  switch (category) {
    case LtcsServiceCodeCategory.physicalCare:
    case LtcsServiceCodeCategory.housework:
    case LtcsServiceCodeCategory.physicalCareAndHousework:
      return contains(row, '110002') ? 2 : 1
    default:
      return 0
  }
}

/**
 * 特定事業所加算区分を特定する.
 */
const detectSpecifiedOfficeAddition = (row: Row): HomeVisitLongTermCareSpecifiedOfficeAddition => {
  if (contains(row, '110005')) {
    return HomeVisitLongTermCareSpecifiedOfficeAddition.addition1
  } else if (contains(row, '110006')) {
    return HomeVisitLongTermCareSpecifiedOfficeAddition.addition2
  } else if (contains(row, '110007')) {
    return HomeVisitLongTermCareSpecifiedOfficeAddition.addition3
  } else if (contains(row, '110010')) {
    return HomeVisitLongTermCareSpecifiedOfficeAddition.addition4
  } else {
    return HomeVisitLongTermCareSpecifiedOfficeAddition.none
  }
}

/**
 * 単位数区分を特定する.
 */
const detectCalcType = (row: Row): LtcsCalcType => {
  switch (row[7]) {
    case '01':
      return LtcsCalcType.score
    case '03':
      return LtcsCalcType.percent
    case '04':
      return LtcsCalcType.baseScore
    case '07':
      return LtcsCalcType.percent
    case '08':
      return LtcsCalcType.permille
    default:
      throw new Error(`未対応の単位数識別: ${row[7]}`)
  }
}

/**
 * 算定単位を特定する.
 */
const detectCalcCycle = (row: Row): LtcsCalcCycle => {
  switch (row[8]) {
    case '01':
      return LtcsCalcCycle.perService
    case '02':
      return LtcsCalcCycle.perDay
    case '03':
      return LtcsCalcCycle.perMonth
    default:
      throw new Error(`未対応の算定単位: ${row[8]}`)
  }
}

/**
 * 時間帯を特定する.
 */
const detectTimeframe = (row: Row, category: LtcsServiceCodeCategory): Timeframe => {
  switch (category) {
    case LtcsServiceCodeCategory.physicalCare:
    case LtcsServiceCodeCategory.housework:
    case LtcsServiceCodeCategory.physicalCareAndHousework:
      if (contains(row, '110003')) {
        return Timeframe.night
      } else if (contains(row, '110004')) {
        return Timeframe.midnight
      } else {
        return Timeframe.daytime
      }
    default:
      return Timeframe.unknown
  }
}

/**
 * 特定事業所加算係数を算出する.
 */
const computeSpecifiedOfficeAddition = (x: HomeVisitLongTermCareSpecifiedOfficeAddition): number => {
  switch (x) {
    case HomeVisitLongTermCareSpecifiedOfficeAddition.addition1:
      return 120
    case HomeVisitLongTermCareSpecifiedOfficeAddition.addition2:
    case HomeVisitLongTermCareSpecifiedOfficeAddition.addition3:
      return 110
    case HomeVisitLongTermCareSpecifiedOfficeAddition.addition4:
      return 105
    default:
      return 100
  }
}

/**
 * 時間帯係数を算出する.
 */
const computeTimeframeAddition = (x: Timeframe): number => {
  switch (x) {
    case Timeframe.midnight:
      return 150
    case Timeframe.morning:
    case Timeframe.night:
      return 125
    default:
      return 100
  }
}

/**
 * 身体時間数を特定する.
 */
const detectPhysicalMinutes = (
  row: Row,
  category: LtcsServiceCodeCategory,
  totalMinutes: Range<number>
): Range<number> => {
  switch (category) {
    case LtcsServiceCodeCategory.physicalCare:
      return totalMinutes
    case LtcsServiceCodeCategory.physicalCareAndHousework:
      switch (row[31]) {
        case '1':
          return range(20, 30)
        case '2':
          return range(30, 60)
        case '3':
          return range(60, 90)
        case '4':
          return range(90, 120)
        case '5':
          return range(120, 150)
        case '6':
          return range(150, 180)
        case '7':
          return range(180, 210)
        case '8':
          return range(210, 240)
        case '9':
        case 'A':
        case 'B':
        case 'C':
          return range(240, 9999)
        case 'D':
          return range(0, 20)
        default:
          throw new Error(`未対応の身体生活識別区分: ${row[31]}`)
      }
    default:
      return range(0, 0)
  }
}

/**
 * 家事時間数を特定する.
 */
const detectHouseworkDurations = (
  category: LtcsServiceCodeCategory,
  totalMinutes: Range<number>,
  physicalMinutes: Range<number>
): Range<number> => {
  switch (category) {
    case LtcsServiceCodeCategory.housework:
      return totalMinutes
    // case 文の中で `const` を使うため `{...}` で括る
    case LtcsServiceCodeCategory.physicalCareAndHousework: {
      const x = totalMinutes.start - physicalMinutes.start
      return range(x, x === 70 ? 9999 : x + 25)
    }
    default:
      return range(0, 0)
  }
}

const rowToObject = (row: Row): LtcsHomeVisitLongTermCareDictionaryEntry => {
  const serviceCode = row[0] + row[1]
  const name = normalizeString(row[5])
  const isScalable = row[9] !== ''

  const category = detectLtcsServiceCodeCategory(row, name)
  const specifiedOfficeAddition = detectSpecifiedOfficeAddition(row)
  const timeframe = detectTimeframe(row, category)

  const totalMinutes = range(+row[37], +row[38])
  const physicalMinutes = detectPhysicalMinutes(row, category, totalMinutes)
  const houseworkMinutes = detectHouseworkDurations(category, totalMinutes, physicalMinutes)

  return {
    serviceCode,
    name,
    category,
    headcount: detectHeadcount(row, category),
    compositionType: +row[14] as LtcsCompositionType,
    specifiedOfficeAddition,
    noteRequirement: row[80] === '01' ? LtcsNoteRequirement.durationMinutes : LtcsNoteRequirement.none,
    isLimited: row[81] === '3',
    isBulkSubtractionTarget: !!+row[293],
    isSymbioticSubtractionTarget: !!+row[294],
    score: {
      calcCycle: detectCalcCycle(row),
      calcType: detectCalcType(row),
      value: +row[6]
    },
    extraScore: {
      baseMinutes: +row[9],
      isAvailable: isScalable,
      specifiedOfficeAdditionCoefficient: isScalable ? computeSpecifiedOfficeAddition(specifiedOfficeAddition) : 0,
      timeframeAdditionCoefficient: isScalable ? computeTimeframeAddition(timeframe) : 0,
      unitMinutes: +row[11],
      unitScore: +row[13]
    },
    timeframe,
    totalMinutes,
    physicalMinutes,
    houseworkMinutes
  }
}

export const parseLtcsCsv = async (source: string): Promise<LtcsHomeVisitLongTermCareDictionaryEntry[]> => {
  if (!(await fileExists(source))) {
    throw new Error(`File not found: ${source}`)
  }
  const rows = await readFromCsv(source)
  return rows
    // - 訪問介護のみ
    // - 適用期間未定のみ
    // - 通院等乗降介助を除く
    .filter(row => +row[0] === 11 && +row[4] === 99999999 && +row[35] !== 8)
    .map(rowToObject)
}
