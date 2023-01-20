/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
  dwsOutingSupportForPwsd: 101202,
  ltcsPhysicalCare: 201101,
  ltcsHousework: 201102,
  commAccompanyWithPhysicalCare: 111101,
  commAccompany: 111102,
  comprehensive: 211101,
  ownExpense: 711101,
  fieldwork: 811101,
  assessment: 811102,
  visit: 899999,
  officeWork: 911101,
  sales: 911102,
  meeting: 911103,
  other: 988888,
  resting: 999999
} as const

/**
 * 勤務内容.
 */
export type Activity = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Activity = createEnumerable($$, [
  [$$.dwsPhysicalCare, '居宅：身体'],
  [$$.dwsHousework, '居宅：家事'],
  [$$.dwsAccompanyWithPhysicalCare, '居宅：通院・身体あり'],
  [$$.dwsAccompany, '居宅：通院・身体なし'],
  [$$.dwsVisitingCareForPwsd, '重訪'],
  [$$.dwsOutingSupportForPwsd, '重訪（移動加算）'],
  [$$.ltcsPhysicalCare, '介保：身体'],
  [$$.ltcsHousework, '介保：生活'],
  [$$.commAccompanyWithPhysicalCare, '移動支援・身体あり'],
  [$$.commAccompany, '移動支援・身体なし'],
  [$$.comprehensive, '総合事業'],
  [$$.ownExpense, '自費'],
  [$$.fieldwork, '実地研修'],
  [$$.assessment, 'アセスメント'],
  [$$.visit, 'その他往訪'],
  [$$.officeWork, '事務'],
  [$$.sales, '営業'],
  [$$.meeting, 'ミーティング'],
  [$$.other, 'その他'],
  [$$.resting, '休憩']
])

export const resolveActivity = Activity.resolve
