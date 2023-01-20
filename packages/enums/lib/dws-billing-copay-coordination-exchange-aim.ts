/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  declaration: 1,
  modification: 2,
  cancel: 3
} as const

/**
 * 利用者負担上限額管理結果票：作成区分.
 */
export type DwsBillingCopayCoordinationExchangeAim = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingCopayCoordinationExchangeAim = createEnumerable($$, [
  [$$.declaration, '新規'],
  [$$.modification, '修正'],
  [$$.cancel, '取り消し']
])

export const resolveDwsBillingCopayCoordinationExchangeAim = DwsBillingCopayCoordinationExchangeAim.resolve
