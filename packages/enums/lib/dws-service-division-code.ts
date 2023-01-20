/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  homeHelpService: '11',
  visitingCareForPwsd: '12'
} as const

/**
 * 障害福祉サービス：請求：サービス種類コード.
 */
export type DwsServiceDivisionCode = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsServiceDivisionCode = createEnumerable($$, [
  [$$.homeHelpService, '居宅介護'],
  [$$.visitingCareForPwsd, '重度訪問介護']
])

export const resolveDwsServiceDivisionCode = DwsServiceDivisionCode.resolve
