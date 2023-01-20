/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 範囲.
 */
export type Range<T> = Readonly<{
  /** 開始 */
  start: T

  /** 終了 */
  end: T
}>

export const range = <T> (start: T, end: T): Range<T> => ({ start, end })
