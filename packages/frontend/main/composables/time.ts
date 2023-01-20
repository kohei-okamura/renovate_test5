/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { datetime } from '~/composables/datetime'
import { DateLike, READABLE_TIME_FORMAT } from '~/models/date'

/**
 * 日時を指定のフォーマットで整形する.
 */
export const time = (input: DateLike, format: string = READABLE_TIME_FORMAT): string => datetime(input, format)
