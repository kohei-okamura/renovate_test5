/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateTime } from 'luxon'

export type DateLike = string | DateTime

export type DateString = string

export const HOUR_AND_MINUTE_FORMAT = 'HH:mm'

export const MINUTES_PER_DAY = 1440

export const OLDEST_DATE = '1920-01-01'

//
// データ交換用フォーマット
//
export const ISO_MONTH_FORMAT = 'yyyy-MM'

export const ISO_DATE_FORMAT = 'yyyy-MM-dd'

export const ISO_TIME_FORMAT = 'HH:mm'

export const ISO_DATETIME_FORMAT = 'yyyy-MM-dd\'T\'HH:mm:ssZZZ'

//
// 表示用フォーマット
//
export const READABLE_MONTH_FORMAT = 'yyyy年MM月'

export const READABLE_DATE_FORMAT = 'yyyy年MM月dd日'

export const READABLE_TIME_FORMAT = 'HH:mm'

export const READABLE_DATETIME_FORMAT = 'yyyy年MM月dd日 HH:mm'
