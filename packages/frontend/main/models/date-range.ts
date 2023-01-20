/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateRangeType } from '@zinger/enums/lib/date-range-type'
import { isEmpty } from '@zinger/helpers'
import { DateTime, DateTimeUnit } from 'luxon'
import { Range } from '~/models/range'

type DateRange = Range<string>

type DateRangeParams = Readonly<{
  dateRangeType?: DateRangeType
  start?: string
  end?: string
}>

export const createDateRange = (base: DateTime, unit: DateTimeUnit) => ({
  start: base.startOf(unit).toISODate(),
  end: base.endOf(unit).toISODate()
})

export const expandDateRange = (params: DateRangeParams): DateRange => {
  const base = DateTime.local()
  return DateRangeType.match(params.dateRangeType ?? DateRangeType.thisWeek, {
    lastWeek: () => createDateRange(base.minus({ weeks: 1 }), 'week'),
    thisWeek: () => createDateRange(base, 'week'),
    nextWeek: () => createDateRange(base.plus({ weeks: 1 }), 'week'),
    lastMonth: () => createDateRange(base.minus({ months: 1 }), 'month'),
    thisMonth: () => createDateRange(base, 'month'),
    nextMonth: () => createDateRange(base.plus({ months: 1 }), 'month'),
    specify: () => ({
      start: isEmpty(params.start) ? base.startOf('week').toISODate() : params.start,
      end: isEmpty(params.end) ? base.endOf('week').toISODate() : params.end
    })
  })
}
