/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 指定したミリ秒待機する.
 */
export const wait = (ms?: number): Promise<void> => new Promise(resolve => setTimeout(resolve, ms))
