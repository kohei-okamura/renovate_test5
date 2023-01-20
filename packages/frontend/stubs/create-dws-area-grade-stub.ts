/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsAreaGrade, DwsAreaGradeId } from '~/models/dws-area-grade'

const xs: DwsAreaGrade[] = [
  { id: 1, code: '1', name: '1級地' },
  { id: 2, code: '2', name: '2級地' },
  { id: 3, code: '3', name: '3級地' },
  { id: 4, code: '4', name: '4級地' },
  { id: 5, code: '5', name: '5級地' },
  { id: 6, code: '6', name: '6級地' },
  { id: 7, code: '7', name: 'その他' }
]
export const DWS_AREA_GRADE_STUB_COUNT = xs.length
export const DWS_AREA_GRADE_ID_MAX = 7
export const DWS_AREA_GRADE_ID_MIN = 1

export function createDwsAreaGradeStub (id: DwsAreaGradeId | undefined): DwsAreaGrade | undefined {
  return xs.find(x => x.id === id)
}

export function createDwsAreaGradeStubs () {
  return xs
}
