/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateTime } from 'luxon'
import { date } from '~/composables/date'
import { DateLike, ISO_DATE_FORMAT, ISO_DATETIME_FORMAT } from '~/models/date'
import { Range, TimeRange } from '~/models/range'

/**
 * スケジュール.
 */
export type Schedule = Range<DateLike> & {
  /** 日付 */
  date: DateLike
}

/**
 * 日付と開始時間、終了時間から Schedule 型を作成する.
 * 開始時間が終了時間より過去の場合は翌日として扱う.
 *
 * @param params { date 日付文字列（yyyy-MM-dd）, start: 'HH:mm', end: 'HH:mm' }
 * @throw 引数から有効な日付型が作成できなかった場合
 * @example
 * timeToSchedule('2021-02-15', { start: '23:00', end: '01:00' })
 * //=>{
 *   date: '2021-02-15',
 *   start: '2021-02-15T23:00:00+0900',
 *   end: '2021-02-16T01:00:00+0900'
 * }
 */
type ScheduleFromTimeRange = {
  (params: TimeRange & { date: DateLike }): Schedule
  (params?: Partial<TimeRange & { date: DateLike }>): undefined
}
export const scheduleFromTimeRange: ScheduleFromTimeRange = (
  params: Partial<TimeRange & { date: DateLike }> = {}
): any => {
  const { start, end } = params
  if (params.date === undefined || start === undefined || end === undefined) {
    return undefined
  } else {
    const dateString = date(params.date, ISO_DATE_FORMAT)
    const startDateTime = DateTime.fromISO(`${dateString}T${start}`)
    const endDateTime = DateTime.fromISO(`${dateString}T${end}`)
    if (!startDateTime.isValid || !endDateTime.isValid) {
      throw new Error(`Invalid argument. date: ${dateString}, start: ${start}, end: ${end}`)
    }
    return {
      date: dateString,
      start: startDateTime.toFormat(ISO_DATETIME_FORMAT),
      end: (startDateTime < endDateTime ? endDateTime : endDateTime.plus({ days: 1 })).toFormat(ISO_DATETIME_FORMAT)
    }
  }
}
