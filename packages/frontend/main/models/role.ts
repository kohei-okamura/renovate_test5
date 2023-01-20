/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { RoleScope } from '@zinger/enums/lib/role-scope'
import { DateLike } from '~/models/date'

/**
 * ロール ID.
 */
export type RoleId = number

/**
 * ロール.
 */
export type Role = Readonly<{
  /** ロール ID */
  id: RoleId

  /** ロール名 */
  name: string

  /** システム管理者フラグ */
  isSystemAdmin: boolean

  /** 権限 */
  permissions: Permission[]

  /** 権限範囲 */
  scope: RoleScope

  /** 表示順 */
  sortOrder: number

  /** 登録日時 */
  createdAt: DateLike

  /** 更新日時 */
  updatedAt: DateLike
}>
