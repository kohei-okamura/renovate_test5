/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { breadcrumb } from '~/composables/breadcrumbs/core'

export const setting = {
  index: [
    breadcrumb('事業者別設定')
  ],
  edit: [
    breadcrumb('事業者別設定', '/settings'),
    breadcrumb('事業者別設定を編集')
  ],
  new: [
    breadcrumb('事業者別設定', '/settings'),
    breadcrumb('事業者別設定を登録')
  ]
} as const
