/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  category1: 1,
  category2: 2
} as const

/**
 * 障害福祉サービス：請求：給付種別.
 */
export type DwsBillingPaymentCategory = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const DwsBillingPaymentCategory = createEnumerable($$, [
  [$$.category1, '介護給付費・訓練等給付費・地域相談支援給付費・特例介護給付費・特例訓練等給付費'],
  [$$.category2, '特定障害者特別給付費・高額障害者福祉サービス費']
])

export const resolveDwsBillingPaymentCategory = DwsBillingPaymentCategory.resolve
