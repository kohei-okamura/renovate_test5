/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { Staff } from '~/models/staff'

/**
 * 認証・認可情報.
 */
export type Auth = Readonly<{
  /** システム管理者フラグ */
  isSystemAdmin: boolean

  /** 権限 */
  permissions: Permission[]

  /** スタッフ */
  staff: Staff
}>
