/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 1ページの表示件数
 */
export const ItemsPerPageValuesStandard = [10, 20, 50, 100] as const
export const ItemsPerPageValuesLargeNumber = [10, 100, 200, 300] as const

export type ItemsPerPageStandard = typeof ItemsPerPageValuesStandard[number]
export type ItemsPerPageLargeNumber = typeof ItemsPerPageValuesLargeNumber[number]
export type ItemsPerPage = ItemsPerPageStandard | ItemsPerPageLargeNumber
