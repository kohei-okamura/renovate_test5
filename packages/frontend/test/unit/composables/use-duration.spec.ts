/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Activity } from '@zinger/enums/lib/activity'
import { Task } from '@zinger/enums/lib/task'
import { useDurations } from '~/composables/use-durations'
import { Duration } from '~/models/duration'
import { TimeDuration } from '~/models/time-duration'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/use-duration', () => {
  setupComposableTest()

  describe('getTotalDuration', () => {
    it('should not be error when passed undefined schedule', () => {
      const { getTotalDuration } = useDurations(undefined)
      expect(getTotalDuration(undefined)).toStrictEqual(TimeDuration.zero())
    })
  })

  describe('getOutputDurations (basic)', () => {
    it('should be equal to the total time', () => {
      const durations: Duration[] = []
      const task = Task.officeWork
      const schedule = { start: '9:00', end: '13:00' }
      const expected = [
        { activity: Activity.officeWork, duration: 240 /* (13 - 9) * 60 */ }
      ]
      const { getOutputDurations } = useDurations(durations)
      expect(getOutputDurations(task, schedule)).toStrictEqual(expected)
    })

    it('should be equal to the total time minus rest time', () => {
      const restMinutes = 60
      const durations: Duration[] = [
        { activity: Activity.resting, duration: restMinutes }
      ]
      const task = Task.officeWork
      const schedule = { start: '9:00', end: '18:00' }
      const expected = [
        { activity: Activity.officeWork, duration: 480 /* (18 - 9) * 60 - 60 */ },
        { activity: Activity.resting, duration: restMinutes }
      ]
      const { getOutputDurations } = useDurations(durations)
      expect(getOutputDurations(task, schedule)).toStrictEqual(expected)
    })
  })

  describe('getOutputDurations (ltcs)', () => {
    it('should be equal to the input value (din not take a break)', () => {
      const durations: Duration[] = [
        { activity: Activity.ltcsPhysicalCare, duration: 3 },
        { activity: Activity.ltcsHousework, duration: 2 }
      ]
      const task = Task.ltcsPhysicalCareAndHousework
      const schedule = { start: '9:00', end: '13:00' }
      const { getOutputDurations } = useDurations(durations)
      expect(getOutputDurations(task, schedule)).toStrictEqual(durations)
    })

    it('should be equal to the input value (took a break)', () => {
      const restMinutes = 30
      const durations: Duration[] = [
        { activity: Activity.ltcsPhysicalCare, duration: 2.5 },
        { activity: Activity.ltcsHousework, duration: 2 },
        { activity: Activity.resting, duration: restMinutes }
      ]
      const task = Task.ltcsPhysicalCareAndHousework
      const schedule = { start: '9:00', end: '13:00' }
      const { getOutputDurations } = useDurations(durations)
      expect(getOutputDurations(task, schedule)).toStrictEqual(durations)
    })
  })

  describe('getOutputDurations (for pwsd)', () => {
    it('outing support should not affect the total duration (din not take a break)', () => {
      const durations: Duration[] = [
        { activity: Activity.dwsOutingSupportForPwsd, duration: 2 }
      ]
      const expectedAttendance: Duration[] = [
        { activity: Activity.dwsVisitingCareForPwsd, duration: 240 },
        { activity: Activity.dwsOutingSupportForPwsd, duration: 2 }
      ]
      const task = Task.dwsVisitingCareForPwsd
      const schedule = { start: '9:00', end: '13:00' }
      const { getOutputDurations } = useDurations(durations)
      expect(getOutputDurations(task, schedule)).toStrictEqual(expectedAttendance)
    })

    it('outing support should not affect the total duration (took a break)', () => {
      const restMinutes = 30
      const durations: Duration[] = [
        { activity: Activity.dwsOutingSupportForPwsd, duration: 2 },
        { activity: Activity.resting, duration: restMinutes }
      ]
      const expectedAttendance: Duration[] = [
        { activity: Activity.dwsVisitingCareForPwsd, duration: 210 },
        { activity: Activity.resting, duration: restMinutes },
        { activity: Activity.dwsOutingSupportForPwsd, duration: 2 }
      ]
      const task = Task.dwsVisitingCareForPwsd
      const schedule = { start: '9:00', end: '13:00' }
      const { getOutputDurations } = useDurations(durations)
      expect(getOutputDurations(task, schedule)).toStrictEqual(expectedAttendance)
    })
  })
})
