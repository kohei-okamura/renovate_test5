/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { TimeDuration } from '~/models/time-duration'

/**
 * 経過時間を整形して表示する.
 */
export function duration (input: TimeDuration | number): string {
  const x = TimeDuration.isTimeDuration(input) ? (input.totalMinutes ?? 0) : input
  const h = `${Math.floor(x / 60)}`
  const m = `${x % 60}`.padStart(2, '0')
  return `${h}時間${m}分`
}
