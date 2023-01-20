/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  backOffice: 1,
  homeVisitCare: 2,
  homeVisitNursing: 3,
  dayCare: 4,
  homeCareSupport: 5,
  college: 6,
  massage: 7
} as const

/**
 * 事業内容.
 */
export type Business = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const Business = createEnumerable($$, [
  [$$.backOffice, 'バックオフィス'],
  [$$.homeVisitCare, '訪問介護'],
  [$$.homeVisitNursing, '訪問看護'],
  [$$.dayCare, 'デイサービス'],
  [$$.homeCareSupport, '居宅介護支援'],
  [$$.college, 'カレッジ'],
  [$$.massage, 'マッサージ']
])

export const resolveBusiness = Business.resolve
