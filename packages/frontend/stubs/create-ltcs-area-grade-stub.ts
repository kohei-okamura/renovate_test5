/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsAreaGrade, LtcsAreaGradeId } from '~/models/ltcs-area-grade'

const xs: LtcsAreaGrade[] = [
  { id: 1, code: '1', name: '1級地' },
  { id: 2, code: '2', name: '2級地' },
  { id: 3, code: '3', name: '3級地' },
  { id: 4, code: '4', name: '4級地' },
  { id: 5, code: '5', name: '5級地' },
  { id: 6, code: '6', name: '6級地' },
  { id: 7, code: '7', name: '7級地' },
  { id: 8, code: '8', name: 'その他' }
]
export const LTCS_AREA_GRADE_STUB_COUNT = xs.length
export const LTCS_AREA_GRADE_ID_MAX = 7
export const LTCS_AREA_GRADE_ID_MIN = 1

export function createLtcsAreaGradeStub (id: LtcsAreaGradeId | undefined): LtcsAreaGrade | undefined {
  return xs.find(x => x.id === id)
}

export function createLtcsAreaGradeStubs () {
  return xs
}

export const ltcsAreaGradeStubs: LtcsAreaGrade[] = [
  { id: 1, code: '1', name: '1級地' },
  { id: 2, code: '2', name: '2級地' },
  { id: 3, code: '3', name: '3級地' },
  { id: 4, code: '4', name: '4級地' },
  { id: 5, code: '5', name: '5級地' },
  { id: 6, code: '6', name: '6級地' },
  { id: 7, code: '7', name: '7級地' },
  { id: 8, code: '8', name: 'その他' }
]
