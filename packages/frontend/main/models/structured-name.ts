/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 氏名.
 */
export type StructuredName = Readonly<{
  /** 姓 */
  familyName: string

  /** 名 */
  givenName: string

  /** フリガナ：姓 */
  phoneticFamilyName: string

  /** フリガナ：名 */
  phoneticGivenName: string

  /** 表示用氏名 */
  displayName: string

  /** フリガナ：表示用氏名 */
  phoneticDisplayName: string
}>
