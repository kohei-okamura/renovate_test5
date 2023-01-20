/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateTime as LuxonDateTime, Settings } from 'luxon'
import { DateLike } from '~/models/date'

type ParseFunction = {
  (x: DateLike): LuxonDateTime
  (x: DateLike | undefined): LuxonDateTime | undefined
}

export type DatetimeService = {
  now: LuxonDateTime
  from (year: number, month: number, day: number): LuxonDateTime
  parse: ParseFunction
}

function createDatetimeService (): DatetimeService {
  Settings.defaultLocale = 'ja'
  Settings.defaultZone = 'Asia/Tokyo'
  return {
    get now (): LuxonDateTime {
      return LuxonDateTime.local()
    },
    from (year: number, month: number, day: number): LuxonDateTime {
      return LuxonDateTime.local(year, month, day, 0, 0, 0)
    },
    parse (x: any): any {
      if (typeof x === 'string') {
        return LuxonDateTime.fromISO(x)
      } else if (x instanceof Date) {
        return LuxonDateTime.fromJSDate(x)
      } else {
        return x
      }
    }
  }
}

export const $datetime = createDatetimeService()
