/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  provisional: 1,
  active: 2,
  retired: 9
} as const

/**
 * スタッフ：状態.
 */
export type StaffStatus = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const StaffStatus = createEnumerable($$, [
  [$$.provisional, '仮登録'],
  [$$.active, '在職中'],
  [$$.retired, '退職']
])

export const resolveStaffStatus = StaffStatus.resolve
