/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  careManagerOffice: 1,
  self: 2,
  preventionOffice: 3
} as const

/**
 * 介護保険サービス：居宅サービス計画作成区分.
 */
export type LtcsCarePlanAuthorType = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const LtcsCarePlanAuthorType = createEnumerable($$, [
  [$$.careManagerOffice, '居宅介護支援事業所作成'],
  [$$.self, '自己作成'],
  [$$.preventionOffice, '介護予防支援事業所・地域包括支援センター作成']
])

export const resolveLtcsCarePlanAuthorType = LtcsCarePlanAuthorType.resolve
