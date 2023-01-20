/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateTimeFormatOptions, LocaleOptions } from 'luxon'
import { DateLike } from '~/models/date'
import { $datetime } from '~/services/datetime-service'

/**
 * 日付を Intl を使ってフォーマットする.
 */
export function localeDate (input: DateLike, options: LocaleOptions & DateTimeFormatOptions): string {
  return input ? $datetime.parse(input).toLocaleString(options) : '-'
}
