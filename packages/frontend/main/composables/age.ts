/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { $datetime } from '~/services/datetime-service'

/**
 * 生年月日を年齢に変換する.
 */
export function age (input: DateLike): string {
  if (input) {
    const today = $datetime.now
    const birthday = $datetime.parse(input)
    return `${today.diff(birthday, ['years', 'months']).years}`
  } else {
    return '-'
  }
}
