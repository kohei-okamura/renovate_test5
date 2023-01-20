/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 障害地域区分 ID.
 */
export type DwsAreaGradeId = number

/**
 * 障害福祉サービス：地域区分.
 */
export type DwsAreaGrade = Readonly<{
  /** 地域区分 ID */
  id: DwsAreaGradeId

  /** 地域区分コード */
  code: string

  /** 名称 */
  name: string
}>
