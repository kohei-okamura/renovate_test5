/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Time } from '~/models/time'

/**
 * 範囲.
 */
export type Range<T> = Readonly<{
  /** 開始 */
  start: T

  /** 終了 */
  end: T
}>

/**
 * 時刻の範囲.
 */
export type TimeRange = Range<Time>
