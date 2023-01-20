/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'
import { OwnExpenseProgram } from '~/models/own-expense-program'

const index = breadcrumb('自費サービス', '/own-expense-programs?restore=1')

export const ownExpensePrograms = {
  index: [
    breadcrumb('自費サービス')
  ],
  new: [
    index,
    breadcrumb('自費サービスを登録')
  ],
  view: [
    index,
    breadcrumb('自費サービス詳細')
  ],
  edit: (program: OwnExpenseProgram | undefined) => [
    index,
    breadcrumb('自費サービス詳細', program ? `/own-expense-programs/${program.id}` : ''),
    breadcrumb('自費サービスを編集')
  ]
} as const
