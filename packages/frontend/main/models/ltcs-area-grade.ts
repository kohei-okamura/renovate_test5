/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 介保地域区分.
 */
export type LtcsAreaGradeId = number

/**
 * 介保地域区分.
 */
export type LtcsAreaGrade = Readonly<{
  /** 地域区分 ID */
  id: LtcsAreaGradeId

  /** 地域区分コード */
  code: string

  /** 名称 */
  name: string
}>
