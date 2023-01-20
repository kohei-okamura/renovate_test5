/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { reactive } from '@nuxtjs/composition-api'
import { Activity } from '@zinger/enums/lib/activity'
import { Task } from '@zinger/enums/lib/task'
import { Duration, duration } from '~/models/duration'
import { TimeRange } from '~/models/range'
import { taskToActivity } from '~/models/task-utils'
import { TimeDuration } from '~/models/time-duration'

const findAndMap = (xs: Partial<Duration>[] | undefined, activity: Activity) => {
  const duration = (xs ?? []).find(x => x.activity === activity)
  return duration && duration.duration ? TimeDuration.fromMinutes(duration.duration) : TimeDuration.zero()
}

const getTotalDuration = (schedule: Partial<TimeRange> | undefined) => {
  const { start, end } = schedule ?? {}
  return TimeDuration.diff(start, end).getOrElse(() => TimeDuration.zero())
}

/**
 * 休憩時間を反映した勤務時間の内訳を返す
 * 特殊な時間（身体介護、移動加算 等）を持つタスクについては考慮しない
 *
 * @param task タスクの種類
 * @param totalDuration 合計勤務時間
 * @param resting 休憩時間
 * @return durations [{ activity, duration }, ... ]
 */
const getDurationReflectedResting = (task: Task, totalDuration: TimeDuration, resting: TimeDuration) => {
  if (resting.isZero) {
    // 休憩なしの場合は合計時間をそのまま用いる.
    return taskToActivity(task).map(x => duration(x, totalDuration))
  } else {
    // 休憩がある場合は合計時間から休憩時間を引く.
    return [
      ...taskToActivity(task).map(x => duration(x, totalDuration.minus(resting))),
      duration(Activity.resting, resting)
    ]
  }
}

export const useDurations = (xs: Partial<Duration>[] | undefined) => {
  const durations = reactive({
    housework: findAndMap(xs, Activity.ltcsHousework),
    physicalCare: findAndMap(xs, Activity.ltcsPhysicalCare),
    dwsOutingSupportForPwsd: findAndMap(xs, Activity.dwsOutingSupportForPwsd),
    resting: findAndMap(xs, Activity.resting)
  })
  const getOutputDurations = (task: Task, schedule: Partial<TimeRange> | undefined) => {
    const totalDuration = getTotalDuration(schedule)
    if (task === Task.ltcsPhysicalCareAndHousework) {
      // 介保：身体・生活の場合は入力された値をそのまま用いる.
      return [
        duration(Activity.ltcsPhysicalCare, durations.physicalCare),
        duration(Activity.ltcsHousework, durations.housework),
        ...(durations.resting.isZero ? [] : [duration(Activity.resting, durations.resting)])
      ]
    } else if (task === Task.dwsVisitingCareForPwsd) {
      // 重度訪問介護の場合
      // 移動加算の入力がある場合は、Activity を追加する
      // 勤務時間に上乗せして請求できるため、単純に追加するだけで良い
      const outingSupport = durations.dwsOutingSupportForPwsd
      return [
        ...getDurationReflectedResting(task, totalDuration, durations.resting),
        ...(outingSupport.isZero ? [] : [duration(Activity.dwsOutingSupportForPwsd, outingSupport)])
      ]
    } else {
      return getDurationReflectedResting(task, totalDuration, durations.resting)
    }
  }
  return { durations, getTotalDuration, getOutputDurations }
}
