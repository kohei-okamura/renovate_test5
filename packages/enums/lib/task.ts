/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  dwsPhysicalCare: 101101,
  dwsHousework: 101102,
  dwsAccompanyWithPhysicalCare: 101103,
  dwsAccompany: 101104,
  dwsVisitingCareForPwsd: 101201,
  ltcsPhysicalCare: 201101,
  ltcsHousework: 201102,
  ltcsPhysicalCareAndHousework: 201103,
  commAccompanyWithPhysicalCare: 111101,
  commAccompany: 111102,
  comprehensive: 211101,
  ownExpense: 701101,
  fieldwork: 801101,
  assessment: 801102,
  visit: 899999,
  officeWork: 901101,
  sales: 901102,
  meeting: 901103,
  other: 988888
} as const

/**
 * 勤務区分.
 */
export type Task = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Task = createEnumerable($$, [
  [$$.dwsPhysicalCare, '居宅：身体'],
  [$$.dwsHousework, '居宅：家事'],
  [$$.dwsAccompanyWithPhysicalCare, '居宅：通院・身体'],
  [$$.dwsAccompany, '居宅：通院'],
  [$$.dwsVisitingCareForPwsd, '重度訪問介護'],
  [$$.ltcsPhysicalCare, '介保：身体'],
  [$$.ltcsHousework, '介保：生活'],
  [$$.ltcsPhysicalCareAndHousework, '介保：身体・生活'],
  [$$.commAccompanyWithPhysicalCare, '移動支援・身体'],
  [$$.commAccompany, '移動支援'],
  [$$.comprehensive, '総合事業'],
  [$$.ownExpense, '自費'],
  [$$.fieldwork, '実地研修'],
  [$$.assessment, 'アセスメント'],
  [$$.visit, 'その他往訪'],
  [$$.officeWork, '事務'],
  [$$.sales, '営業'],
  [$$.meeting, 'ミーティング'],
  [$$.other, 'その他']
])

export const resolveTask = Task.resolve
