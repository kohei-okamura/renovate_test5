/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Activity } from '@zinger/enums/lib/activity'
import { TimeDuration } from '~/models/time-duration'

/**
 * 予実所要時間.
 */
export type Duration = Readonly<{
  /** 予実活動内容 */
  activity: Activity

  /** 所要時間（分） */
  duration: number
}>

/**
 * {@link Duration} を生成する.
 */
export const duration = (activity: Activity, duration: TimeDuration | number): Duration => ({
  activity,
  duration: TimeDuration.isTimeDuration(duration) ? (duration.totalMinutes ?? 0) : duration
})
