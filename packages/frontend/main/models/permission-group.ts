/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { DateLike } from '~/models/date'

/**
 * 権限グループ ID.
 */
export type PermissionGroupId = number

/**
 * 権限グループ.
 */
export type PermissionGroup = Readonly<{
  /** 権限 ID */
  id: PermissionGroupId

  /** 権限グループコード */
  code: string

  /** 名称 */
  name: string

  /** 表示名 */
  displayName: string

  /** 権限 */
  permissions: Permission[]

  /** 登録日時 */
  sortOrder: number

  /** 登録日時 */
  createdAt: DateLike
}>
