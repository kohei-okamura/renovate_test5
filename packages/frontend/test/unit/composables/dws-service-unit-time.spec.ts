/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { calculateWorkingHours, getMovingDurationHours } from '~/composables/dws-service-unit-time'

describe('composables/dws-service-unit-time', () => {
  describe('calculateWorkingHours', () => {
    it.each`
      time  | digits | expected
      ${{ start: '2021-02-15T23:00:00+0900', end: '2021-02-16T01:00:00+0900' }} | ${2} | ${'2.00'}
      ${{ start: '2021-02-15T22:39:00+0900', end: '2021-02-16T01:00:00+0900' }} | ${1} | ${'2.4'}
    `('should return $expected if $time is passed', ({ time, digits, expected }) => {
      expect(calculateWorkingHours(time, digits)).toEqual(expected)
    })
  })

  describe('getMovingDurationHours', () => {
    it.each`
      minutes | digits | expected
      ${0}  | ${1} | ${''}
      ${30} | ${1} | ${'0.5'}
      ${60} | ${2} | ${'1.00'}
      ${81} | ${1} | ${'1.5'}
      ${181} | ${1} | ${'4.0'}
    `('should return $expected if $minutes is passed', ({ minutes, digits, expected }) => {
      expect(getMovingDurationHours(minutes, digits)).toEqual(expected)
    })
  })
})
