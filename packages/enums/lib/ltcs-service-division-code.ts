/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  homeVisitLongTermCare: '11'
} as const

/**
 * 介護保険サービス：請求：サービス種類コード.
 */
export type LtcsServiceDivisionCode = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsServiceDivisionCode = createEnumerable($$, [
  [$$.homeVisitLongTermCare, '訪問介護']
])

export const resolveLtcsServiceDivisionCode = LtcsServiceDivisionCode.resolve
