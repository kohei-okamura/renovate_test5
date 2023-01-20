/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
import { createEnumerable } from './enum'

const $$ = {
  whole: 1,
  group: 2,
  office: 3,
  person: 4
} as const

/**
 * 権限範囲.
 */
export type RoleScope = typeof $$[keyof typeof $$]

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const RoleScope = createEnumerable($$, [
  [$$.whole, '全体'],
  [$$.group, 'グループ'],
  [$$.office, '事業所'],
  [$$.person, '個人']
])

export const resolveRoleScope = RoleScope.resolve
