/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

import { StaffId } from '~/models/staff'

/**
 * 予実担当スタッフ.
 */
export type Assignee = Readonly<{
  /** スタッフID */
  staffId: StaffId | undefined

  /** 未定フラグ */
  isUndecided: boolean

  /** 研修フラグ */
  isTraining: boolean
}>
