/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike, READABLE_DATETIME_FORMAT } from '~/models/date'
import { $datetime } from '~/services/datetime-service'

/**
 * 日時を指定のフォーマットで整形する.
 */
export const datetime = (input: DateLike, format: string = READABLE_DATETIME_FORMAT): string => {
  return input ? $datetime.parse(input).toFormat(format) : '-'
}
