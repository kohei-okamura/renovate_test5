/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateTime } from 'luxon'
import { DateLike } from '~/models/date'
import { $datetime } from '~/services/datetime-service'

/**
 * 和暦.
 */
const eras = [
  { code: '明治', from: $datetime.from(1868, 1, 25), to: $datetime.from(1912, 7, 29) },
  { code: '大正', from: $datetime.from(1912, 7, 30), to: $datetime.from(1926, 12, 24) },
  { code: '昭和', from: $datetime.from(1926, 12, 25), to: $datetime.from(1989, 1, 7) },
  { code: '平成', from: $datetime.from(1989, 1, 8), to: $datetime.from(2019, 4, 30) },
  { code: '令和', from: $datetime.from(2019, 5, 1), to: undefined }
] as const

type EraDefs = typeof eras
type EraDef = EraDefs[number]
type FormatFunction = (era: EraDef, year: string, date: DateTime) => string
type Formats = 'longDate' | 'shortDate' | 'month' | 'year'
type DateFormat = 'short' | 'long'

const formats: Record<Formats, FormatFunction> = {
  longDate: (era, year, date) => `${era.code}${year}年${date.month}月${date.day}日`,
  shortDate: (era, year, date) => {
    const month = `${date.month}`.padStart(2, '0')
    const day = `${date.day}`.padStart(2, '0')
    return `${era.code[0]}${year.padStart(2, '0')}.${month}.${day}`
  },
  month: (era, year, date) => `${era.code}${year}年${date.month}月`,
  year: (era, year) => `${era.code}${year}年`
}

const convert = (input: DateLike | undefined, format: FormatFunction): string => {
  if (input) {
    const value = $datetime.parse(input)
    const era = eras.find(x => x.from <= value && (x.to === undefined || x.to >= value))
    if (era) {
      // 0埋め処理がしやすいように年は文字列として扱う
      const year = `${value.year - era.from.year + 1}`
      return format(era, year, value)
    }
  }
  return '-'
}

/**
 * 日付を和暦表示(NYY.MM.DD)に変換する.
 *
 * @param input 日付文字列
 * @param format 書式
 * @example
 * eraDate('2020-11-06) //=> '令02.11.06'
 */
export const eraDate = (input?: DateLike, format: DateFormat = 'long') => {
  return convert(input, formats[format === 'long' ? 'longDate' : 'shortDate'])
}

/**
 * 日付を和暦の年月表示(元号Y年M月)に変換する.
 * 元号が切り替わった年月を渡した場合は、変更後の元号を返す
 *
 * @param input 日付文字列
 * @example
 * eraMonth('2020-11-06) //=> '令和2年11月'
 * @example
 * eraMonth('1989-01) //=> '平成1年1月'
 */
export const eraMonth = (input?: DateLike) => {
  if (typeof input === 'string' && /^\d{4}-\d{2}$/.test(input)) {
    return convert($datetime.parse(`${input}-01`).endOf('month').startOf('day'), formats.month)
  } else {
    return convert(input, formats.month)
  }
}

/**
 * 日付を和暦の年表示に変換する.
 * 元号が切り替わった年を渡した場合は、変更後の元号を返す
 *
 * @param input 日付文字列
 * @example
 * eraYear('2020-11-06') //=> '令和2年'
 */
export const eraYear = (input?: DateLike) => {
  if (typeof input === 'string' && /^\d{4}$/.test(input)) {
    return convert($datetime.parse(`${input}-12-31`).startOf('day'), formats.year)
  } else if (typeof input === 'string' && /^\d{4}-\d{2}$/.test(input)) {
    return convert($datetime.parse(`${input}-01`).endOf('month').startOf('day'), formats.year)
  } else {
    return convert(input, formats.year)
  }
}
